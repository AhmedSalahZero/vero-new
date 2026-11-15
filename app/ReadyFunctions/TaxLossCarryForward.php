<?php

namespace App\ReadyFunctions;

/**
 * * الضريبة سنة بسنة ، وخسارة السنة بتترحّل 5 سنين (قانون 91/2005 مادة 29) — المرحلة 5 ، نفس القاعدة في FinVero
 * * (App\Support\TaxLossCarryForward).
 * * خسارة السنة بتتخصم من أرباح الخمس سنين اللي بعدها ، الأقدم الأول ، واللي يفضل منها بعد السنة الخامسة بيسقط.
 * * مثال بـ 22.5%: 2026 خسرت 100 و 2027 كسبت 300 → ضريبة 2027 على 200 = 45 (مش 67.5).
 * * قبل كده سنة الخسارة كانت ضريبتها صفر ، والخسارة مكانتش بتترحّل.
 * * سنة الدراسة سنة ميلادية (الدراسة اللي بتبدأ يوليو أول سنة ضريبية فيها 6 شهور).
 */
final class TaxLossCarryForward
{
	public const YEARS = 5;

	/**
	 * @param  array<int, float>  $profitBeforeTaxByYear  السنة => ربح السنة قبل الضريبة
	 * @return array<int, float>  السنة => الربح الخاضع للضريبة بعد خسائر الخمس سنين اللي قبلها
	 */
	public static function taxableProfits(array $profitBeforeTaxByYear): array
	{
		ksort($profitBeforeTaxByYear);
		$losses = [];
		$taxable = [];
		foreach ($profitBeforeTaxByYear as $year => $profit) {
			$losses = array_filter($losses, fn (float $loss, int $lossYear) => $loss > 0 && $year - $lossYear <= self::YEARS, ARRAY_FILTER_USE_BOTH);
			$profit = (float) $profit;
			if ($profit <= 0) {
				$taxable[$year] = 0.0;
				if ($profit < 0) {
					$losses[$year] = -$profit;
				}

				continue;
			}
			foreach ($losses as $lossYear => $loss) {
				$used = min($loss, $profit);
				$losses[$lossYear] -= $used;
				$profit -= $used;
			}
			$taxable[$year] = $profit;
		}

		return $taxable;
	}
}
