<?php

namespace Tests\Unit\NonBanking;

use App\ReadyFunctions\CalculateFixedLoanAtBeginningService;
use Tests\TestCase;

/**
 * * قروض «Fixed At Beginning» (القسط أول الشهر) لما الكوريدور يتغير (المرحلة 4، بند 2): نفس قاعدة «Fixed At End».
 * * - السعر الجديد بيبدأ من قسط شهر التغيير نفسه (القسط أول الشهر بيشيل فايدة الشهر ده). كان بيبدأ من القسط اللي بعده.
 * * - ميعاد أول قسط مايتغيرش: باقي السماح بيفضل ، وفايدته بالسعر الجديد. كان السماح بيضيع والعميل يدفع من الشهر اللي بعد التغيير.
 * * - عداد الـ step بيكمّل من الأقساط اللي اتدفعت. كان بيبدأ من الأول.
 *
 * * الأرقام: الدراسة بتبدأ يناير 2026 (الشهر 0) ، القرض 1,000,000 ، الهامش 2% ، الكوريدور 18% ويبقى 22% من مارس (الشهر 2)،
 * * والفايدة على الرصيد بعد قسط أول الشهر × عدد أيام الشهر ÷ 360.
 */
class LoanAtBeginningRepricingTest extends TestCase
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
        app()->instance('dateIndexWithDate', $this->dateIndexWithDate);
    }

    /** the corridor of every month for 60 months: 18 %, then 22 % from March 2026 unless told otherwise */
    private function corridor(float $start = 18.0, array $changes = [2 => 22.0]): array
    {
        $out = [];
        $rate = $start;
        for ($i = 0; $i < 60; $i++) {
            $rate = $changes[$i] ?? $rate;
            $out[$this->dateIndexWithDate[$i]] = $rate;
        }

        return $out;
    }

    private function loan(array $corridor, int $tenor, string $type = 'normal', float $grace = 0, float $stepUp = 0): array
    {
        $service = new CalculateFixedLoanAtBeginningService();

        return $service->__calculateBasedOnDiffBaseRates($corridor, $type, $this->dateIndexWithDate[0], self::AMOUNT, self::MARGIN, $tenor, 'monthly',
            1, $stepUp, 'annually', 0, 'annually', $grace, 0, $this->dateWithDateIndex, $this->dateIndexWithDate);
    }

    private function daysIn(int $monthIndex): int
    {
        return (int) date('t', mktime(0, 0, 0, 1 + $monthIndex, 1, 2026));
    }

    /**
     * the equal installment, paid at the start of each month from $from to $to, that repays $balance owed at the start
     * of $from: after each payment the rest earns $rate for the days of that month
     */
    private function annuityDue(float $balance, float $rate, int $from, int $to): float
    {
        $growth = 1.0;
        $weights = 0.0;
        for ($m = $from; $m <= $to; $m++) {
            $weights += 1 / $growth;
            $growth *= 1 + $rate * $this->daysIn($m) / 360;
        }

        return $balance / $weights;
    }

    private function assertMoney(float $expected, float $actual, string $message = '', float $delta = 0.01): void
    {
        $this->assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    public function test_a_normal_loan_takes_the_new_rate_from_the_installment_of_the_change_month(): void
    {
        // 24 installments from January 2026 at 18 + 2 = 20 %: the January and February installments are the one-rate ones;
        // from March (the change) the 22 installments left are set again at 22 + 2 = 24 % on the balance owed on 1 March.
        $schedule = $this->loan($this->corridor(), 24);
        $oneRate = $this->annuityDue(self::AMOUNT, 0.20, 0, 23);

        $this->assertMoney($oneRate, $schedule['schedulePayment'][0]);
        $this->assertMoney($oneRate, $schedule['schedulePayment'][1]);
        $newInstallment = $this->annuityDue($schedule['beginning'][2], 0.24, 2, 23);
        $this->assertGreaterThan($oneRate + 100, $newInstallment);
        foreach (range(2, 23) as $row) {
            $this->assertMoney($newInstallment, $schedule['schedulePayment'][$row], "row {$row}");
        }
        // the March interest is on the balance after the March installment, at the new rate
        $this->assertMoney(($schedule['beginning'][2] - $newInstallment) * 0.24 * 31 / 360, $schedule['interestAmount'][2]);
        $this->assertMoney(0.0, $schedule['endBalance'][23]);
    }

    public function test_a_grace_without_capitalisation_keeps_its_first_installment_date_after_a_change(): void
    {
        // 6 months of grace paying the interest only: the first installment stays in July 2026 (month 6).
        // March–June pay the interest at the new 24 % (it used to lose the grace and charge installments from April).
        $schedule = $this->loan($this->corridor(), 24, 'grace_period_without_capitalization', 6);

        foreach (range(0, 5) as $row) {
            $this->assertMoney($schedule['interestAmount'][$row], $schedule['schedulePayment'][$row], "month {$row} pays the interest only");
            $this->assertMoney(self::AMOUNT, $schedule['endBalance'][$row], "month {$row}");
        }
        $this->assertMoney(self::AMOUNT * 0.20 * 31 / 360, $schedule['interestAmount'][0]);    // January at 20 %
        $this->assertMoney(self::AMOUNT * 0.24 * 30 / 360, $schedule['interestAmount'][3]);    // April at 24 %
        $installment = $this->annuityDue(self::AMOUNT, 0.24, 6, 23);
        foreach (range(6, 23) as $row) {
            $this->assertMoney($installment, $schedule['schedulePayment'][$row], "row {$row}");
        }
        $this->assertMoney(0.0, $schedule['endBalance'][23]);
    }

    public function test_a_capitalised_grace_accrues_at_the_new_rate_from_the_change_month(): void
    {
        // nothing is paid for 6 months and the interest joins the balance: January and February at 20 %, March to June at
        // 24 % (they used to stay at 20 % until the first installment in July)
        $schedule = $this->loan($this->corridor(), 24, 'grace_period_with_capitalization', 6);

        $balance = self::AMOUNT;
        foreach (range(0, 5) as $row) {
            $this->assertMoney(0.0, $schedule['schedulePayment'][$row], "month {$row}");
            $balance *= 1 + ($row < 2 ? 0.20 : 0.24) * $this->daysIn($row) / 360;
            $this->assertMoney($balance, $schedule['endBalance'][$row], "month {$row}");
        }
        $installment = $this->annuityDue($balance, 0.24, 6, 23);
        foreach (range(6, 23) as $row) {
            $this->assertMoney($installment, $schedule['schedulePayment'][$row], "row {$row}");
        }
        $this->assertMoney(0.0, $schedule['endBalance'][23]);
    }

    public function test_a_step_up_loan_keeps_its_steps_on_their_installments_after_a_change(): void
    {
        // 24 installments, +10 % every 12: the step stays on installment 13 (January 2027, month 12) after the
        // March re-pricing (the counter used to start again from March, so the step came in March 2027)
        $schedule = $this->loan($this->corridor(), 24, 'step-up', 0, 10);

        foreach (range(3, 11) as $row) {
            $this->assertMoney($schedule['schedulePayment'][2], $schedule['schedulePayment'][$row], "row {$row} is on the March level");
        }
        $this->assertMoney($schedule['schedulePayment'][11] * 1.10, $schedule['schedulePayment'][12], 'the step on installment 13');
        foreach (range(13, 23) as $row) {
            $this->assertMoney($schedule['schedulePayment'][12], $schedule['schedulePayment'][$row], "row {$row}");
        }
        $this->assertMoney(0.0, $schedule['endBalance'][23]);
    }

    public function test_a_flat_corridor_gives_the_one_rate_schedule(): void
    {
        $schedule = $this->loan($this->corridor(18.0, []), 24);

        $installment = $this->annuityDue(self::AMOUNT, 0.20, 0, 23);
        foreach (range(0, 23) as $row) {
            $this->assertMoney($installment, $schedule['schedulePayment'][$row], "row {$row}");
        }
        $this->assertMoney(0.0, $schedule['endBalance'][23]);
    }
}
