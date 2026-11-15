<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Http\Requests\StoreOpeningBalancesRequest;
use App\Models\NonBankingService\CashAndBankOpeningBalance;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\FixedAssetOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * قائمة الدخل والميزانية في Vero بعد إصلاحات المرحلة 3 ، على دراسة 140 الحقيقية (Misr Finance: الدراسة من يناير
 * * 2026 والتشغيل من سبتمبر 2026 ، تمويل متناهي الصغر ، أرصدة افتتاحية).
 */
class StatementsPhaseThreeTest extends NonBankingTestCase
{
	private Study $study;

	protected function setUp(): void
	{
		parent::setUp();
		$this->study = $this->loadStudyFixture('study_140');
	}

	/** income statement → cash flow (+ ODA) → balance sheet, in the order the pages run them */
	private function statements(): array
	{
		$company = $this->companyOf($this->study);
		$is = (new IncomeStatementController())->index($company, Study::findOrFail($this->study->id), true)['tableDataFormatted'];
		$study = Study::findOrFail($this->study->id);
		$cf = $study->getCashInOutFlowViewVars();
		$study->cashFlowForOdas($cf['netCashBeforeWorking']);
		$bs = Study::findOrFail($this->study->id)->getBalanceSheetViewVars(true)['tableDataFormatted'];

		return [$is, $bs];
	}

	private function months(): array
	{
		return range(0, 11);
	}

	private function at(array $series, int $month): float
	{
		return (float) ($series[$month] ?? 0);
	}

	public function test_ebitda_is_gross_profit_less_sg_and_a_and_ebit_takes_the_ecl_and_depreciation_off_it(): void
	{
		// the ECL, the depreciation and the right-of-use amortisation are in the section after EBITDA: EBITDA − EBIT is
		// exactly that section (EBITDA used to add the depreciation back again, so it came out higher by it).
		// Study 140 has no depreciation of its own: an opening asset depreciates 10,000 a month for 6 months.
		$asset = FixedAssetOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$asset->monthly_depreciation = 10000;
		$asset->monthly_counts = 6;
		$asset->save();
		[$is] = $this->statements();
		$ebitda = $is[7]['main_items']['ebitda']['data'];
		$section = $is[8]['main_items']['ecl-and-depreciation-expenses']['data'];
		$ebit = $is[9]['main_items']['ebit']['data'];
		$depreciation = $is[8]['sub_items']['total-depreciation']['data'] ?? [];

		$this->assertGreaterThan(0, array_sum($depreciation), 'the study has depreciation');
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta($this->at($section, $month), $this->at($ebitda, $month) - $this->at($ebit, $month), 0.01, "month {$month}");
		}
	}

	public function test_the_corporate_tax_share_of_revenue_divides_the_tax_by_the_revenue(): void
	{
		// study 140 makes a loss in 2026 and pays no tax: with a 22.5 % rate and 500,000,000 of admin fees in December
		// the year is profitable, so the tax is not 0. The share was empty every month, and the yearly one divided the
		// yearly sales expenses by the monthly taxes
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$nb->table('studies')->where('id', $this->study->id)->update(['corporate_taxes_rate' => 22.5]);
		$adminFees = (array) json_decode((string) $nb->table('income_statement_reports')->where('study_id', $this->study->id)->value('total_admin_fees'), true);
		$adminFees[11] = (float) ($adminFees[11] ?? 0) + 500000000;
		$nb->table('income_statement_reports')->where('study_id', $this->study->id)->update(['total_admin_fees' => json_encode($adminFees)]);
		$company = $this->companyOf($this->study);
		$is = (new IncomeStatementController())->index($company, Study::findOrFail($this->study->id), true)['tableDataFormatted'];
		$revenue = $is[0]['main_items']['sales-revenue']['data'];
		$tax = $is[12]['main_items']['corporate-taxes']['data'];
		$share = $is[12]['main_items']['% Of Revenue']['data'];

		$this->assertGreaterThan(0, array_sum($tax), 'the year pays tax');
		$this->assertGreaterThan(0, $this->at($share, 11));
		foreach ($this->months() as $month) {
			$expected = $this->at($revenue, $month) ? $this->at($tax, $month) / $this->at($revenue, $month) * 100 : 0;
			$this->assertEqualsWithDelta($expected, $this->at($share, $month), 0.0001, "month {$month}");
		}
		$yearRevenue = array_sum(array_map(fn ($m) => $this->at($revenue, $m), $this->months()));
		$yearTax = array_sum(array_map(fn ($m) => $this->at($tax, $m), $this->months()));
		$this->assertEqualsWithDelta($yearRevenue ? $yearTax / $yearRevenue * 100 : 0, (float) array_values($is[12]['main_items']['% Of Revenue']['year_total'])[0], 0.0001);
	}

	public function test_the_capital_is_on_the_balance_sheet_from_the_first_month_and_it_balances_before_the_operation(): void
	{
		// the operation starts in September 2026: January–August used to show no paid-up capital and no reserve, so
		// the balance sheet was out by 230,031,996 in those months
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		[, $bs] = $this->statements();
		$capital = (float) DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('equity_opening_balances')->where('study_id', $this->study->id)->value('paid_up_capital_amount');

		$this->assertGreaterThan(0, $capital);
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta($capital, $this->at($bs[13]['sub_items']['Paid Up Capital']['data'], $month), 0.01, "month {$month}");
			$this->assertEqualsWithDelta(0, $this->at($bs[14]['main_items'][14]['data'], $month), 0.01, "check error, month {$month}");
		}
	}

	public function test_a_difference_under_one_thousand_shows_on_the_balance_sheet(): void
	{
		// 500 more opening retained earnings than the opening balance sheet has (without saving the page): the check
		// line shows −500 every month (anything between −1,000 and 1,000 used to show as 0)
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('equity_opening_balances')->where('study_id', $this->study->id)->increment('retained_earnings', 500);
		[, $bs] = $this->statements();

		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta(-500, $this->at($bs[14]['main_items'][14]['data'], $month), 0.01, "month {$month}");
		}
	}

	public function test_a_distribution_declared_from_the_opening_retained_earnings_leaves_them_and_the_balance_sheet_balances(): void
	{
		// 300,000 declared in March 2026 and paid in May: retained earnings are 300,000 lower from March, the
		// dividends payable is 300,000 from March to April, and the check line stays 0 (it was out by 300,000)
		$equity = EquityOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$before = $this->retainedEarnings();
		// the page saves every month of the study (0 where nothing is declared or paid)
		$months = count($this->study->getStudyDates());
		$equity->retained_earning_distribution_amounts = array_replace(array_fill(0, $months, 0), [2 => 300000]);
		$equity->retained_earning_distribution_payments = array_replace(array_fill(0, $months, 0), [4 => 300000]);
		$equity->save();
		[, $bs] = $this->statements();

		$this->assertEqualsWithDelta(300000, $this->at($bs[11]['sub_items']['Dividends Payable']['data'], 3), 0.01, 'payable in April');
		$this->assertEqualsWithDelta(0, $this->at($bs[11]['sub_items']['Dividends Payable']['data'], 4), 0.01, 'paid in May');
		$this->assertEqualsWithDelta($before[1], $this->at($bs[13]['sub_items']['Retained Earnings']['data'], 1), 0.01, 'February');
		foreach ([2, 3, 6, 11] as $month) {
			$this->assertEqualsWithDelta($before[$month] - 300000, $this->at($bs[13]['sub_items']['Retained Earnings']['data'], $month), 0.01, "month {$month}");
		}
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta(0, $this->at($bs[14]['main_items'][14]['data'], $month), 0.01, "check error, month {$month}");
		}
	}

	/** the retained earnings line of the balance sheet with the study as it is (no distributions declared) */
	private function retainedEarnings(): array
	{
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		[, $bs] = $this->statements();

		return array_map(fn ($m) => $this->at($bs[13]['sub_items']['Retained Earnings']['data'], $m), $this->months());
	}

	public function test_an_opening_fixed_asset_keeps_what_is_left_of_it_after_its_last_depreciation_month(): void
	{
		// an opening asset depreciated 44,444 a month for 4 months with 177,778 left to depreciate: 2 stay on the
		// balance sheet from May with no more depreciation, and the balance sheet still balances (the 2 used to drop
		// off the balance sheet in May with no expense — study 127 was out by −2.00 from May to September)
		$asset = FixedAssetOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$asset->gross_amount = 1600000;
		$asset->accumulated_depreciation = 1422222;
		$asset->monthly_depreciation = 44444;
		$asset->monthly_counts = 4;
		$asset->save();
		// the opening balance sheet takes the same asset value (the page checks that the opening balance sheet balances)
		$difference = 177778 - $this->openingNetValueBefore;
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('equity_opening_balances')->where('study_id', $this->study->id)->increment('retained_earnings', $difference);
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		[, $bs] = $this->statements();

		$statement = FixedAssetOpeningBalance::findOrFail($asset->id)->statement;
		$this->assertEqualsWithDelta(2, $statement['end_balance'][3], 0.01, 'April: after the 4th month');
		foreach ([4, 8, 11] as $month) {
			$this->assertEqualsWithDelta(2, $statement['end_balance'][$month], 0.01, "month {$month}");
			$this->assertArrayNotHasKey($month, $statement['monthly_depreciation']);
		}
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta(0, $this->at($bs[14]['main_items'][14]['data'], $month), 0.01, "check error, month {$month}");
		}
	}

	public function test_the_opening_balances_page_shows_no_depreciation_warning_and_the_leftover_stays_on_the_balance_sheet(): void
	{
		// study 141's licence — 1,192,932 gross, 866,934 depreciated (325,998 net), 57,055 a month for 5 months = 285,275,
		// so 40,723 stays on the balance sheet after the last month. Phase 4 warned about it above the assets; the user
		// removed that warning (study 154: "we don't want it"), the asset's statement is unchanged.
		$asset = FixedAssetOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$asset->gross_amount = 1192932;
		$asset->accumulated_depreciation = 866934;
		$asset->monthly_depreciation = 57055;
		$asset->monthly_counts = 5;
		$asset->save();

		$statement = FixedAssetOpeningBalance::findOrFail($asset->id)->statement;
		$this->assertEqualsWithDelta(40723, $statement['end_balance'][4], 0.01, 'after the 5th month');
		$this->assertEqualsWithDelta(40723, $statement['end_balance'][11], 0.01, 'and it stays to the end of the study');

		$html = $this->openingBalancesSection();
		$this->assertStringNotContainsString('stays on the balance sheet', $html);
		$this->assertStringNotContainsString('Check the monthly depreciation or the months', $html);
		$this->assertStringNotContainsString('alert-warning', $html);
	}

	private function openingBalancesSection(): string
	{
		$study = Study::findOrFail($this->study->id);

		return view('non_banking_services.openingBalances._content', $study->getOpeningBalancesViewVars() + ['company' => $this->companyOf($study)])->render();
	}

	private float $openingNetValueBefore = 0;

	protected function assertPreConditions(): void
	{
		parent::assertPreConditions();
		$asset = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_opening_balances')->where('study_id', $this->study->id)->first();
		$this->openingNetValueBefore = $asset ? (float) $asset->gross_amount - (float) $asset->accumulated_depreciation : 0;
	}

	public function test_a_non_performing_opening_portfolio_is_on_the_balance_sheet_with_its_ecl_and_it_balances(): void
	{
		// 1,000,000 non-performing at the start with a 100,000 ECL (10 %), nothing collected, so the opening equity is
		// 900,000 higher: the existing customer outstanding carries the 1,000,000, the accumulated ECL the 100,000, and
		// the balance sheet balances every month (the non-performing portfolio used to be 0 on it, and its ECL off it)
		$before = $this->customerOutstanding();
		$cash = CashAndBankOpeningBalance::where('study_id', $this->study->id)->firstOrFail();
		$months = count($this->study->getStudyDates());
		$cash->non_performing_outstanding = 1000000;
		$cash->non_performing_expected_credit_loss = -100000;
		$cash->non_performing_ecl_existing_rate = 10;
		$cash->non_performing_payload = array_fill(0, $months, 0);
		$cash->non_performing_interests = array_fill(0, $months, 0);
		$cash->save();
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('equity_opening_balances')->where('study_id', $this->study->id)->increment('retained_earnings', 900000);
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		[, $bs] = $this->statements();

		$this->assertEqualsWithDelta($before['existing'][0] + 1000000, $this->at($bs[6]['sub_items']['Existing Customer Outstanding']['data'], 0), 0.01);
		$this->assertEqualsWithDelta($before['ecl'][0] - 100000, $this->at($bs[6]['sub_items']['Accumulated ECL']['data'], 0), 0.01);
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta(0, $this->at($bs[14]['main_items'][14]['data'], $month), 0.01, "check error, month {$month}");
		}
	}

	/** the existing customer outstanding and the accumulated ECL lines of the balance sheet with the study as it is */
	private function customerOutstanding(): array
	{
		EquityOpeningBalance::where('study_id', $this->study->id)->get()->each->save();
		[, $bs] = $this->statements();

		return [
			'existing' => array_map(fn ($m) => $this->at($bs[6]['sub_items']['Existing Customer Outstanding']['data'], $m), $this->months()),
			'ecl' => array_map(fn ($m) => $this->at($bs[6]['sub_items']['Accumulated ECL']['data'] ?? [], $m), $this->months()),
		];
	}

	public function test_the_opening_balance_check_counts_the_non_performing_portfolio(): void
	{
		// assets: 1,000,000 performing + 500,000 non-performing − 50,000 ECL on it = 1,450,000; equity 1,450,000:
		// balanced (the non-performing lines used to be left out, so the same page was 450,000 out)
		$request = StoreOpeningBalancesRequest::create('/', 'POST', [
			'cashAndBankOpeningBalances' => [['cash_and_bank_amount' => '0', 'customer_receivable_amount' => '1,000,000', 'expected_credit_loss' => '0',
				'non_performing_outstanding' => '500,000', 'non_performing_expected_credit_loss' => '-50,000']],
			'equityOpeningBalances' => [['paid_up_capital_amount' => '1,450,000', 'legal_reserve' => '0', 'retained_earnings' => '0']],
		]);
		(fn () => $this->prepareForValidation())->call($request);

		$this->assertEqualsWithDelta(0, (float) $request->get('total_liabilities_and_equity_minus_total_assets'), 0.01);
	}

	public function test_the_saved_balance_sheet_keeps_the_fixed_assets_not_the_loans_that_financed_them(): void
	{
		[, $bs] = $this->statements();
		$saved = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('balance_sheets')->where('study_id', $this->study->id)->value('monthly_fixed_assets');
		$saved = (array) json_decode((string) $saved, true);

		$this->assertGreaterThan(0, $this->at($bs[1]['main_items'][1]['data'], 0));
		foreach ($this->months() as $month) {
			$this->assertEqualsWithDelta($this->at($bs[1]['main_items'][1]['data'], $month), $this->at($saved, $month), 0.01, "month {$month}");
		}
	}
}
