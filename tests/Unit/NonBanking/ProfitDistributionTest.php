<?php

namespace Tests\Unit\NonBanking;

use App\ReadyFunctions\ProfitDistribution;
use PHPUnit\Framework\TestCase;

/**
 * * توزيع ربح السنة (المرحلة 5): معادلة FinVero بالظبط ، والنسب من صفحة General Assumptions بتاعة Vero.
 * * قبل كده Vero كان بياخد النسب ومبيسجلش احتياطي ولا توزيع.
 */
class ProfitDistributionTest extends TestCase
{
	private function rates(array $override = []): array
	{
		return array_merge([
			'legal_reserve_rate' => 0.05, 'legal_reserve_cap' => 0.5, 'fra_reserve_rate' => 0.0, 'fra_reserve_cap' => 0.0,
			'employee_share' => 0.10, 'first_dividend' => 0.05, 'board_share' => 0.10, 'dividend_payout' => 0.20, 'dividend_in_cash' => true,
		], $override);
	}

	public function test_the_example_of_the_plan(): void
	{
		// ربح 100: احتياطي 5 ، عاملين 10 ، أول توزيع 5 ، الباقي 100 − 5 − 10 − 5 = 80 ، مجلس 8 ، توزيع 16 ؛ الأرباح المرحلة +56
		$d = ProfitDistribution::forYear(100, 0, 1000, 1000, 0, 0, $this->rates());

		$this->assertEqualsWithDelta(5, $d['legal_reserve'], 1e-9);
		$this->assertEqualsWithDelta(10, $d['employee_profit_share'], 1e-9);
		$this->assertEqualsWithDelta(5, $d['shareholders_first_dividend'], 1e-9);
		$this->assertEqualsWithDelta(8, $d['board_profit_share'], 1e-9);
		$this->assertEqualsWithDelta(16, $d['shareholders_dividend_cash'], 1e-9);
		$this->assertSame(0.0, $d['shareholders_dividend_shares']);
		$this->assertEqualsWithDelta(56, 100 - array_sum($d), 1e-9);
	}

	public function test_the_legal_reserve_stops_at_its_cap(): void
	{
		// الحد 50% من رأس مال 1,000 = 500 ، والاحتياطي 498 → ياخد 2 بس من الـ 5
		$this->assertEqualsWithDelta(2, ProfitDistribution::forYear(100, 0, 0, 1000, 498, 0, $this->rates())['legal_reserve'], 1e-9);
		$this->assertSame(0.0, ProfitDistribution::forYear(100, 0, 0, 1000, 500, 0, $this->rates())['legal_reserve']);
		// الحد 0 = من غير حد
		$this->assertEqualsWithDelta(5, ProfitDistribution::forYear(100, 0, 0, 1000, 5000, 0, $this->rates(['legal_reserve_cap' => 0]))['legal_reserve'], 1e-9);
	}

	public function test_a_loss_year_distributes_nothing(): void
	{
		$this->assertSame(0.0, array_sum(ProfitDistribution::forYear(-100, 0, 1000, 1000, 0, 0, $this->rates())));
	}

	public function test_only_the_reserves_while_the_retained_earnings_and_the_profit_are_not_positive(): void
	{
		// أرباح مرحلة −500 وربح السنة 100: الاحتياطي 5 بس
		$d = ProfitDistribution::forYear(100, -500, 1000, 1000, 0, 0, $this->rates());

		$this->assertEqualsWithDelta(5, $d['legal_reserve'], 1e-9);
		$this->assertEqualsWithDelta(5, array_sum($d), 1e-9);
	}

	public function test_the_employees_share_is_at_most_the_years_salaries(): void
	{
		$this->assertEqualsWithDelta(7, ProfitDistribution::forYear(100, 0, 7, 1000, 0, 0, $this->rates())['employee_profit_share'], 1e-9);
	}

	public function test_a_dividend_in_shares_is_not_paid_in_cash(): void
	{
		$d = ProfitDistribution::forYear(100, 0, 1000, 1000, 0, 0, $this->rates(['dividend_in_cash' => false]));

		$this->assertSame(0.0, $d['shareholders_dividend_cash']);
		$this->assertEqualsWithDelta(16, $d['shareholders_dividend_shares'], 1e-9);
	}

	public function test_the_fra_reserve_has_its_own_rate_and_cap_and_is_not_taken_off_the_board_base(): void
	{
		// هيئة 10% بحد 20% من رأس المال (200): 10 ؛ الباقي للمجلس لسه 80 (زي FinVero)
		$d = ProfitDistribution::forYear(100, 0, 1000, 1000, 0, 195, $this->rates(['fra_reserve_rate' => 0.10, 'fra_reserve_cap' => 0.2]));

		$this->assertEqualsWithDelta(5, $d['fra_reserve'], 1e-9);
		$this->assertEqualsWithDelta(8, $d['board_profit_share'], 1e-9);
	}
}
