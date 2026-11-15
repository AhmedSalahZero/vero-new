<?php

namespace App\Console\Commands;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\Company;
use App\Models\NonBankingService\CashAndBankOpeningBalance;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\FixedAssetOpeningBalance;
use App\Models\NonBankingService\Study;
use App\Models\NonBankingService\SupplierPayableOpeningBalance;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use App\Services\NonBanking\OpeningBalancesStatements;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * * المرحلة 7: كل دراسات الـ non-banking بتتحسب تاني بالكود الحالي ، بنفس ترتيب الصفحات:
 * *   1. الأرصدة الافتتاحية (حقوق الملكية ، الأصول الثابتة ، المحفظة وقروضها ، وتحصيلاتها ومدفوعاتها بالشهر)
 * *   2. القروض والإيرادات والمصروفات (recalculateAllRevenuesLoans) ، 3. الأصول الثابتة ، 4. المرتبات
 * *   ومصروفات صفحة Expenses كلها (recalculateStudyExpenses) ، 5. قائمة الدخل والتدفقات والـ ODA ، 6. الميزانية.
 * * الإصلاحات اللي اتعملت في المراحل 1–6 (مثلاً V6 و V18 و V19 والضريبة والتوزيعات) مش بتظهر في الدراسات المحفوظة غير لما
 * * تتحسب تاني. كل دراسة في transaction لوحدها ، والتقرير بيطلّع قبل وبعد: صافي الربح ، والأصول والكاش آخر الدراسة ،
 * * وأكبر فرق في الميزانية.
 * *   php artisan non-banking:recalculate --dry-run              (بيحسب ويرجّع كل حاجة زي ما كانت)
 * *   php artisan non-banking:recalculate --study=140 --study=85
 * *   php artisan non-banking:recalculate --company=31
 */
class RecalculateNonBankingStudiesCommand extends Command
{
	protected $signature = 'non-banking:recalculate
		{--study=* : only these study ids}
		{--company= : only the studies of this company}
		{--dry-run : recalculate inside a transaction that is rolled back: the report, and nothing changed}';

	protected $description = 'Recalculate the non-banking studies with the current code and report each balance sheet before and after';

	public function handle(): int
	{
		$dryRun = (bool) $this->option('dry-run');
		$query = Study::query()->orderBy('id');
		if ($this->option('study')) {
			$query->whereIn('id', array_map('intval', (array) $this->option('study')));
		}
		if ($this->option('company') !== null) {
			$query->where('company_id', (int) $this->option('company'));
		}
		if ($user = User::query()->first()) {
			Auth::setUser($user);   // the statements' controllers expect a signed-in user
		}

		$rows = [];
		$failed = 0;
		foreach ($query->pluck('id') as $studyId) {
			$rows[] = $row = $this->recalculate((int) $studyId, $dryRun);
			$failed += $row[9] === 'failed' ? 1 : 0;
			gc_collect_cycles();
		}

		$this->table(['study', 'company', 'net profit before', 'net profit after', 'assets before', 'assets after', 'worst check before', 'worst check after', 'first month off (after)', 'result'], $rows);
		$this->line(($dryRun ? 'Dry run: nothing was saved. ' : '').count($rows).' studies, '.$failed.' failed.');

		return $failed ? self::FAILURE : self::SUCCESS;
	}

	/** @return list<string> one row of the report */
	private function recalculate(int $studyId, bool $dryRun): array
	{
		$study = Study::find($studyId);
		$company = $study ? Company::find($study->company_id) : null;
		$row = fn (string $result, ?array $before = null, ?array $after = null) => [
			$studyId, $study->company_id ?? '—',
			$this->money($before['net_profit'] ?? null), $this->money($after['net_profit'] ?? null),
			$this->money($before['assets'] ?? null), $this->money($after['assets'] ?? null),
			$this->money($before['check'] ?? null), $this->money($after['check'] ?? null), $after['first_month_off'] ?? '—', $result,
		];
		if (! $company) {
			return $row('skipped: its company was deleted (non-banking:prune-deleted-companies)');
		}
		if (! $study->generalAndReserveAssumption) {
			return $row('skipped: no general assumptions yet');
		}

		$connections = [DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME), DB::connection()];
		foreach ($connections as $connection) {
			$connection->beginTransaction();
		}
		try {
			NonBankingServiceProvider::bindStudyDates($study);
			// a study whose statements were never calculated cannot show a balance sheet yet: "—" before
			try {
				$before = $this->snapshot($studyId);
			} catch (Throwable) {
				$before = null;
			}
			$this->recalculateStudy($studyId, $company);
			$after = $this->snapshot($studyId);
			foreach ($connections as $connection) {
				$dryRun ? $connection->rollBack() : $connection->commit();
			}

			return $row($dryRun ? 'dry run' : 'saved', $before, $after);
		} catch (Throwable $e) {
			foreach ($connections as $connection) {
				$connection->rollBack();
			}
			$this->error("study {$studyId}: ".$e->getMessage());

			return $row('failed');
		}
	}

	private function recalculateStudy(int $studyId, Company $company): void
	{
		// 1. the opening balances, as their page's Save does (the model hooks rebuild their statements)
		foreach ([EquityOpeningBalance::class, FixedAssetOpeningBalance::class, CashAndBankOpeningBalance::class, SupplierPayableOpeningBalance::class] as $model) {
			foreach ($model::where('study_id', $studyId)->get() as $openingBalance) {
				$openingBalance->save();
			}
		}
		OpeningBalancesStatements::refresh(Study::findOrFail($studyId));
		// 2. the loans, revenues and the expenses that follow them
		Study::findOrFail($studyId)->recalculateAllRevenuesLoans(new Request());
		// 3. the fixed assets and 4. the manpower
		foreach ([FixedAsset::FFE, FixedAsset::PER_EMPLOYEE, FixedAsset::NEW_BRANCH] as $type) {
			Study::findOrFail($studyId)->recalculateFixedAssets($type);
		}
		Study::findOrFail($studyId)->recalculateManpower();
		// 4b. every Expenses-page row from its own dates (the loans step only redoes % of sales and cost per unit): a
		// study that was extended kept the amounts of its old dates (study 154: an expense ending 12/2027 had 2028)
		Study::findOrFail($studyId)->recalculateStudyExpenses();
		// 5. income statement (it runs the cash flow first), cash flow and ODA, 6. balance sheet
		(new IncomeStatementController())->index($company, Study::findOrFail($studyId), true);
		$study = Study::findOrFail($studyId);
		$study->cashFlowForOdas($study->getCashInOutFlowViewVars()['netCashBeforeWorking']);
	}

	/**
	 * the study as its statements show it now; first_month_off is the first month whose balance sheet is out by more than
	 * 1 (month 0 = the opening balance sheet itself does not balance)
	 *
	 * @return array{net_profit: float, assets: float, check: float, first_month_off: string}
	 */
	private function snapshot(int $studyId): array
	{
		$study = Study::findOrFail($studyId);
		$months = range(0, $study->getViewStudyEndDateAsIndex());
		$balanceSheet = $study->getBalanceSheetViewVars(true)['tableDataFormatted'];
		$assets = (array) ($balanceSheet[8]['main_items'][8]['data'] ?? []);
		$checks = (array) ($balanceSheet[14]['main_items'][14]['data'] ?? []);
		$netProfit = $study->incomeStatement ? (array) $study->incomeStatement->monthly_net_profit : [];

		return [
			'net_profit' => array_sum(array_intersect_key(array_map('floatval', $netProfit), array_flip($months))),
			'assets' => (float) ($assets[end($months)] ?? 0),
			'check' => (float) array_reduce($months, fn ($worst, $m) => abs((float) ($checks[$m] ?? 0)) > abs($worst) ? (float) ($checks[$m] ?? 0) : $worst, 0.0),
			'first_month_off' => (string) (array_values(array_filter($months, fn ($m) => abs((float) ($checks[$m] ?? 0)) > 1))[0] ?? '—'),
		];
	}

	private function money(?float $value): string
	{
		return $value === null ? '—' : number_format($value, 2);
	}
}
