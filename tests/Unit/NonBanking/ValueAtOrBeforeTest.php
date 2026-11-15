<?php

namespace Tests\Unit\NonBanking;

use App\Helpers\HArr;
use PHPUnit\Framework\TestCase;

/**
 * * HArr::valueAtOrBefore: القيمة المحفوظة عند الشهر ، ولو الشهر مش محفوظ (الدراسة اتطوّلت) قيمة آخر شهر محفوظ قبله.
 */
class ValueAtOrBeforeTest extends TestCase
{
	/** a one-year study's operation months (September–December 2026) */
	private const SAVED = [8 => '10.0', 9 => '20.0', 10 => '30.0', 11 => '40.0'];

	public function test_a_saved_month_keeps_its_own_value(): void
	{
		$this->assertSame(10.0, HArr::valueAtOrBefore(self::SAVED, 8, 0));
		$this->assertSame(30.0, HArr::valueAtOrBefore(self::SAVED, 10, 0));
	}

	public function test_a_month_after_the_last_saved_month_takes_the_last_saved_value(): void
	{
		$this->assertSame(40.0, HArr::valueAtOrBefore(self::SAVED, 12, 0));
		$this->assertSame(40.0, HArr::valueAtOrBefore(self::SAVED, 35, 0));
	}

	public function test_a_gap_takes_the_closest_saved_month_before_it(): void
	{
		$this->assertSame(5.0, HArr::valueAtOrBefore([0 => 1, 3 => 5, 9 => 7], 6, 0));
	}

	public function test_a_month_before_the_first_saved_month_takes_the_default(): void
	{
		$this->assertSame(0.0, HArr::valueAtOrBefore(self::SAVED, 7, 0));
		$this->assertSame(12.0, HArr::valueAtOrBefore(self::SAVED, 0, 12));
	}

	public function test_nothing_saved_takes_the_default(): void
	{
		$this->assertSame(1.0, HArr::valueAtOrBefore(null, 5, 1));
		$this->assertSame(12.0, HArr::valueAtOrBefore([], 5, 12));
	}

	public function test_a_saved_zero_stays_zero_and_is_repeated_as_zero(): void
	{
		$this->assertSame(0.0, HArr::valueAtOrBefore([0 => '5', 1 => '0'], 1, 12));
		$this->assertSame(0.0, HArr::valueAtOrBefore([0 => '5', 1 => '0'], 4, 12));
	}

	public function test_a_saved_null_takes_the_default_like_before(): void
	{
		// the getters used `?? $default`: a month saved empty still shows the default, it does not borrow the month before
		$this->assertSame(1.0, HArr::valueAtOrBefore([0 => '3', 1 => null], 1, 1));
		$this->assertSame(1.0, HArr::valueAtOrBefore([0 => '3', 1 => null], 2, 1));
	}

	public function test_a_json_list_counts_from_month_zero(): void
	{
		// loan officers' cases are saved as a JSON list: ["5","5",...] for months 0–11
		$this->assertSame(7.0, HArr::valueAtOrBefore(array_merge(array_fill(0, 11, '5'), ['7']), 23, 0));
	}
}
