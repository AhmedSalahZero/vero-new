<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use App\Services\NonBanking\StudyRecalculation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * * المرحلة 7: كل دراسات الـ non-banking بتتحسب تاني بالكود الحالي ، كأن اليوزر حفظ كل صفحاتها بالترتيب
 * * (الجولة 2: الخطوات في App\Services\NonBanking\StudyRecalculation ، وأداة المقارنة بتستخدمها كمان):
 * *   الأرصدة الافتتاحية (الـ 13 جدول) ، وحالات التمويل متناهي الصغر وعقوده ، والقروض والإيرادات ، والأصول الثابتة ،
 * *   والمرتبات ، ومصروفات صفحة Expenses كلها ، وإيجار الفروع الجديدة ، وقائمة الدخل والتدفقات والـ ODA والميزانية.
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
		// every page's Save, in the pages' order (the gate's runner uses the same steps)
		StudyRecalculation::run($studyId, $company);
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
