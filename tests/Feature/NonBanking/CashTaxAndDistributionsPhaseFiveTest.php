<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\FixedAssetOpeningBalance;
use App\Models\NonBankingService\IncomeStatementReport;
use App\Models\NonBankingService\Study;
use App\Providers\NonBankingServiceProvider;
use Illuminate\Support\Facades\DB;

/**
 * * المرحلة 5 في Vero ، على دراسة 140 الحقيقية (Misr Finance ، 2026 ، أرصدة افتتاحية فيها ODA بـ 758,238,624):
 * * - فايدة فائض النقدية والـ ODA على متوسط رصيد الشهر × السعر ÷ 12 (نفس FinVero).
 * * - الضريبة سنة بسنة ، والخسارة بتترحّل 5 سنين.
 * * - الاحتياطي القانوني في ديسمبر ، وبنود التوزيع في يناير ، والدفع في أبريل (معادلة FinVero). قبل كده Vero كان
 * *   بياخد النسب دي ومبيسجلش حاجة.
 */
class CashTaxAndDistributionsPhaseFiveTest extends NonBankingTestCase
{
	private Study $study;

	protected function setUp(): void
	{
		parent::setUp();
		$this->study = $this->loadStudyFixture('study_140');
		// the opening balances saved again with the current code, as the page's Save does (the fixture keeps the
		// production rows, whose capital starts with the operation — phase 3 V6 / V18)
		foreach (EquityOpeningBalance::where('study_id', $this->study->id)->get() as $equity) {
			$equity->save();
		}
		foreach (FixedAssetOpeningBalance::where('study_id', $this->study->id)->get() as $asset) {
			$asset->save();
		}
	}

	/** income statement → cash flow (+ ODA) → balance sheet, in the order the pages run them */
	private function statements(): array
	{
		$company = $this->companyOf($this->study);
		$is = (new IncomeStatementController())->index($company, Study::findOrFail($this->study->id), true)['tableDataFormatted'];
		$study = Study::findOrFail($this->study->id);
		$cf = $study->getCashInOutFlowViewVars()['tableDataFormatted'];
		$study->cashFlowForOdas($study->getCashInOutFlowViewVars()['netCashBeforeWorking']);
		$bs = Study::findOrFail($this->study->id)->getBalanceSheetViewVars(true)['tableDataFormatted'];

		return [$is, $cf, $bs];
	}

	/** sets General Assumptions columns as the page saves them (no model hooks, so no loan is calculated again) */
	private function assumptions(array $values): void
	{
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')
			->where('study_id', $this->study->id)
			->update(array_map(fn ($v) => is_array($v) ? json_encode($v) : $v, $values));
	}

	private function everyMonth(float $value, int $months = 24): array
	{
		return array_fill(0, $months, $value);
	}

	private function report(): object
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $this->study->id)->first();
	}

	private function at(array $series, int $month): float
	{
		return (float) ($series[$month] ?? 0);
	}

	private function assertBalanced(array $bs, int $months): void
	{
		for ($month = 0; $month < $months; $month++) {
			$this->assertEqualsWithDelta(0, $this->at($bs[14]['main_items'][14]['data'], $month), 1, "check error, month {$month}");
		}
	}

	public function test_surplus_cash_and_the_oda_earn_interest_on_the_average_balance_of_the_month(): void
	{
		// credit interest 12 % (1 % a month); ODA at the corridor 20 % + 3 % = 23 %
		$this->assumptions([
			'credit_interest_rate_for_surplus_cash' => $this->everyMonth(12),
			'cbe_lending_corridor_rates' => $this->everyMonth(20),
			'odas_bank_lending_margin_rates' => $this->everyMonth(3),
		]);
		[, , $bs] = $this->statements();

		$report = $this->report();
		$beginning = json_decode($report->cash_and_bank_beginning_balances, true);
		$beforeTopUp = json_decode($report->net_cash_before_extra_capital_injection, true);
		$interest = json_decode(DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $this->study->id)->value('interest_cash_surplus'), true);
		$oda = json_decode($report->oda_statements, true);

		$this->assertEqualsWithDelta(758238624, $oda['oda_opening_balances'][0], 0.01, 'the opening ODA');
		$this->assertGreaterThan(0, array_sum($oda['oda_interests']));
		for ($month = 0; $month < 12; $month++) {
			// cash: (opening + the cash before the interest) ÷ 2 × 1 % (there is no manual equity in this study)
			$beforeInterest = $beforeTopUp[$month] - $interest[$month];
			$this->assertEqualsWithDelta(max(0, ($beginning[$month] + $beforeInterest) / 2) * 0.01, $interest[$month], 0.01, "cash interest, month {$month}");
			// ODA: (opening + the balance after the month's drawdowns and settlement) ÷ 2 × 23 % ÷ 12
			$this->assertEqualsWithDelta(($oda['oda_opening_balances'][$month] + $oda['total_dues'][$month]) / 2 * 0.23 / 12, $oda['oda_interests'][$month], 0.01, "ODA interest, month {$month}");
			$this->assertEqualsWithDelta($oda['total_dues'][$month] + $oda['oda_interests'][$month] + $oda['extra_odas_withdrawal'][$month], $oda['end_balance'][$month], 0.01, "ODA end, month {$month}");
		}
		$this->assertBalanced($bs, 12);
	}

	public function test_a_tax_year_deducts_the_losses_of_the_years_before_it(): void
	{
		// 2026 earns 3,600,000 (in April) after a 2025 loss of 1,200,000: taxed on 2,400,000 × 22.5 % = 540,000
		// (with no loss brought forward it is 810,000)
		$study = Study::findOrFail($this->study->id);
		$study->corporate_taxes_rate = 22.5;
		$report = new IncomeStatementReport(['leasing_revenue' => [3 => 3600000]]);
		$dateIndexWithDate = $study->getDateIndexWithDate();

		$withLoss = $study->calculateCorporateTaxesFromIncomeStatementReport($report, $study->getYearIndexWithItsMonths(), 0, 11, $dateIndexWithDate, [2025 => -1200000]);
		$withoutLoss = $study->calculateCorporateTaxesFromIncomeStatementReport($report, $study->getYearIndexWithItsMonths(), 0, 11, $dateIndexWithDate);

		$this->assertSame(2026, $withLoss['tax_year']);
		$this->assertEqualsWithDelta(3600000, $withLoss['profit_before_tax'], 0.01);
		$this->assertEqualsWithDelta(540000, array_sum($withLoss['calculate_corporate_taxes']), 0.01);
		$this->assertEqualsWithDelta(540000, $withLoss['calculate_corporate_taxes'][3], 0.01);
		$this->assertEqualsWithDelta(810000, array_sum($withoutLoss['calculate_corporate_taxes']), 0.01);
		// a loss year has no tax at all
		$loss = $study->calculateCorporateTaxesFromIncomeStatementReport(new IncomeStatementReport(['leasing_revenue' => [3 => -100]]), $study->getYearIndexWithItsMonths(), 0, 11, $dateIndexWithDate);
		$this->assertEqualsWithDelta(0, array_sum($loss['calculate_corporate_taxes']), 0.001);
	}

	public function test_the_legal_reserve_is_taken_in_december_and_nothing_else_while_the_retained_earnings_are_negative(): void
	{
		// 2026 earns about 30.5 million; the opening retained earnings are −188,878,783, so retained earnings + the
		// profit are negative: only the 5 % legal reserve is taken (its cap is 50 % of 205,000,000, far above the
		// 25,031,996 already there)
		$this->assumptions(['legal_reserve_rate' => 5, 'max_legal_reserve_rate' => 50,
			'employee_profit_share_rates' => [10, 10], 'shareholders_dividend_payout_ratios' => [20, 20]]);
		[$is, , $bs] = $this->statements();

		$profit = array_sum(array_slice($is[13]['main_items']['net-profit']['data'], 0, 12, true));
		$this->assertGreaterThan(0, $profit);
		$transfer = $profit * 0.05;
		$this->assertEqualsWithDelta(25031996, $this->at($bs[13]['sub_items']['Legal Reserve']['data'], 10), 0.01, 'November');
		$this->assertEqualsWithDelta(25031996 + $transfer, $this->at($bs[13]['sub_items']['Legal Reserve']['data'], 11), 0.01, 'December');
		$distributions = json_decode($this->report()->profit_distributions, true);
		$this->assertEqualsWithDelta(0, array_sum($distributions['employee_profit_share'] ?? []), 0.001);
		$this->assertEqualsWithDelta(0, array_sum($distributions['payments'] ?? []), 0.001);
		$this->assertBalanced($bs, 12);
	}

	/** the balance sheet check of each month, for two years */
	private function checkErrors(array $bs): array
	{
		return array_map(fn (int $month) => round($this->at($bs[14]['main_items'][14]['data'], $month), 2), range(0, 23));
	}

	public function test_the_profit_distribution_is_recognised_in_january_and_paid_in_april(): void
	{
		// the same study over two years, its opening retained earnings made positive (200,000,000 moved from the
		// paid-up capital: the opening balance sheet is unchanged). 2026's profit P: legal reserve 5 % (no cap), then
		// employees 10 % and first dividend 5 % of P, board 10 % and dividend 20 % of the rest (80 % of P):
		// January 2027 owes 10 % + 5 % + 8 % + 16 % = 39 % of P, paid in April 2027
		$equity = EquityOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$equity->paid_up_capital_amount = 5000000;
		$equity->retained_earnings = -188878783 + 200000000;
		$equity->save();
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies')->where('id', $this->study->id)
			->update(['study_end_date' => '2027-12-01', 'duration_in_years' => 2]);
		NonBankingServiceProvider::bindStudyDates(Study::findOrFail($this->study->id));
		// the same two years with no reserve and no distribution: 2027's saved inputs are not complete (the fixture is
		// a one-year study, nothing is calculated again here), so its balance sheet check is not 0 to start with —
		// what the distribution must do is leave it as it is, month by month
		[$noDistributionIs, , $noDistributionBs] = $this->statements();
		$this->assertEqualsWithDelta(0, array_sum(array_slice($this->checkErrors($noDistributionBs), 0, 12)), 1, '2026 balances');
		$this->assumptions(['legal_reserve_rate' => 5, 'max_legal_reserve_rate' => 0,
			'employee_profit_share_rates' => [10, 10], 'shareholders_first_dividend_portions' => [5, 5],
			'border_of_directors_profit_share_rates' => [10, 10], 'shareholders_dividend_payout_ratios' => [20, 20],
			'shareholders_dividend_in_cash_or_shares' => ['in_cash', 'in_cash']]);
		[$is, $cf, $bs] = $this->statements();

		$profit = array_sum(array_slice($is[13]['main_items']['net-profit']['data'], 0, 12, true));
		$salaries = array_sum(array_slice((array) json_decode(DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')
			->where('study_id', $this->study->id)->value('total_manpower_expenses'), true), 0, 12, true));
		$this->assertGreaterThan(0, $profit);
		$employees = min($profit * 0.10, $salaries ?: INF);
		$base = $profit - $profit * 0.05 - $employees - $profit * 0.05;
		$payable = $employees + $profit * 0.05 + $base * 0.10 + $base * 0.20;

		$this->assertEqualsWithDelta($profit * 0.05, $this->at($bs[13]['sub_items']['Legal Reserve']['data'], 11) - 25031996, 0.01, 'December reserve');
		$this->assertEqualsWithDelta(0, $this->at($bs[11]['sub_items']['Dividends Payable']['data'], 11), 0.01, 'nothing owed in December');
		foreach ([12, 13, 14] as $month) {
			$this->assertEqualsWithDelta($payable, $this->at($bs[11]['sub_items']['Dividends Payable']['data'], $month), 0.01, "owed in month {$month}");
		}
		$this->assertEqualsWithDelta(0, $this->at($bs[11]['sub_items']['Dividends Payable']['data'], 15), 0.01, 'paid in April 2027');
		$this->assertEqualsWithDelta($payable, $this->at($cf[1]['sub_items']['Dividend Payment']['data'], 15), 0.01, 'the April cash');
		// the retained earnings lose the reserve in December and the distribution in January
		$retained = $bs[13]['sub_items']['Retained Earnings']['data'];
		$netProfit = $bs[13]['sub_items']['Net Profit']['data'];
		$this->assertEqualsWithDelta($this->at($retained, 10) + $this->at($netProfit, 10) - $profit * 0.05, $this->at($retained, 11), 0.01, 'December');
		$this->assertEqualsWithDelta($this->at($retained, 11) + $this->at($netProfit, 11) - $payable, $this->at($retained, 12), 0.01, 'January');
		// 2026's profit is the same with or without the distribution (it is paid after the year) …
		$this->assertEqualsWithDelta(array_sum(array_slice($noDistributionIs[13]['main_items']['net-profit']['data'], 0, 12, true)), $profit, 0.01);
		// … and every entry has its other side: the check of each month does not move
		$this->assertEqualsWithDelta($this->checkErrors($noDistributionBs), $this->checkErrors($bs), 1);
	}
}
