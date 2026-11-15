<?php

namespace Tests\Unit\NonBanking;

use App\ReadyFunctions\TaxLossCarryForward;
use PHPUnit\Framework\TestCase;

/**
 * * الضريبة سنة بسنة ، وخسارة السنة بتترحّل 5 سنين (المرحلة 5 ، قانون 91/2005 مادة 29 ، نفس FinVero).
 * * قبل كده سنة الخسارة ضريبتها صفر ، والخسارة مكانتش بتتخصم من أي سنة بعدها.
 */
class TaxLossCarryForwardTest extends TestCase
{
	public function test_a_loss_is_deducted_from_the_next_years_profit(): void
	{
		// 2026 خسرت 1,200,000 ، 2027 كسبت 3,600,000 → 2027 بتدفع على 2,400,000 (ضريبة 540,000 بـ 22.5%)
		$this->assertSame([2026 => 0.0, 2027 => 2400000.0], TaxLossCarryForward::taxableProfits([2026 => -1200000, 2027 => 3600000]));
	}

	public function test_a_loss_bigger_than_the_profit_is_used_over_several_years(): void
	{
		$this->assertSame(
			[2026 => 0.0, 2027 => 0.0, 2028 => 0.0, 2029 => 200.0],
			TaxLossCarryForward::taxableProfits([2026 => -500, 2027 => 200, 2028 => 200, 2029 => 300])
		);
	}

	public function test_the_oldest_loss_is_used_first_and_a_loss_lapses_after_five_years(): void
	{
		// 2028 بتستخدم 100 بتاعة 2026 الأول وبعدين 20 من 2027 ؛ الـ 30 الباقيين من 2027 لسه ينفعوا في 2032 (سنتها الخامسة)
		$taxable = TaxLossCarryForward::taxableProfits([2026 => -100, 2027 => -50, 2028 => 120, 2029 => 0, 2030 => 0, 2031 => 0, 2032 => 1000]);

		$this->assertSame(0.0, $taxable[2028]);
		$this->assertSame(970.0, $taxable[2032]);
		$this->assertSame(900.0, TaxLossCarryForward::taxableProfits([2026 => -100, 2031 => 1000])[2031]);
		$this->assertSame(1000.0, TaxLossCarryForward::taxableProfits([2026 => -100, 2032 => 1000])[2032]);
	}

	public function test_a_loss_after_a_profit_is_not_carried_back(): void
	{
		$this->assertSame([2026 => 3600000.0, 2027 => 0.0], TaxLossCarryForward::taxableProfits([2026 => 3600000, 2027 => -1200000]));
	}
}
