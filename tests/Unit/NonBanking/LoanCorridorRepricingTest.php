<?php

namespace Tests\Unit\NonBanking;

use App\Helpers\HArr;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use App\ReadyFunctions\CalculateVariableLoanAtEndService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * * قروض الكوريدور (Fixed At End): القسط بيتعاد حسابه بس في الشهور اللي السعر بيتغير فيها بعد شهر الصرف ،
 * * على الرصيد والمدة اللي فاضلين ، وفترة السماح وعداد الـ step بيكمّلوا من مكانهم (نفس قاعدة FinVero ‏LoanEngine).
 *
 * * قبل التعديل (المرحلة 3 ، V1 / V2 / V3 / V4 / V10):
 * * - V1: فلتر بالسنة ورقم الشهر كان بيشيل التغييرات: قرض نوفمبر 2026 ماكانش بيشوف تغيير فبراير 2027 غير في نوفمبر 2027.
 * * - V2: لما القايمة تبدأ من أول الدراسة ، القرض كان بيتسعّر بسعر أول شهر في الدراسة ، وبعدها متأخر بعدد الشهور اللي قبل صرفه.
 * * - V3: القرض الربع سنوي كان بيتأخر 3 شهور مع كل شهر في القايمة.
 * * - V4: الـ RF كان بياخد سعر شهر الدفع ، مش الشهر اللي الفايدة اتحسبت فيه.
 * * - V10: كل شهر في القايمة كان بيعيد القرض بفترة سماح صفر وعداد step من الأول.
 *
 * * الأرقام: الدراسة بتبدأ يناير 2026 (الشهر 0) ، والقرض 1,000,000 ، والهامش 2% ، والفايدة بعدد أيام الشهر ÷ 360.
 */
class LoanCorridorRepricingTest extends TestCase
{
    private const AMOUNT = 1_000_000.0;
    private const MARGIN = 2.0;

    private array $dateIndexWithDate = [];
    private array $dateWithDateIndex = [];

    protected function setUp(): void
    {
        parent::setUp();
        for ($i = 0; $i < 144; $i++) {
            $this->dateIndexWithDate[$i] = date('Y-m-d', mktime(0, 0, 0, 1 + $i, 1, 2026));
        }
        $this->dateWithDateIndex = array_flip($this->dateIndexWithDate);
        // the page binds the study's dates; extendPerMonth (quarterly loans) checks for them
        app()->instance('dateIndexWithDate', $this->dateIndexWithDate);
    }

    /** the corridor of every month for 60 months, the way GeneralAndReserveAssumption::getBaseRatesPerMonths gives it */
    private function corridor(float $start, array $changes = []): array
    {
        $out = [];
        $rate = $start;
        for ($i = 0; $i < 60; $i++) {
            $rate = $changes[$i] ?? $rate;
            $out[$this->dateIndexWithDate[$i]] = $rate;
        }

        return $out;
    }

    private function loan(array $corridor, int $start, int $tenor, string $interval = 'monthly', string $type = 'normal', float $grace = 0, float $stepUp = 0, float $stepDown = 0, string $stepInterval = 'annually'): array
    {
        $service = new CalculateFixedLoanAtEndService();

        return $service->__calculateBasedOnDiffBaseRates($corridor, $type, $this->dateIndexWithDate[$start], self::AMOUNT, self::MARGIN, $tenor, $interval,
            $service->getInstallmentPaymentIntervalValue($interval), $stepUp, $stepInterval, $stepDown, $stepInterval, $grace, $start, $this->dateWithDateIndex, $this->dateIndexWithDate);
    }

    /**
     * the schedule Vero gives when one rate is known for the whole loan (every study with a flat corridor), the way
     * the non-banking module asks for it ($interestByDays); the standalone loan calculator calls it without the flag
     */
    private function oneRateLoan(float $corridor, int $start, int $tenor, string $interval = 'monthly', string $type = 'normal', float $grace = 0, float $stepUp = 0, float $stepDown = 0, string $stepInterval = 'annually', bool $interestByDays = true): array
    {
        $schedule = (new CalculateFixedLoanAtEndService())->__calculate([], -1, $type, $this->dateIndexWithDate[$start], self::AMOUNT, $corridor, self::MARGIN, $tenor, $interval, $stepUp, $stepInterval, $stepDown, $stepInterval, $grace, $start, null, null, 0, $interestByDays)['final_result'];
        unset($schedule['totals']);

        return $schedule;
    }

    private function daysIn(int $monthIndex): int
    {
        return (int) date('t', mktime(0, 0, 0, 1 + $monthIndex, 1, 2026));
    }

    /** the installment that repays $balance over the periods given (accrual months of each period), paid at the end */
    private function annuity(float $balance, float $rate, array $periods): float
    {
        $discount = 1.0;
        $weights = 0.0;
        foreach ($periods as $accrualMonths) {
            $days = array_sum(array_map(fn ($m) => $this->daysIn($m), (array) $accrualMonths));
            $discount *= 1 + $rate * $days / 360;
            $weights += 1 / $discount;
        }

        return $balance / $weights;
    }

    private function assertMoney(float $expected, float $actual, string $message = '', float $delta = 0.01): void
    {
        $this->assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    public function test_the_rates_of_a_loan_start_at_its_own_month_and_keep_only_the_changes(): void
    {
        // corridor 22 % until January 2027, 20 % from February 2027: a loan of November 2026 (month 10)
        $corridor = $this->corridor(22.0, [13 => 20.0]);

        $this->assertSame([10 => 22.0, 13 => 20.0], HArr::rateChangesFromMonth($corridor, 10, $this->dateWithDateIndex));
        // a loan after the change starts at the new rate and has nothing to re-price
        $this->assertSame([24 => 20.0], HArr::rateChangesFromMonth($corridor, 24, $this->dateWithDateIndex));
        // a month with the same rate as the month before is not a change
        $this->assertSame([0 => 18.0], HArr::rateChangesFromMonth($this->corridor(18.0, [6 => 18.0]), 0, $this->dateWithDateIndex));
    }

    public function test_a_november_loan_takes_the_february_cut_from_the_march_installment(): void
    {
        // 24 monthly installments from November 2026 at 22 + 2 = 24 %: 53,027.09.
        // The corridor falls to 20 % in February 2027: the February row is the last at 24 %, its balance is 900,204.05,
        // and the 21 installments left are set again at 22 %: 52,133.12 from March 2027.
        // (The old filter kept 53,027.09 until the November 2027 installment.)
        $schedule = $this->loan($this->corridor(22.0, [13 => 20.0]), 10, 24);

        $this->assertMoney(53027.09, $schedule['schedulePayment'][11]);
        $this->assertMoney(53027.09, $schedule['schedulePayment'][13]);
        $this->assertMoney(900204.05, $schedule['endBalance'][13]);
        $expected = $this->annuity($schedule['endBalance'][13], 0.22, range(13, 33));
        $this->assertMoney(52133.12, $expected);
        foreach (range(14, 33) as $row) {
            $this->assertMoney($expected, $schedule['schedulePayment'][$row], "row {$row}");
        }
        // March's interest is February's days at the new rate
        $this->assertMoney(900204.05 * 0.22 * 28 / 360, $schedule['interestAmount'][14]);
        $this->assertMoney(0.0, $schedule['endBalance'][34]);
    }

    public function test_a_loan_after_the_change_is_priced_with_the_rate_of_its_own_month(): void
    {
        // the list starts in January 2026 at 22 %; the corridor is 18 % from February 2027. A loan of January 2028 is a
        // plain 20 % loan (it used to start at 22 %, the rate of January 2026, and follow the corridor two years late)
        $schedule = $this->loan($this->corridor(22.0, [13 => 18.0]), 24, 24);

        $this->assertEquals($this->oneRateLoan(18.0, 24, 24), $schedule);
    }

    public function test_a_quarterly_loan_takes_a_change_from_the_next_quarter_row(): void
    {
        // 8 quarterly installments from January 2026 at 18 + 2 = 20 %: 155,082.34. The corridor rises to 22 % in August
        // (inside the July–September quarter): that quarter (paid in October) stays at 20 %, the balance after the
        // October row is 670,122.26, and the five quarters left are set again at 24 %: 159,449.94 from January 2027.
        $schedule = $this->loan($this->corridor(18.0, [7 => 22.0]), 0, 24, 'quarterly');
        $quarters = [[0, 1, 2], [3, 4, 5], [6, 7, 8], [9, 10, 11], [12, 13, 14], [15, 16, 17], [18, 19, 20], [21, 22, 23]];

        foreach ([3, 6, 9] as $row) {
            $this->assertMoney(155082.34, $schedule['schedulePayment'][$row], "row {$row}");
        }
        $this->assertMoney(670122.26, $schedule['endBalance'][9]);
        $this->assertMoney(159449.94, $this->annuity(670122.26, 0.24, array_slice($quarters, 3)), 'hand check', 0.02);
        foreach ([12, 15, 18] as $row) {
            $this->assertMoney(159449.94, $schedule['schedulePayment'][$row], "row {$row}", 0.02);
        }
        $this->assertMoney(0.0, $schedule['endBalance'][24]);
    }

    public function test_two_changes_inside_one_quarter_reprice_the_next_quarter_with_the_later_rate(): void
    {
        // February 21 % and March 23 %: both fall in the January–March quarter, paid in April, which stays at 20 %;
        // April–June is the first quarter priced, with March's 23 + 2 = 25 %
        $schedule = $this->loan($this->corridor(18.0, [1 => 21.0, 2 => 23.0]), 0, 24, 'quarterly');
        $quarters = [[3, 4, 5], [6, 7, 8], [9, 10, 11], [12, 13, 14], [15, 16, 17], [18, 19, 20], [21, 22, 23]];

        $this->assertMoney($this->oneRateLoan(18.0, 0, 24, 'quarterly')['schedulePayment'][3], $schedule['schedulePayment'][3]);
        $this->assertMoney($this->annuity($schedule['endBalance'][3], 0.25, $quarters), $schedule['schedulePayment'][6]);
    }

    public function test_a_change_during_the_grace_period_keeps_the_grace_and_starts_the_installments_on_time(): void
    {
        // six months of grace with the interest paid (grace without capitalization) from January 2026 at 20 %; the
        // corridor rises to 22 % in March. The grace rows keep paying interest only — February's row 17,222.22
        // (January's days at 20 %), March's 15,555.56 (February at 20 %), April's 20,666.67 (March at 24 %) — and the
        // 18 installments start in August, as agreed, at 66,900.89. (The grace used to end at the first re-pricing.)
        $schedule = $this->loan($this->corridor(18.0, [2 => 22.0]), 0, 24, 'monthly', 'grace_period_without_capitalization', 6);

        $this->assertMoney(17222.22, $schedule['schedulePayment'][1]);
        $this->assertMoney(15555.56, $schedule['schedulePayment'][2]);
        $this->assertMoney(20666.67, $schedule['schedulePayment'][3]);
        foreach (range(1, 6) as $row) {
            $this->assertMoney($schedule['interestAmount'][$row], $schedule['schedulePayment'][$row], "grace row {$row}");
            $this->assertMoney(self::AMOUNT, $schedule['endBalance'][$row], "grace row {$row}");
        }
        $this->assertMoney(66900.89, $schedule['schedulePayment'][7]);
        $this->assertMoney(0.0, $schedule['endBalance'][24]);
    }

    public function test_a_change_during_a_capitalised_grace_accrues_at_the_new_rate_from_the_change(): void
    {
        // three months of grace with the interest added to the balance; the corridor rises in February. February's
        // row (January's days) is at 20 %, March and April (February and March days) at 24 %, and the installments
        // from May are set on the real balance (they used to wait until May for the new rate)
        $schedule = $this->loan($this->corridor(18.0, [1 => 22.0]), 0, 12, 'monthly', 'grace_period_with_capitalization', 3);

        $this->assertMoney(self::AMOUNT * 0.20 * 31 / 360, $schedule['interestAmount'][1]);
        $this->assertMoney($schedule['endBalance'][1] * 0.24 * 28 / 360, $schedule['interestAmount'][2]);
        $this->assertMoney(0.0, $schedule['schedulePayment'][3]);
        $this->assertMoney($this->annuity($schedule['endBalance'][3], 0.24, range(3, 11)), $schedule['schedulePayment'][4]);
        $this->assertMoney(0.0, $schedule['endBalance'][12]);
    }

    public function test_a_step_up_loan_keeps_its_steps_after_a_change(): void
    {
        // 10 % more every 12 installments; the corridor changes in July 2026 (after 6 installments). The installments of
        // the second year stay 10 % above those of the first year after the change, and the step stays in February
        $schedule = $this->loan($this->corridor(18.0, [6 => 22.0]), 0, 36, 'monthly', 'step-up', 0, 10);

        $this->assertMoney($schedule['schedulePayment'][7], $schedule['schedulePayment'][12], 'same step until the 12th installment');
        $this->assertEqualsWithDelta(1.10, $schedule['schedulePayment'][13] / $schedule['schedulePayment'][12], 0.000001);
        $this->assertEqualsWithDelta(1.10, $schedule['schedulePayment'][25] / $schedule['schedulePayment'][24], 0.000001);
        $this->assertMoney(0.0, $schedule['endBalance'][36]);
    }

    public static function loanTypes(): array
    {
        return [
            'normal monthly' => ['normal', 'monthly', 0, 0, 0, 'annually'],
            'normal quarterly' => ['normal', 'quarterly', 0, 0, 0, 'annually'],
            'normal semi-annual' => ['normal', 'semi annually', 0, 0, 0, 'annually'],
            'step-up' => ['step-up', 'monthly', 0, 10, 0, 'annually'],
            'step-down every quarter' => ['step-down', 'monthly', 0, 0, -5, 'quarterly'],
            'step-up, quarterly installments' => ['step-up', 'quarterly', 0, 8, 0, 'annually'],
            'grace with capitalization' => ['grace_period_with_capitalization', 'monthly', 3, 0, 0, 'annually'],
            'grace without capitalization, quarterly' => ['grace_period_without_capitalization', 'quarterly', 6, 0, 0, 'annually'],
            'grace + step-up with capitalization' => ['grace_step-up_with_capitalization', 'monthly', 3, 10, 0, 'annually'],
            'grace + step-down without capitalization' => ['grace_step-down_without_capitalization', 'monthly', 6, 0, -7, 'semi annually'],
        ];
    }

    #[DataProvider('loanTypes')]
    public function test_a_flat_corridor_month_by_month_gives_the_one_rate_schedule(string $type, string $interval, float $grace, float $stepUp, float $stepDown, string $stepInterval): void
    {
        // when the corridor changes somewhere in the study every loan gets the monthly list; a loan that sees no change
        // must be the same loan as with one rate (the grace and step loans used to restart at every month of the list)
        $schedule = $this->loan($this->corridor(20.0), 1, 36, $interval, $type, $grace, $stepUp, $stepDown, $stepInterval);

        $this->assertEquals($this->oneRateLoan(20.0, 1, 36, $interval, $type, $grace, $stepUp, $stepDown, $stepInterval), $schedule);
    }

    public function test_a_quarter_interest_is_recognised_by_the_days_of_its_months(): void
    {
        // a quarterly loan from January 2026 at 20 %: the first quarter (January 31 + February 28 + March 31 = 90 days)
        // earns 1,000,000 × 20 % × 90 / 360 = 50,000.00, recognised February 17,222.22 (31 days), March 15,555.56
        // (28 days) and April, the payment month, 17,222.22 (31 days). (It used to be 16,666.67 every month.)
        $schedule = $this->loan($this->corridor(18.0), 0, 24, 'quarterly');

        $this->assertMoney(50000.0, $schedule['schedulePayment'][3] - $schedule['principleAmount'][3]);
        $this->assertMoney(17222.22, $schedule['interestAmount'][1]);
        $this->assertMoney(15555.56, $schedule['interestAmount'][2]);
        $this->assertMoney(17222.22, $schedule['interestAmount'][3]);
        $this->assertMoney(array_sum(array_map(fn ($row) => $schedule['schedulePayment'][$row] - $schedule['principleAmount'][$row], [3, 6, 9, 12, 15, 18, 21, 24])),
            array_sum($schedule['interestAmount']), 'the months add up to the interest paid');
    }

    public function test_the_loan_calculator_and_a_fixed_day_count_keep_equal_parts_of_the_quarter(): void
    {
        // the standalone loan calculator (and the property-management module) do not ask for the split by days: the
        // quarter's 50,000.00 stays 16,666.67 a month there. A loan priced on a fixed day count splits equally too (the
        // fixed count is per row: 30 days for the quarter, 16,666.67, so 5,555.56 a month)
        $calculator = $this->oneRateLoan(18.0, 0, 24, 'quarterly', 'normal', 0, 0, 0, 'annually', false);
        $fixedDays = (new CalculateFixedLoanAtEndService())->__calculate([], -1, 'normal', $this->dateIndexWithDate[0], self::AMOUNT, 18.0, self::MARGIN, 24, 'quarterly', 0, 'annually', 0, 'annually', 0, 0, 30, null, 0, true)['final_result'];
        $fixedDaysQuarter = $fixedDays['schedulePayment'][3] - $fixedDays['principleAmount'][3];

        $this->assertMoney(1000000 * 0.20 * 30 / 360, $fixedDaysQuarter);
        foreach ([1, 2, 3] as $month) {
            $this->assertMoney(50000.0 / 3, $calculator['interestAmount'][$month], "calculator, month {$month}");
            $this->assertMoney($fixedDaysQuarter / 3, $fixedDays['interestAmount'][$month], "fixed day count, month {$month}");
        }
    }

    public function test_a_grace_that_is_not_a_whole_number_of_quarters_keeps_the_steps_on_their_installments(): void
    {
        // quarterly installments, 4 months of grace (the first installment in July, month 6), 10 % more every 4
        // installments; the corridor changes in December (month 11) and the loan is set again from the October row
        // with 3 installments paid: the 5th installment (January 2027, month 18) is the first 10 % higher, and the 9th
        // (month 30) the next (with the first installment taken as month 7 the steps moved one installment late)
        $schedule = $this->loan($this->corridor(18.0, [11 => 22.0]), 0, 36, 'quarterly', 'grace_step-up_without_capitalization', 4, 10);

        // April's row is the grace: the quarter's interest only; July's is the first installment
        $this->assertMoney($schedule['interestAmount'][1] + $schedule['interestAmount'][2] + $schedule['interestAmount'][3], $schedule['schedulePayment'][3], 'April: interest only');
        $this->assertMoney(self::AMOUNT, $schedule['endBalance'][3]);
        $this->assertLessThan(self::AMOUNT, $schedule['endBalance'][6]);
        $this->assertEqualsWithDelta(1.10, $schedule['schedulePayment'][18] / $schedule['schedulePayment'][15], 0.000001);
        $this->assertEqualsWithDelta(1.00, $schedule['schedulePayment'][27] / $schedule['schedulePayment'][18], 0.000001);
        $this->assertEqualsWithDelta(1.10, $schedule['schedulePayment'][30] / $schedule['schedulePayment'][27], 0.000001);
        $this->assertMoney(0.0, $schedule['endBalance'][36]);
    }

    public function test_a_loan_without_a_tenor_has_no_schedule(): void
    {
        $this->assertSame([], $this->loan($this->corridor(18.0, [6 => 22.0]), 0, 0));
    }

    public function test_quarterly_reverse_factoring_interest_keeps_the_rate_of_its_quarter(): void
    {
        // quarterly interest from January 2026; the corridor falls from 22 % to 20 % in February (inside the quarter):
        // January–March stays at 24 % — March's row is February's 28 days on 1,000,000 = 18,666.67 — and April–June
        // is the first quarter at 22 %: May's row is April's 30 days on 750,000 = 13,750.00
        $daily = [];
        foreach ($this->dateIndexWithDate as $index => $date) {
            $daily[$date] = (($index >= 1 ? 20.0 : 22.0) + self::MARGIN) / 100 / 360;
        }
        $schedule = (new CalculateVariableLoanAtEndService())->__calculate([], -1, 'normal', $this->dateIndexWithDate[0], self::AMOUNT, $daily, self::MARGIN, 12, 'quarterly', 'quarterly', 0, 'monthly', 0, 'monthly', 0, 0)['final_result'];

        $this->assertMoney(18666.67, $schedule['interestAmount'][2]);
        $this->assertMoney(1000000 * 0.24 * 31 / 360, $schedule['interestAmount'][3]);
        $this->assertMoney(13750.00, $schedule['interestAmount'][4]);
        $this->assertMoney(750000 * 0.22 * 31 / 360, $schedule['interestAmount'][5], 'June: May at 22 %');
    }

    public function test_reverse_factoring_interest_takes_the_rate_of_the_month_it_accrues_in(): void
    {
        // 12 monthly principal payments from November 2026; the corridor falls from 22 % to 20 % in February 2027.
        // February's row is January's interest: 833,333.33 × 24 % × 31 / 360 = 17,222.22 (it used to take February's
        // 22 %: 15,787.04); March's row is February's interest at 22 %: 750,000 × 22 % × 28 / 360 = 12,833.33
        $daily = [];
        foreach ($this->dateIndexWithDate as $index => $date) {
            $daily[$date] = (($index >= 13 ? 20.0 : 22.0) + self::MARGIN) / 100 / 360;
        }
        $schedule = (new CalculateVariableLoanAtEndService())->__calculate([], -1, 'normal', $this->dateIndexWithDate[10], self::AMOUNT, $daily, self::MARGIN, 12, 'monthly', 'monthly', 0, 'monthly', 0, 'monthly', 0, 10)['final_result'];

        $this->assertMoney(833333.33, $schedule['beginning'][13]);
        $this->assertMoney(17222.22, $schedule['interestAmount'][13]);
        $this->assertMoney(12833.33, $schedule['interestAmount'][14]);
    }
}
