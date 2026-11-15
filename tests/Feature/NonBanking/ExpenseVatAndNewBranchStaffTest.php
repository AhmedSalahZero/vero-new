<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 على دراسة 140 (يناير 2026 ، التشغيل من سبتمبر = شهر 8):
 * * 1. الـ VAT القابلة للخصم على المصروفات (قرار اليوزر: نصلح Vero ، و FinVero زيه). مصروف 1,000 + VAT ‏14% قابلة للخصم:
 * *    المصروف 1,000 ، والفاتورة 1,140 بتتدفع ، والـ 140 أصل "Recoverable VAT". قبل كده:
 * *    - الثابت الشهري: الـ 140 مكانتش متسجلة في أي مكان ، فالميزانية مختلة 140 كل شهر.
 * *    - "% من": الـ VAT بتطلع −1,000 ، فالمصروف والدفع صفر.
 * *    - "لكل موظف": المصروف 1,140.
 * * 2. تعيينات الفروع الجديدة بتتفرد على الافتتاحات زي ما صفحة New Branches بتحفظها (recalculateManpower كان بيقرا القالب
 * *    كأنه شهور الدراسة ، من غير ضرب في عدد الفروع).
 */
class ExpenseVatAndNewBranchStaffTest extends NonBankingTestCase
{
	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function study(): Study
	{
		$study = $this->loadStudyFixture('study_140');
		foreach (EquityOpeningBalance::where('study_id', $study->id)->get() as $equity) {
			$equity->save();   // V6: the capital from the first month, so the balance sheet balances before the operation start
		}

		return Study::findOrFail($study->id);
	}

	private function addExpense(Study $study, array $row): int
	{
		return $this->nb()->table('expenses')->insertGetId(array_merge([
			'expense_name_id' => (int) $this->nb()->table('expense_names')->value('id'), 'expense_category' => 'general-expense',
			'start_date' => 8, 'end_date' => 11, 'payment_terms' => 'cash', 'increase_interval' => 'annually', 'increase_rates' => json_encode([0, 0]),
			'vat_rate' => 14, 'is_deductible' => 1, 'withhold_tax_rate' => 1, 'amount' => 0, 'monthly_percentage' => 0,
			'model_id' => $study->id, 'model_name' => 'Study', 'study_id' => $study->id, 'company_id' => $study->company_id, 'expense_type' => 'Expense',
		], $row));
	}

	/** recalculates the expenses and the statements; @return array{0: array, 1: array} the income statement and the balance sheet */
	private function statements(Study $study): array
	{
		Study::findOrFail($study->id)->recalculateStudyExpenses();
		$is = (new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true)['tableDataFormatted'];
		$bs = Study::findOrFail($study->id)->getBalanceSheetViewVars(true)['tableDataFormatted'];

		return [$is, $bs];
	}

	private function worstCheck(array $bs): float
	{
		return max(array_map(fn ($v) => abs((float) $v), array_slice((array) $bs[14]['main_items'][14]['data'], 0, 24, true)));
	}

	/** the balance sheet line of the given title, wherever it is */
	private function bsLine(array $bs, string $title): array
	{
		foreach ($bs as $section) {
			foreach ($section['sub_items'] ?? [] as $item) {
				if (($item['options']['title'] ?? '') === $title) {
					return (array) $item['data'];
				}
			}
		}

		return [];
	}

	public function test_a_fixed_monthly_expense_with_a_deductible_vat_balances(): void
	{
		$study = $this->study();
		$id = $this->addExpense($study, ['relation_name' => 'fixed_monthly_repeating_amount', 'amount' => 1000]);

		[, $bs] = $this->statements($study);

		$expense = $this->nb()->table('expenses')->find($id);
		$this->assertEqualsWithDelta(1000, json_decode($expense->monthly_repeating_amounts, true)[8], 0.01, 'the expense is before VAT');
		$this->assertEqualsWithDelta(1140, json_decode($expense->total_after_vat, true)[8], 0.01, 'the invoice');
		$recoverable = $this->bsLine($bs, 'Recoverable VAT');
		$this->assertEqualsWithDelta(140, $recoverable[8], 0.01);
		$this->assertEqualsWithDelta(560, $recoverable[11], 0.01, 'September to December: 4 × 140');
		$this->assertLessThanOrEqual(1, $this->worstCheck($bs), 'it was out by 140 a month');
	}

	public function test_a_percentage_expense_with_a_deductible_vat_is_charged_and_paid(): void
	{
		$study = $this->study();
		$id = $this->addExpense($study, ['relation_name' => 'percentage_of_sales', 'percentage_of' => 'revenue', 'revenue_stream_type' => json_encode(['has_micro_finance']),
			'stream_category_ids' => json_encode([]), 'monthly_percentage' => 1, 'withhold_tax_rate' => 0]);

		[, $bs] = $this->statements($study);

		$expense = $this->nb()->table('expenses')->find($id);
		$beforeVat = (array) json_decode($expense->expense_as_percentages, true);
		$month = array_key_first(array_filter($beforeVat, fn ($v) => (float) $v > 0));
		$this->assertNotNull($month, 'the study has microfinance revenue to take 1 % of');
		// 1 % of the month's revenue, and the invoice 14 % more (the VAT was −the expense, so both were 0)
		$this->assertEqualsWithDelta((float) $beforeVat[$month] * 0.14, (float) json_decode($expense->total_vat, true)[$month], 0.01);
		$this->assertEqualsWithDelta((float) $beforeVat[$month] * 1.14, (float) json_decode($expense->total_after_vat, true)[$month], 0.01);
		$this->assertLessThanOrEqual(1, $this->worstCheck($bs));
	}

	public function test_a_per_employee_expense_with_a_deductible_vat_is_charged_before_vat(): void
	{
		$study = $this->study();
		// 10 staff hired in September (the operation start) at 5,000 net, and 100 an employee a month + 14 % deductible VAT
		// → 1,000 a month (it was 1,140). (Staff employed before the operation start are paid in cash but not charged on
		// Vero's income statement, which shows the operation months only: a separate finding, not this test's.)
		$manpower = $this->nb()->table('manpowers')->where('study_id', $study->id)->first();
		$hiring = array_fill(0, 24, 0);
		$hiring[8] = 10;
		$this->nb()->table('manpowers')->where('id', $manpower->id)->update(['existing_count' => 0, 'hiring_counts' => json_encode($hiring), 'monthly_net_salary' => 5000]);
		Study::findOrFail($study->id)->recalculateManpower();
		$id = $this->addExpense($study, ['relation_name' => 'expense_per_employee', 'monthly_cost_of_unit' => 100, 'position_ids' => json_encode([$manpower->position_id]), 'withhold_tax_rate' => 0]);

		[, $bs] = $this->statements($study);

		$this->assertEqualsWithDelta(1000, json_decode($this->nb()->table('expenses')->find($id)->monthly_repeating_amounts, true)[8], 0.01);
		$this->assertEqualsWithDelta(140, $this->bsLine($bs, 'Recoverable VAT')[8], 0.01);
		$this->assertLessThanOrEqual(1, $this->worstCheck($bs));
	}

	public function test_new_branch_staff_are_hired_from_each_branch_opening_times_its_branches(): void
	{
		// the page's template: 2 in the opening month and 1 three months later, per branch; 3 branches open in June (5)
		// → 6 hired in June and 3 in September: 6 staff from June, 9 from September
		$study = $this->study();
		$this->nb()->table('new_branch_microfinance_opening_projections')->where('study_id', $study->id)->update(['start_date' => 5, 'counts' => 3]);
		$template = array_fill(0, 24, 0);
		$template[0] = 2;
		$template[3] = 1;
		$position = (int) $this->nb()->table('manpowers')->where('study_id', $study->id)->value('position_id');
		$this->nb()->table('manpowers')->insert(['study_id' => $study->id, 'company_id' => $study->company_id, 'position_id' => $position, 'type' => 'new-branches',
			'existing_count' => 0, 'monthly_net_salary' => 5000, 'hiring_counts' => json_encode($template)]);

		$study = Study::findOrFail($study->id);
		$this->assertSame([5 => 6.0, 6 => 0.0, 7 => 0.0, 8 => 3.0], array_slice(array_filter($study->newBranchesHiringCounts($template), fn ($v, $k) => $k <= 8, ARRAY_FILTER_USE_BOTH), 0, 4, true));

		$study->recalculateManpower();

		$counts = json_decode($this->nb()->table('manpowers')->where('study_id', $study->id)->where('type', 'new-branches')->value('accumulated_manpower_counts'), true);
		$this->assertEquals(0, $counts[4]);
		$this->assertEquals(6, $counts[5]);
		$this->assertEquals(9, $counts[8], 'it used to read the template as study months: 2 in January, 3 from April');
	}
}
