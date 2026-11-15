<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\Study;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use Illuminate\Support\Facades\DB;

/**
 * * تطويل الدراسة (سنة → 3 سنين ، زي دراسة 154): الشهور والسنين الجديدة مالهاش مدخلات محفوظة.
 * * قرار اليوزر: النسب والأسعار والسياسات وحجم الشغل بتاخد آخر قيمة محفوظة ، والأحداث (التعيينات ، فتح الفروع ،
 * * ضخ رأس المال) مابتتكررش. مصروفات الفروع والإيجار بيكمّلوا لآخر الدراسة الجديدة. بعدها الدراسة كلها بتتحسب تاني.
 * * قبل كده: الـ ECL والرسوم والهوامش ونسب التوزيع بقت صفر في السنين الجديدة ، والتمويل متناهي الصغر وقف عند آخر السنة الأولى.
 *
 * * الدراسة المستخدمة: Vero 140 "Maak Outlook" (يناير–ديسمبر 2026 ، التشغيل من سبتمبر = الشهور 8–11).
 */
class StudyExtensionCarriesInputsForwardTest extends NonBankingTestCase
{
	private const FUNDING_RATES_ID = 312;          // the microfinance ECL / admin fees / funding row

	private const LOAN_OFFICERS_ROW_ID = 184;      // 450 existing officers, 5 cases a month each

	private const MANPOWER_ID = 616;

	private const ALL_BRANCHES_EXPENSE_ID = 11361;

	private const NEW_BRANCHES_EXPENSE_ID = 11362;

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	public function test_microfinance_keeps_lending_through_the_new_years(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->extendToThreeYears($study);

		$revenue = (array) json_decode((string) $this->nb()->table('income_statement_reports')->where('study_id', $study->id)->value('microfinance_revenue'), true);
		$this->assertGreaterThan(0, $this->sumOfMonths($revenue, 12, 23), 'microfinance revenue in 2027');
		$this->assertGreaterThan(0, $this->sumOfMonths($revenue, 24, 35), 'microfinance revenue in 2028');

		$officers = $this->nb()->table('microfinance_loan_officers_cases_projects')->where('id', self::LOAN_OFFICERS_ROW_ID)->first();
		$this->assertEqualsWithDelta(5, (float) json_decode($officers->existing_cases, true)[35], 0.0001);
		$this->assertEqualsWithDelta(450 * 5, (float) json_decode($officers->total_existing_officers_cases_count, true)[35], 0.0001);
	}

	public function test_rates_and_policies_take_their_last_value_into_the_new_months_and_years(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$months = fn (float $value) => json_encode([8 => $value, 9 => $value, 10 => $value, 11 => $value]);
		$this->nb()->table('ecl_and_new_portfolio_funding_rates')->where('id', self::FUNDING_RATES_ID)->update([
			'ecl_rates' => $months(2),
			'admin_fees_rates' => $months(1.5),
			'equity_funding_rates' => json_encode(['by-odas' => [8 => 30, 9 => 30, 10 => 30, 11 => 30]]),
			'new_loans_funding_rates' => json_encode(['by-odas' => [8 => 70, 9 => 70, 10 => 70, 11 => 70]]),
		]);
		$this->nb()->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_base_lending_corridor_rates' => $months(20),
			'cbe_lending_corridor_rates' => $months(22),
			'bank_lending_margin_rates' => json_encode(['leasing' => [8 => 3, 9 => 3, 10 => 3, 11 => 3], 'microfinance' => [8 => 4, 9 => 4, 10 => 4, 11 => 4]]),
			'odas_bank_lending_margin_rates' => $months(2),
			'credit_interest_rate_for_surplus_cash' => $months(12),
			'min_cash_balances' => $months(1000),
			'employee_profit_share_rates' => json_encode([10]),
			'shareholders_dividend_payout_ratios' => json_encode([25]),
			'shareholders_dividend_in_cash_or_shares' => json_encode(['in_shares']),
		]);

		$this->extendToThreeYears($study);

		$funding = $this->nb()->table('ecl_and_new_portfolio_funding_rates')->where('id', self::FUNDING_RATES_ID)->first();
		foreach ([12, 35] as $month) {
			$this->assertEqualsWithDelta(2, (float) json_decode($funding->ecl_rates, true)[$month], 0.0001, "ECL rate, month {$month}");
			$this->assertEqualsWithDelta(1.5, (float) json_decode($funding->admin_fees_rates, true)[$month], 0.0001, "admin fees, month {$month}");
			$this->assertEqualsWithDelta(30, (float) json_decode($funding->equity_funding_rates, true)['by-odas'][$month], 0.0001, "equity funding, month {$month}");
			$this->assertEqualsWithDelta(70, (float) json_decode($funding->new_loans_funding_rates, true)['by-odas'][$month], 0.0001, "new loans funding, month {$month}");
		}

		$assumption = $this->nb()->table('general_and_reserve_assumptions')->where('study_id', $study->id)->first();
		foreach (['cbe_base_lending_corridor_rates' => 20, 'cbe_lending_corridor_rates' => 22, 'odas_bank_lending_margin_rates' => 2, 'credit_interest_rate_for_surplus_cash' => 12, 'min_cash_balances' => 1000] as $column => $value) {
			$series = json_decode($assumption->{$column}, true);
			$this->assertEqualsWithDelta($value, (float) $series[12], 0.0001, "{$column}, month 12");
			$this->assertEqualsWithDelta($value, (float) $series[35], 0.0001, "{$column}, month 35");
			$this->assertEqualsWithDelta($value, (float) $series[8], 0.0001, "{$column}: a saved month is kept");
		}
		$margins = json_decode($assumption->bank_lending_margin_rates, true);
		$this->assertEqualsWithDelta(3, (float) $margins['leasing'][35], 0.0001);
		$this->assertEqualsWithDelta(4, (float) $margins['microfinance'][35], 0.0001);
		$this->assertEquals([10, 10, 10], array_map('floatval', json_decode($assumption->employee_profit_share_rates, true)));
		$this->assertEquals([25, 25, 25], array_map('floatval', json_decode($assumption->shareholders_dividend_payout_ratios, true)));
		$this->assertSame(['in_shares', 'in_shares', 'in_shares'], json_decode($assumption->shareholders_dividend_in_cash_or_shares, true));
	}

	public function test_the_leasing_volume_repeats_its_last_month(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$breakdownId = $this->nb()->table('leasing_revenue_stream_breakdowns')->insertGetId([
			'category_id' => 386,
			'loan_nature' => 'fixed-at-end',
			'loan_type' => 'normal',
			'tenor' => 12,
			'grace_period' => 0,
			'margin_rate' => 5,
			'installment_interval' => 'monthly',
			'loan_amounts' => json_encode([8 => 100, 9 => 200, 10 => 300, 11 => 400]),
			'study_id' => $study->id,
			'company_id' => $study->company_id,
		]);

		$this->extendToThreeYears($study);

		$amounts = json_decode((string) $this->nb()->table('leasing_revenue_stream_breakdowns')->where('id', $breakdownId)->value('loan_amounts'), true);
		$this->assertEqualsWithDelta(100, (float) $amounts[8], 0.0001, 'a saved month is kept');
		$this->assertEqualsWithDelta(400, (float) $amounts[12], 0.0001);
		$this->assertEqualsWithDelta(400, (float) $amounts[35], 0.0001);
	}

	public function test_events_are_not_repeated(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->nb()->table('microfinance_loan_officers_cases_projects')->where('id', self::LOAN_OFFICERS_ROW_ID)
			->update(['hiring' => json_encode(array_merge(array_fill(0, 11, '0'), ['2']))]);
		$hiringCounts = json_encode([8 => 0, 9 => 0, 10 => 0, 11 => 1]);
		$this->nb()->table('manpowers')->where('id', self::MANPOWER_ID)->update(['hiring_counts' => $hiringCounts]);
		$this->nb()->table('cashflow_statement_reports')->where('study_id', $study->id)->update(['manual_equity_injection' => json_encode([11 => 1000])]);
		$openings = $this->nb()->table('new_branch_microfinance_opening_projections')->where('study_id', $study->id)->count();

		$this->extendToThreeYears($study);

		$hiring = json_decode((string) $this->nb()->table('microfinance_loan_officers_cases_projects')->where('id', self::LOAN_OFFICERS_ROW_ID)->value('hiring'), true);
		$this->assertSame([], array_filter($hiring, fn ($count, $month) => $month > 11 && (float) $count != 0, ARRAY_FILTER_USE_BOTH), 'no loan officer hired after December 2026');
		$this->assertSame($hiringCounts, $this->nb()->table('manpowers')->where('id', self::MANPOWER_ID)->value('hiring_counts'));
		$this->assertSame([11 => 1000], json_decode((string) $this->nb()->table('cashflow_statement_reports')->where('study_id', $study->id)->value('manual_equity_injection'), true));
		$this->assertSame($openings, $this->nb()->table('new_branch_microfinance_opening_projections')->where('study_id', $study->id)->count());
	}

	public function test_branch_expenses_run_to_the_new_end_and_the_rent_covers_the_study(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->nb()->table('expenses')->where('id', self::ALL_BRANCHES_EXPENSE_ID)->update(['amount' => 500]);

		$this->extendToThreeYears($study);

		foreach ([self::ALL_BRANCHES_EXPENSE_ID, self::NEW_BRANCHES_EXPENSE_ID] as $expenseId) {
			$this->assertSame(35, (int) Expense::findOrFail($expenseId)->end_date, "expense {$expenseId} runs to December 2028");
		}
		$amounts = (array) Expense::findOrFail(self::ALL_BRANCHES_EXPENSE_ID)->monthly_repeating_amounts;
		$this->assertEqualsWithDelta(500, (float) $amounts[12], 0.0001);
		$this->assertEqualsWithDelta(500, (float) $amounts[35], 0.0001, 'one existing branch × 500');
		$this->assertSame(36, (int) Study::findOrFail($study->id)->right_of_use_rent['duration'], 'the branch rent covers the three years');
	}

	/** Edits the study the way the study page does: same start, now three years (to December 2028). */
	private function extendToThreeYears(Study $study): Study
	{
		$this->actAs($this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]))
			->putJson(route('non.banking.update.study', ['company' => $study->company_id, 'study' => $study->id]), [
				'name' => $study->name,
				'study_start_date' => '2026-01',
				'study_end_date' => '2028-12-01',
				'duration_in_years' => 3,
				'operation_start_date' => '2026-09',
				'has_micro_finance' => 1,
			])
			->assertOk();
		$study = $study->fresh();
		NonBankingServiceProvider::bindStudyDates($study);

		return $study;
	}

	private function sumOfMonths(array $series, int $from, int $to): float
	{
		$total = 0.0;
		for ($month = $from; $month <= $to; $month++) {
			$total += (float) ($series[$month] ?? 0);
		}

		return $total;
	}
}
