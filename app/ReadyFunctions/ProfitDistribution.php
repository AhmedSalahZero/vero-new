<?php

namespace App\ReadyFunctions;

/**
 * * توزيع ربح السنة — المرحلة 5 ، معادلة FinVero الحالية بالظبط (قرار اليوزر ؛ FinancialResultsAggregator هناك).
 * * - الاحتياطي القانوني واحتياطي الهيئة: النسبة × صافي ربح السنة ، لحد ما الاحتياطي يوصل للحد (نسبة من رأس المال المدفوع
 * *   آخر السنة ؛ الحد 0 = من غير حد). بيتسجلوا في ديسمبر.
 * * - لو الأرباح المرحلة + ربح السنة مش موجبين: الاحتياطيات بس.
 * * - حصة العاملين وأول توزيع للمساهمين: من صافي الربح (حصة العاملين مش أكتر من مرتبات السنة).
 * * - حصة المجلس والتوزيع: من الباقي = الربح − الاحتياطي القانوني − حصة العاملين − أول توزيع.
 * * - البنود دي بتتسجل التزام في يناير اللي بعدها وبتتدفع في أبريل ، والتوزيع بالأسهم بيتضاف لرأس المال في يناير.
 * * مثال: ربح 100 ، احتياطي 5% ، عاملين 10% ، أول توزيع 5% ، مجلس 10% ، توزيع 20%:
 * *   احتياطي 5 ، عاملين 10 ، أول توزيع 5 ، الباقي 80 ، مجلس 8 ، توزيع 16 ، والأرباح المرحلة تزيد 56.
 */
final class ProfitDistribution
{
	/** * البنود اللي بتتسجل في يناير اللي بعد السنة (كلها من الأرباح المرحلة) */
	public const ITEMS = ['employee_profit_share', 'shareholders_first_dividend', 'board_profit_share', 'shareholders_dividend_cash', 'shareholders_dividend_shares'];

	/**
	 * @param  array{legal_reserve_rate: float, legal_reserve_cap: float, fra_reserve_rate: float, fra_reserve_cap: float,
	 *               employee_share: float, first_dividend: float, board_share: float, dividend_payout: float, dividend_in_cash: bool}  $rates
	 *         كل النسب كسور (5% = 0.05) ، والحدود نسبة من رأس المال المدفوع
	 * @return array{legal_reserve: float, fra_reserve: float, employee_profit_share: float, shareholders_first_dividend: float,
	 *               board_profit_share: float, shareholders_dividend_cash: float, shareholders_dividend_shares: float}
	 */
	public static function forYear(float $netProfit, float $retainedEarningsBefore, float $grossSalaries, float $paidUpCapital,
		float $legalReserveSoFar, float $fraReserveSoFar, array $rates): array
	{
		$result = array_fill_keys(['legal_reserve', 'fra_reserve', 'employee_profit_share', 'shareholders_first_dividend',
			'board_profit_share', 'shareholders_dividend_cash', 'shareholders_dividend_shares'], 0.0);
		if ($netProfit <= 0) {
			return $result;
		}
		$result['legal_reserve'] = self::reserveTransfer($netProfit, $rates['legal_reserve_rate'], $rates['legal_reserve_cap'] * $paidUpCapital, $legalReserveSoFar);
		$result['fra_reserve'] = self::reserveTransfer($netProfit, $rates['fra_reserve_rate'], $rates['fra_reserve_cap'] * $paidUpCapital, $fraReserveSoFar);
		if ($retainedEarningsBefore + $netProfit <= 0) {
			return $result;
		}
		$employeeShare = $netProfit * $rates['employee_share'];
		$result['employee_profit_share'] = $grossSalaries > 0 ? min($employeeShare, $grossSalaries) : $employeeShare;
		$result['shareholders_first_dividend'] = $netProfit * $rates['first_dividend'];
		$base = $netProfit - $result['legal_reserve'] - $result['employee_profit_share'] - $result['shareholders_first_dividend'];
		if ($base > 0) {
			$result['board_profit_share'] = $base * $rates['board_share'];
			$dividend = $base * $rates['dividend_payout'];
			$result[$rates['dividend_in_cash'] ? 'shareholders_dividend_cash' : 'shareholders_dividend_shares'] = $dividend;
		}

		return $result;
	}

	/** * نصيب الاحتياطي من ربح السنة: النسبة × الربح ، في حدود اللي فاضل للحد (الحد 0 = من غير حد) */
	private static function reserveTransfer(float $profit, float $rate, float $cap, float $reserveSoFar): float
	{
		$transfer = $profit * $rate;

		return $cap > 0 ? max(0.0, min($transfer, $cap - $reserveSoFar)) : $transfer;
	}
}
