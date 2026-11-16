<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 (دراسة 103 ، كل دراسات الإنتاج في البوابة): القروض المستلمة من البنك في التدفقات كانت الأرقام اللي صفحة
 * * المنتج بتحسبها في المتصفح وبتقربها لأقرب جنيه ، ورصيد قرض البنك في الميزانية متحسب من الصرف × نسبة تمويل البنك
 * * من غير تقريب. فالكاش والقرض بيختلفوا بالكسر ، والميزانية بتختل: 103 اختلت 1.66 لحد يونيو 2026.
 *
 * * المثال: صرف leasing ‏222,222,222.50 في سبتمبر 2026 ، وتمويل الشركة 40% ، يعني البنك بيسلّف 60% = 133,333,333.50.
 * * الصفحة كانت حافظة 133,333,334 ، والتدفقات كانت بتاخدها.
 */
class BankLoansReceivedTest extends NonBankingTestCase
{
	private const SEPTEMBER = 8;

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	/** a funding row as the product page saves it: the equity share, and the bank's loans as the browser rounded them */
	private function fundingRow(Study $study, string $streamType, float $equityRate, float $browserLoan): void
	{
		$this->nb()->table('ecl_and_new_portfolio_funding_rates')->insert([
			'revenue_stream_type' => $streamType, 'study_id' => $study->id, 'company_id' => $study->company_id,
			'admin_fees_rates' => json_encode([self::SEPTEMBER => 0]), 'ecl_rates' => json_encode([self::SEPTEMBER => 0]),
			'equity_funding_rates' => json_encode([self::SEPTEMBER => $equityRate]), 'equity_funding_values' => json_encode([self::SEPTEMBER => 0]),
			'new_loans_funding_rates' => json_encode([self::SEPTEMBER => 100 - $equityRate]), 'new_loans_funding_values' => json_encode([self::SEPTEMBER => $browserLoan]),
			'monthly_new_loans_funding_values' => json_encode([]), 'monthly_admin_fees_amounts' => json_encode([]),
		]);
	}

	public function test_the_leasing_bank_loans_received_are_the_loans_the_bank_balance_is_built_from(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$categoryId = (int) $this->nb()->table('leasing_categories')->where('company_id', $study->company_id)->value('id');
		$this->nb()->table('leasing_revenue_stream_breakdowns')->insert([
			'category_id' => $categoryId, 'loan_nature' => 'fixed-at-end', 'loan_type' => 'normal', 'tenor' => 12, 'grace_period' => 0,
			'margin_rate' => 3.5, 'sensitivity_margin_rate' => 0, 'installment_interval' => 'monthly', 'step_up' => 0, 'step_down' => 0,
			'step_interval' => 'annually', 'loan_amounts' => json_encode([self::SEPTEMBER => 222222222.50]), 'monthly_loan_amounts' => json_encode([self::SEPTEMBER => 222222222.50]),
			'study_id' => $study->id, 'company_id' => $study->company_id,
		]);
		$this->fundingRow($study, Study::LEASING, 40, 133333334);

		Study::findOrFail($study->id)->storeFixedLoans(new Request(), Study::LEASING, 'leasingRevenueStreamBreakdown');

		$bankLoan = $this->nb()->table('loan_schedule_payments')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)
			->where('portfolio_loan_type', 'bank_portfolio')->first();
		$this->assertNotNull($bankLoan, 'the bank funds 60 % of the loan');
		$this->assertEqualsWithDelta(133333333.50, (float) json_decode($bankLoan->beginning, true)[self::SEPTEMBER + 1], 0.001, 'the bank loan: 222,222,222.50 × 60 %');

		$received = json_decode((string) $this->nb()->table('cashflow_statement_reports')->where('study_id', $study->id)->value('leasing_loan_withdrawal_amount'), true);
		$this->assertEqualsWithDelta(133333333.50, (float) $received[self::SEPTEMBER], 0.001, 'the cash received; it was the page\'s 133,333,334');
		$this->assertEqualsWithDelta(0, (float) $received[self::SEPTEMBER + 1], 0.001);

		// the cash flow page shows the same figure as the stream's "Loan Withdrawal Amount"
		$shown = Study::findOrFail($study->id)->getEclAndNewPortfolioFundingRatesForStreamType(Study::LEASING)->monthly_new_loans_funding_values;
		$this->assertEqualsWithDelta(133333333.50, (float) $shown[self::SEPTEMBER], 0.001);
	}

	public function test_every_loan_stream_is_recalculated_in_a_study_without_loans(): void
	{
		// study 140 has no leasing, RF or Ijara row (the gate's first run of this fix stopped on studies without RF)
		$study = $this->loadStudyFixture('study_140');

		Study::findOrFail($study->id)->recalculateAllRevenuesLoans(new Request());

		$report = (array) $this->nb()->table('cashflow_statement_reports')->where('study_id', $study->id)->first();
		foreach (['leasing', 'reverse-factoring', 'ijara'] as $stream) {
			$received = (array) json_decode((string) $report[$stream.'_loan_withdrawal_amount'], true);
			$this->assertSame(0.0, (float) array_sum($received), "no {$stream} bank loans");
		}
	}
}
