<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Http\Controllers\NonBankingServices\ValuationController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 3 (قرارات اليوزر) على دراسة 140 (سنة 2026):
 * * 1. المصروف المدفوع قبل ما يستحق أصل «Prepaid Expenses» في المدينين الآخرين. كان بيظهر دائن بالسالب في «Other Creditors».
 * *    مثال: إيجار 25,000 في الشهر من يوليو لديسمبر 2026 ، بيتدفع كل 6 شهور مقدماً (Vero بيدفع مجموع الفترة في أول شهر فيها):
 * *    يوليو بيتدفع 150,000 ويستحق 25,000 → آخر يوليو 125,000 مدفوع مقدماً ، آخر أكتوبر 50,000 ، آخر ديسمبر صفر.
 * *    الدائن بتاعه صفر كل شهر (كان −125,000 في يوليو) ، والميزانية متوازنة.
 * * 2. الـ DCF: الدائنين الآخرين التزام ، فزيادتهم كاش فاضل: التغيير = آخر − أول (calculateChangeInBefore ، زي قروض البنك).
 * *    كان أول − آخر: دائنين من 1,000,000 لـ 1,500,000 كانوا بيخصموا 500,000 من التدفق الحر بدل ما يضيفوهم.
 */
class PrepaidExpensesAndDcfCreditorsTest extends NonBankingTestCase
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

	/** @return array the balance sheet rows after the expenses and the statements are recalculated */
	private function balanceSheet(Study $study): array
	{
		Study::findOrFail($study->id)->recalculateStudyExpenses();
		(new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true);

		return Study::findOrFail($study->id)->getBalanceSheetViewVars(true)['tableDataFormatted'];
	}

	/** the sub item of the given key in the section titled $section */
	private function line(array $bs, string $section, string|int $key): array
	{
		foreach ($bs as $index => $rows) {
			if (($rows['main_items'][$index]['options']['title'] ?? '') === $section) {
				return (array) ($rows['sub_items'][$key]['data'] ?? []);
			}
		}

		return [];
	}

	public function test_an_expense_paid_in_advance_is_a_prepaid_asset_not_a_negative_creditor(): void
	{
		$study = $this->study();
		$nameId = (int) $this->nb()->table('expense_names')->value('id');
		$this->nb()->table('expenses')->insert([
			'expense_name_id' => $nameId, 'expense_category' => 'general-expense', 'relation_name' => 'fixed_monthly_repeating_amount',
			'amount' => 25000, 'start_date' => 6, 'end_date' => 11, 'payment_terms' => 'semi-annually', 'increase_interval' => 'annually',
			'increase_rates' => json_encode([0, 0]), 'vat_rate' => 0, 'is_deductible' => 0, 'withhold_tax_rate' => 0, 'monthly_percentage' => 0,
			'model_id' => $study->id, 'model_name' => 'Study', 'study_id' => $study->id, 'company_id' => $study->company_id, 'expense_type' => 'Expense',
		]);

		$bs = $this->balanceSheet($study);

		$prepaid = $this->line($bs, 'Other Debtors', 'prepaid-expenses');
		$this->assertEqualsWithDelta(125000, $prepaid[6], 0.01, 'July: 150,000 paid, 25,000 due');
		$this->assertEqualsWithDelta(50000, $prepaid[9], 0.01, 'October');
		$this->assertEqualsWithDelta(0, $prepaid[11], 0.01, 'December: all of it charged');
		$creditor = $this->line($bs, 'Other Creditors', $nameId);
		foreach ([6, 7, 8, 9, 10, 11] as $month) {
			$this->assertEqualsWithDelta(0, (float) ($creditor[$month] ?? 0), 0.01, "the creditor of month {$month} (it was the prepaid amount, negative)");
		}
		$check = array_slice((array) $bs[14]['main_items'][14]['data'], 0, 24, true);
		$this->assertLessThanOrEqual(1, max(array_map(fn ($v) => abs((float) $v), $check)), 'the balance sheet balances');
	}

	public function test_creditors_that_grow_add_to_the_free_cash_flow(): void
	{
		$study = $this->study();
		$this->balanceSheet($study);
		$dcf = fn () => array_values((new ValuationController())->index($this->companyOf($study), Study::findOrFail($study->id))->getData()['formattedDcfMethod']['net-change-in-working-capital']);
		$before = $dcf();

		// 500,000 more owed to the other creditors at the end of 2026 (the year's last month, 11)
		$balanceSheet = Study::findOrFail($study->id)->balanceSheet;
		$yearly = (array) $balanceSheet->yearly_other_creditors;
		$yearly[11] = (float) ($yearly[11] ?? 0) + 500000;
		$balanceSheet->update(['yearly_other_creditors' => $yearly]);
		$after = $dcf();

		$this->assertEqualsWithDelta($before[0] + 500000, $after[0], 0.01, '2026 keeps 500,000 more cash (it lost 500,000)');
	}
}
