<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Http\Controllers\NonBankingServices\ValuationController;
use App\Models\NonBankingService\EquityOpeningBalance;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 (قرار اليوزر): التقييم مايتغيرش بتغيير العرض في المرحلة 8. المرحلة 8 نزّلت فايدة فائض النقدية وفايدة
 * * التزامات الإيجار تحت الـ EBIT في قائمة الدخل ، والـ DCF بيفضل على الـ EBIT اللي كان قبلها:
 * * EBIT + فايدة فائض النقدية − فايدة الإيجار. على دراسة 140 بفايدة كاش 12% وفايدة إيجار 10,000 في الشهر (120,000 في السنة).
 */
class ValuationKeepsTheOldEbitTest extends NonBankingTestCase
{
	public function test_the_dcf_takes_the_ebit_before_the_presentation_change(): void
	{
		$study = $this->loadStudyFixture('study_140');
		foreach (EquityOpeningBalance::where('study_id', $study->id)->get() as $equity) {
			$equity->save();
		}
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update(['credit_interest_rate_for_surplus_cash' => json_encode(array_fill(0, 24, 12))]);
		$nb->table('income_statement_reports')->where('study_id', $study->id)->update(['rent_interest' => json_encode(array_fill(0, 12, 10000))]);
		(new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true);

		$study = Study::findOrFail($study->id);
		$statementEbit = (array) $study->incomeStatement->ebit;
		$cashInterest = array_sum(array_slice(array_map('floatval', (array) $study->incomeStatementReport->interest_cash_surplus), 0, 12, true));
		$this->assertGreaterThan(0, $cashInterest, 'the study earns interest on its surplus cash');

		$dcfEbit = ValuationController::ebitBeforeThePresentationChange($study, $statementEbit);

		$firstYear = array_key_first($statementEbit);
		$this->assertEqualsWithDelta((float) $statementEbit[$firstYear] + $cashInterest - 120000, $dcfEbit[$firstYear], 0.01);
		// and what the DCF table shows (by year index)
		$view = (new ValuationController())->index($this->companyOf($study), $study);
		$this->assertEqualsWithDelta($dcfEbit[$firstYear], array_values($view->getData()['formattedDcfMethod']['ebit'])[0], 0.01);
	}

	public function test_the_depreciation_the_dcf_adds_back_is_the_depreciation_of_the_income_statement(): void
	{
		// it was never written (study 140 had none, and its Valuation page stopped with a TypeError)
		$study = $this->loadStudyFixture('study_140');
		$is = (new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true)['tableDataFormatted'];

		$stored = (array) Study::findOrFail($study->id)->incomeStatement->total_depreciation;
		$shown = null;
		foreach ($is as $section) {
			foreach ($section['sub_items'] ?? [] as $item) {
				if (($item['options']['title'] ?? '') === 'Depreciation Expense') {
					$shown = $item['year_total'];
				}
			}
		}
		$this->assertNotNull($shown, 'the income statement has a depreciation line');
		$this->assertNotEmpty($stored);
		foreach ($shown as $year => $amount) {
			$this->assertEqualsWithDelta((float) $amount, (float) ($stored[$year] ?? 0), 0.01, "year {$year}");
		}
	}
}
