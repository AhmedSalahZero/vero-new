<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 (قرار اليوزر ، زي FinVero): التوريق.
 * * (1) القروض المباعة بتخرج من الشركة أول شهر البيع (قسطها في الشهر ده للمشتري) ، فالمشتري بيستلمه آخر الشهر: كل قسط
 * *     بيتخصم بعدد الشهور من البيع ، وشهر البيع = 1. Finance::npv كان بيسيب أول قسط من غير خصم ، وبيعد الأقساط مش
 * *     الشهور: قرض 1,000,000 بفايدة 18% اتباع بخصم 18% كان بيتقيّم 1,015,000 (ربح 15,000 من غير سبب).
 * * (2) رسوم السداد المبكر للبنك على الأصل اللي بيتسدد بدري بس ، مش على الفايدة المستحقة.
 * *
 * * المثال: دراسة 140 (الكوريدور صفر) ، قرض leasing بمليون في سبتمبر 2026 على 12 شهر بهامش 18% ، أقساط ربع سنوية ، والشركة
 * * بتمول 40% (البنك 600,000) ، واتباع في نوفمبر بخصم 18% ورسوم سداد مبكر 1%.
 */
class SecuritizationNpvTest extends NonBankingTestCase
{
	private const SEPTEMBER = 8;
	private const NOVEMBER = 10;

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function securitized(): array
	{
		$study = $this->loadStudyFixture('study_140');
		$categoryId = (int) $this->nb()->table('leasing_categories')->where('company_id', $study->company_id)->value('id');
		$this->nb()->table('leasing_revenue_stream_breakdowns')->insert([
			'category_id' => $categoryId, 'loan_nature' => 'fixed-at-end', 'loan_type' => 'normal', 'tenor' => 12, 'grace_period' => 0,
			'margin_rate' => 18, 'sensitivity_margin_rate' => 0, 'installment_interval' => 'quarterly', 'step_up' => 0, 'step_down' => 0,
			'step_interval' => 'annually', 'loan_amounts' => json_encode([self::SEPTEMBER => 1000000]), 'monthly_loan_amounts' => json_encode([self::SEPTEMBER => 1000000]),
			'study_id' => $study->id, 'company_id' => $study->company_id,
		]);
		$this->nb()->table('ecl_and_new_portfolio_funding_rates')->insert([
			'revenue_stream_type' => Study::LEASING, 'study_id' => $study->id, 'company_id' => $study->company_id,
			'admin_fees_rates' => json_encode([self::SEPTEMBER => 0]), 'ecl_rates' => json_encode([self::SEPTEMBER => 0]),
			'equity_funding_rates' => json_encode([self::SEPTEMBER => 40]), 'equity_funding_values' => json_encode([self::SEPTEMBER => 0]),
			'new_loans_funding_rates' => json_encode([self::SEPTEMBER => 60]), 'new_loans_funding_values' => json_encode([self::SEPTEMBER => 600000]),
			'monthly_new_loans_funding_values' => json_encode([]), 'monthly_admin_fees_amounts' => json_encode([]),
		]);
		// the bank lends at 20 % (the study's corridor is 0), so its quarterly loan has interest accrued at the sale
		$margins = json_decode((string) $this->nb()->table('general_and_reserve_assumptions')->where('study_id', $study->id)->value('bank_lending_margin_rates'), true);
		$margins['leasing'] = array_fill_keys(array_keys($margins['leasing']), 20);
		$this->nb()->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update(['bank_lending_margin_rates' => json_encode($margins)]);
		Study::findOrFail($study->id)->storeFixedLoans(new Request(), Study::LEASING, 'leasingRevenueStreamBreakdown');
		$this->nb()->table('securitizations')->insert([
			'revenue_stream_type' => Study::LEASING, 'disbursement_date' => self::SEPTEMBER, 'securitization_date' => self::NOVEMBER,
			'discount_rate' => 18, 'collection_revenue_rate' => 0, 'early_settlements_expense_rate' => 1, 'expense_amount' => 0,
			'study_id' => $study->id, 'company_id' => $study->company_id,
		]);
		Study::findOrFail($study->id)->calculateSecuritizationLoans();

		$loan = fn (string $type) => $this->nb()->table('loan_schedule_payments')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)->where('portfolio_loan_type', $type)->first();

		return [(array) $this->nb()->table('securitization_loan_schedules')->where('study_id', $study->id)->first(), $loan('portfolio'), $loan('bank_portfolio')];
	}

	public function test_each_installment_sold_is_discounted_by_the_months_since_the_sale(): void
	{
		[$sale, $client, $bank] = $this->securitized();

		$expected = 0.0;
		foreach (json_decode($client->schedulePayment, true) as $month => $installment) {
			if ($month >= self::NOVEMBER) {
				$expected += $installment / (1.015 ** ($month - self::NOVEMBER + 1));   // December's installment: 2 months
			}
		}
		$this->assertEqualsWithDelta($expected, (float) $sale['net_present_value'], 0.01);
		// sold at the loan's own rate, the sale gains (almost) nothing: −433.93 on 1,000,000 (the days of each month vs
		// 1.5 % a month); Finance::npv made it about +15,000
		$this->assertEqualsWithDelta(0, (float) $sale['securitization_profit_or_loss'], 1000);
	}

	public function test_the_early_settlement_fee_is_on_the_bank_principal_only(): void
	{
		[$sale, , $bank] = $this->securitized();

		$principal = (float) json_decode($bank->beginning, true)[self::NOVEMBER];
		$this->assertEqualsWithDelta(600000, $principal, 0.01, 'nothing repaid before the first quarter ends');
		$this->assertGreaterThan($principal, (float) $sale['bank_portfolio_end_balance_sum'], 'the bank is paid its accrued interest too');
		$this->assertEqualsWithDelta(6000, (float) $sale['early_settlements_expense_amount'], 0.01, '1 % × 600,000; it was 1 % × 610,000 (+ 10,000 = 600,000 × 20 % ÷ 12 of October) = 6,100');
	}
}
