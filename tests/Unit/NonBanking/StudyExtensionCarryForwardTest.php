<?php

namespace Tests\Unit\NonBanking;

use App\Services\NonBanking\StudyExtension;
use PHPUnit\Framework\TestCase;

/**
 * * StudyExtension::carryForward: لما الدراسة تتطوّل ، الشهور (أو السنين) اللي بعد آخر مفتاح محفوظ بتاخد آخر قيمة محفوظة
 * * مش فاضية. القيم المحفوظة مابتتغيرش.
 */
class StudyExtensionCarryForwardTest extends TestCase
{
	public function test_the_months_after_the_last_saved_month_take_its_value(): void
	{
		$this->assertSame(
			[8 => '10.0', 9 => '20.0', 10 => '30.0', 11 => '40.0', 12 => '40.0', 13 => '40.0'],
			StudyExtension::carryForward([8 => '10.0', 9 => '20.0', 10 => '30.0', 11 => '40.0'], 13)
		);
	}

	public function test_saved_values_and_gaps_before_the_last_saved_month_are_not_touched(): void
	{
		$this->assertSame([8 => 1, 10 => 3, 11 => 3], StudyExtension::carryForward([8 => 1, 10 => 3], 11));
	}

	public function test_a_trailing_empty_value_is_skipped_for_the_last_value_that_was_entered(): void
	{
		$this->assertSame([0 => 5, 1 => null, 2 => 5, 3 => 5], StudyExtension::carryForward([0 => 5, 1 => null], 3));
	}

	public function test_a_list_stays_a_list_and_strings_are_carried(): void
	{
		$this->assertSame(['in_shares', 'in_shares', 'in_shares'], StudyExtension::carryForward(['in_shares'], 2));
		$this->assertSame([10, 10, 10], StudyExtension::carryForward([10], 2));
	}

	public function test_each_series_of_a_nested_value_is_carried_on_its_own(): void
	{
		$this->assertSame(
			['leasing' => [8 => 3, 9 => 3], 'microfinance' => [8 => 4, 9 => 4]],
			StudyExtension::carryForward(['leasing' => [8 => 3], 'microfinance' => [8 => 4]], 9)
		);
	}

	public function test_nothing_saved_or_already_long_enough_is_left_as_it_is(): void
	{
		$this->assertNull(StudyExtension::carryForward(null, 35));
		$this->assertSame([], StudyExtension::carryForward([], 35));
		$this->assertSame([0 => 1, 1 => 2], StudyExtension::carryForward([0 => 1, 1 => 2], 1));
		$this->assertSame([0 => null], StudyExtension::carryForward([0 => null], 3), 'no value was ever entered');
	}
}
