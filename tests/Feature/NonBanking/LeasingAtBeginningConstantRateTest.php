<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 3 (مقارنة كل القروض مع FinVero): قرض leasing «Fixed At Beginning» في دراسة الكوريدور والهامش فيها ثابتين.
 * * Study::storeFixedLoans بيستخدم __calculate بسعر واحد في الحالة دي ، وكان بيبعت للـ at-beginning service نفس arguments
 * * بتاعة الـ at-end (عدد أيام ، تسعير ، بالأيام): الـ service دي مالهاش الـ arguments دي ، فـ null وصل لـ $installmentsAlreadyPaid
 * * (int) والحفظ كان بيقع بـ TypeError. مفيش قرض زي ده في الإنتاج (كل الـ leasing «Fixed At End»).
 * * المثال: دراسة 140 (كوريدور 0 وهوامش 0) ، مليون في سبتمبر 2026 على 12 شهر بهامش 18%.
 */
class LeasingAtBeginningConstantRateTest extends NonBankingTestCase
{
	public function test_a_loan_paid_at_the_beginning_at_a_constant_rate_is_calculated(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$categoryId = (int) $nb->table('leasing_categories')->where('company_id', $study->company_id)->value('id');
		$nb->table('leasing_revenue_stream_breakdowns')->insert([
			'category_id' => $categoryId, 'loan_nature' => 'fixed-at-beginning', 'loan_type' => 'normal', 'tenor' => 12, 'grace_period' => 0,
			'margin_rate' => 18, 'sensitivity_margin_rate' => 0, 'installment_interval' => 'monthly', 'step_up' => 0, 'step_down' => 0,
			'step_interval' => 'annually', 'loan_amounts' => json_encode([8 => 1000000]), 'monthly_loan_amounts' => json_encode([8 => 1000000]),
			'study_id' => $study->id, 'company_id' => $study->company_id,
		]);

		Study::findOrFail($study->id)->storeFixedLoans(new Request(), Study::LEASING, 'leasingRevenueStreamBreakdown');

		$loan = $nb->table('loan_schedule_payments')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)->where('portfolio_loan_type', 'portfolio')->first();
		$this->assertNotNull($loan, 'it threw a TypeError');
		$payments = array_values(array_filter(json_decode($loan->schedulePayment, true), fn ($v) => (float) $v > 0));
		$this->assertCount(12, $payments, 'paid at the beginning: September 2026 to August 2027');
		$balances = json_decode($loan->endBalance, true);
		$this->assertEqualsWithDelta(0, (float) end($balances), 1, 'repaid');
	}
}
