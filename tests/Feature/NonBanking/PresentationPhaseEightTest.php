<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * المرحلة 8: العرض المشترك بين النظامين (قرار اليوزر) ، على دراسة 140.
 * * - فايدة فائض النقدية إيراد تمويلي تحت الـ EBIT (سطر بالسالب في "Net Finance Cost") ، مش جوه الإيراد.
 * * - فايدة التزامات الإيجار (حق الانتفاع) تكلفة تمويل تحت الـ EBIT ، مش جوه المصروفات العمومية.
 * * - صافي الربح زي ما هو. والـ ECL زي ما كان بعد الـ EBITDA.
 * * - التدفقات: النقدية أول الشهر = آخر الشهر اللي قبله ، وضخ رأس المال اليدوي سطر لوحده في الداخل.
 */
class PresentationPhaseEightTest extends NonBankingTestCase
{
	private Study $study;

	protected function setUp(): void
	{
		parent::setUp();
		$this->study = $this->loadStudyFixture('study_140');
		foreach (EquityOpeningBalance::where('study_id', $this->study->id)->get() as $equity) {
			$equity->save();
		}
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->where('study_id', $this->study->id)
			->update(['credit_interest_rate_for_surplus_cash' => json_encode(array_fill(0, 24, 12))]);
	}

	private function incomeStatement(): array
	{
		return (new IncomeStatementController())->index($this->companyOf($this->study), Study::findOrFail($this->study->id), true)['tableDataFormatted'];
	}

	private function total(array $series): float
	{
		return array_sum(array_map('floatval', array_slice($series, 0, 12, true)));
	}

	public function test_the_surplus_cash_interest_is_below_ebit_and_the_net_profit_does_not_move(): void
	{
		$is = $this->incomeStatement();
		$interest = $this->total((array) json_decode(DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')
			->where('study_id', $this->study->id)->value('interest_cash_surplus'), true));
		$this->assertGreaterThan(0, $interest, 'the study earns interest on its surplus cash');

		$this->assertArrayNotHasKey('Cash Surplus Interest', $is[0]['sub_items'], 'not in the revenue any more');
		$this->assertEqualsWithDelta(-$interest, $this->total($is[10]['sub_items']['Cash Surplus Interest']['data']), 0.01);
		$this->assertSame('Net Finance Cost', $is[10]['main_items']['finance_exp']['options']['title']);
		// EBT = EBIT − the net finance cost, and the net profit = EBT − tax, as before
		$ebit = $this->total($is[9]['main_items']['ebit']['data']);
		$finance = $this->total($is[10]['main_items']['finance_exp']['data']);
		$this->assertEqualsWithDelta($ebit - $finance, $this->total($is[11]['main_items']['ebt']['data']), 0.01);
		$this->assertArrayNotHasKey('Existing Branches Rent Liability Interest', $is[6]['sub_items'] ?? [], 'not in General Expense any more');
	}

	public function test_the_lease_interest_is_a_finance_cost_and_nothing_is_lost_from_the_net_profit(): void
	{
		// 10,000 a month of lease-liability interest (the fixture has none): it is in the net finance cost, not in General
		// Expense, and the net profit is still the profit the corporate tax is worked out on (study 140 has no tax)
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id', $this->study->id)
			->update(['rent_interest' => json_encode(array_fill(0, 12, 10000))]);
		$is = $this->incomeStatement();

		$this->assertEqualsWithDelta(120000, $this->total($is[10]['sub_items']['Existing Branches Rent Liability Interest']['data']), 0.01);
		$this->assertArrayNotHasKey('Existing Branches Rent Liability Interest', $is[6]['sub_items'] ?? []);
		$study = Study::findOrFail($this->study->id);
		$taxBase = $study->calculateCorporateTaxesFromIncomeStatementReport($study->incomeStatementReport, $study->getYearIndexWithItsMonths(), 0, 11, $study->getDateIndexWithDate());
		$this->assertEqualsWithDelta($taxBase['profit_before_tax'], $this->total($is[13]['main_items']['net-profit']['data']), 0.01,
			'every revenue and cost is still in the net profit: the lines moved, none was lost or counted twice');
	}

	public function test_the_opening_cash_is_last_months_closing_cash_and_the_equity_injection_is_a_line_of_its_own(): void
	{
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports')->where('study_id', $this->study->id)
			->update(['has_manual_equity_injection' => 1, 'manual_equity_injection' => json_encode([0 => 0, 1 => 0, 2 => 5000000])]);
		$this->incomeStatement();   // runs the cash flow
		$cashFlow = Study::findOrFail($this->study->id)->getCashInOutFlowViewVars()['tableDataFormatted'];

		$opening = $cashFlow[-1]['main_items']['cash-and-banks']['data'];
		$closing = $cashFlow[4]['main_items']['cash-and-bank-end-balance']['data'];
		$this->assertEqualsWithDelta($closing[1], $opening[2], 0.01, 'March opens with the February closing cash (the injection is not in it)');
		$this->assertEqualsWithDelta(5000000, $cashFlow[0]['sub_items']['Equity Injection']['data'][2], 0.01);
	}
}
