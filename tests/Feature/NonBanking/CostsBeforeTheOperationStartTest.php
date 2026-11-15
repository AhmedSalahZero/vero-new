<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 (قرار اليوزر ، زي FinVero): المرتبات والمصروفات اللي قبل بداية التشغيل مصروف في شهورها. دراسة 140 بتبدأ
 * * يناير 2026 والتشغيل من سبتمبر (شهر 8). قبل كده كانت بتتدفع (التدفقات) وقائمة الدخل بتاخد شهور التشغيل بس ، فالميزانية
 * * مختلة بيها: 10 موظفين من الأول × 5,000 × 8 شهور = 400,000.
 */
class CostsBeforeTheOperationStartTest extends NonBankingTestCase
{
	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	/** a line of the income statement, added up over the sections it is in (the salaries are in each cost category) */
	private function line(array $is, string $title): array
	{
		$total = [];
		foreach ($is as $section) {
			foreach ($section['sub_items'] ?? [] as $key => $item) {
				if ($key === $title || ($item['options']['title'] ?? '') === $title) {
					foreach ((array) ($item['data'] ?? []) as $month => $amount) {
						$total[$month] = ($total[$month] ?? 0) + (float) $amount;
					}
				}
			}
		}

		return $total;
	}

	public function test_salaries_and_expenses_before_the_operation_start_are_costs_of_their_months(): void
	{
		$study = $this->loadStudyFixture('study_140');
		foreach (EquityOpeningBalance::where('study_id', $study->id)->get() as $equity) {
			$equity->save();
		}
		// 10 staff employed from the start at 5,000 net
		$this->nb()->table('manpowers')->where('study_id', $study->id)->update(['existing_count' => 10, 'monthly_net_salary' => 5000, 'hiring_counts' => json_encode(array_fill(0, 24, 0))]);
		Study::findOrFail($study->id)->recalculateManpower();   // before the expense is added: it totals every saved expense
		// and a rent of 1,000 a month from March (a month before the operation start), paid in cash
		$this->nb()->table('expenses')->insert([
			'expense_name_id' => (int) $this->nb()->table('expense_names')->value('id'), 'expense_category' => 'general-expense', 'relation_name' => 'fixed_monthly_repeating_amount',
			'start_date' => 2, 'end_date' => 11, 'amount' => 1000, 'payment_terms' => 'cash', 'increase_interval' => 'annually', 'increase_rates' => json_encode([0, 0]),
			'vat_rate' => 0, 'is_deductible' => 0, 'withhold_tax_rate' => 0, 'monthly_percentage' => 0,
			'model_id' => $study->id, 'model_name' => 'Study', 'study_id' => $study->id, 'company_id' => $study->company_id, 'expense_type' => 'Expense',
		]);
		Study::findOrFail($study->id)->recalculateStudyExpenses();

		$is = (new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true)['tableDataFormatted'];

		$salaries = $this->line($is, 'Manpower Salaries');
		$gross = (array) json_decode((string) $this->nb()->table('manpowers')->where('study_id', $study->id)->value('salary_expenses'), true);
		$this->assertGreaterThanOrEqual(50000, (float) ($salaries[0] ?? 0), 'January: 10 × 5,000 net (grossed up by the study\'s salary taxes)');
		$this->assertEqualsWithDelta((float) $gross[0], (float) $salaries[0], 0.01);
		$this->assertEqualsWithDelta(1000, (float) ($this->line($is, $this->nb()->table('expense_names')->value('name'))[2] ?? 0), 0.01, 'the rent of March');

		$checks = (array) Study::findOrFail($study->id)->getBalanceSheetViewVars(true)['tableDataFormatted'][14]['main_items'][14]['data'];
		$worst = max(array_map(fn ($v) => abs((float) $v), array_slice($checks, 0, 24, true)));
		$this->assertLessThanOrEqual(1, $worst, 'it was out by the salaries and the rent of January–August');
	}
}
