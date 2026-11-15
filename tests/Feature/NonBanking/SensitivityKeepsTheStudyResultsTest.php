<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * * شاشة الـ sensitivity (V11 ، المرحلة 4 ، قرار اليوزر (أ)): تشغيلها بيكتب قروضها في جدولها بس
 * * (sensitivity_loan_schedule_payments) ، ومش بيلمس نتايج الدراسة.
 * * قبل كده كانت بتكتب إيراد وفوايد البنك وتحصيلات الـ leasing والإجارة والـ RF بالهامش الجديد فوق قائمة الدخل والتدفقات
 * * الأساسية ، وتعيد حساب الـ ECL الأساسي ، فالميزانية تبان مختلة لحد أي حفظ أساسي.
 *
 * * المثال: دراسة 140 ، قرض leasing بمليون في سبتمبر 2026 على 12 شهر ، الهامش 3.5% وهامش الـ sensitivity ‏6%.
 */
class SensitivityKeepsTheStudyResultsTest extends NonBankingTestCase
{
	public function test_a_sensitivity_run_writes_its_own_loans_and_leaves_the_study_results_as_they_are(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$categoryId = (int) $nb->table('leasing_categories')->where('company_id', $study->company_id)->value('id');
		$nb->table('leasing_revenue_stream_breakdowns')->insert([
			'category_id' => $categoryId, 'loan_nature' => 'fixed-at-end', 'loan_type' => 'normal', 'tenor' => 12, 'grace_period' => 0,
			'margin_rate' => 3.5, 'sensitivity_margin_rate' => 6, 'installment_interval' => 'monthly', 'step_up' => 0, 'step_down' => 0,
			'step_interval' => 'annually', 'loan_amounts' => json_encode([8 => 1000000]), 'monthly_loan_amounts' => json_encode([8 => 1000000]),
			'study_id' => $study->id, 'company_id' => $study->company_id,
		]);

		Study::findOrFail($study->id)->storeFixedLoans(new Request(), Study::LEASING, 'leasingRevenueStreamBreakdown');
		$incomeBefore = (array) $nb->table('income_statement_reports')->where('study_id', $study->id)->first();
		$cashBefore = (array) $nb->table('cashflow_statement_reports')->where('study_id', $study->id)->first();
		$loansBefore = $nb->table('loan_schedule_payments')->where('study_id', $study->id)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
		$baseRevenue = array_sum(json_decode($incomeBefore['leasing_revenue'], true));
		$this->assertGreaterThan(0, $baseRevenue, 'the base leasing loan earns interest');

		Study::findOrFail($study->id)->recalculateAllRevenuesLoans(new Request(), true);

		$this->assertSame($incomeBefore, (array) $nb->table('income_statement_reports')->where('study_id', $study->id)->first(), 'the income statement report is untouched');
		$this->assertSame($cashBefore, (array) $nb->table('cashflow_statement_reports')->where('study_id', $study->id)->first(), 'the cash flow report is untouched');
		$this->assertSame($loansBefore, $nb->table('loan_schedule_payments')->where('study_id', $study->id)->orderBy('id')->get()->map(fn ($r) => (array) $r)->all(), 'the study loans are untouched');

		$sensitivity = $nb->table('sensitivity_loan_schedule_payments')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)
			->where('portfolio_loan_type', 'portfolio')->get();
		$this->assertCount(1, $sensitivity);
		$sensitivityInterest = array_sum(json_decode($sensitivity->first()->interestAmount, true));
		$this->assertGreaterThan($baseRevenue, $sensitivityInterest, 'the sensitivity loan is priced with the 6 % margin');
	}
}
