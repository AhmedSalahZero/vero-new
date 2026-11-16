<?php

namespace Tests\Unit\NonBanking;

use App\ReadyFunctions\CalculateFixedLoanAtBeginningService;
use App\ReadyFunctions\CalculateFixedLoanAtEndService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * * الجولة 3 (مقارنة كل القروض مع FinVero ‏LoanEngine ، tools/loan_parity): قرض الـ step اللي فترة الـ step بتاعته = فترة
 * * القسط (step كل 3 شهور لقرض ربع سنوي ، أو كل 6 شهور لقرض نص سنوي). calculateInstallmentAmount كان بيشرط
 * * (رقم القسط % عدد الأقساط في الـ step) == 1 ، والعدد هنا 1 ، وأي رقم % 1 = 0: القسط عمره ما زاد ، وأول قسط محسوب على
 * * إنه هيزيد ، فالقرض بيخلص وعليه رصيد. مثال: مليون بـ 23% (كوريدور 20% + هامش 3%) نص سنوي على 12 شهر من مارس 2026 ،
 * * step-up ‏10% كل 6 شهور: القسط 562,721.86 ثم 618,994.05 (كان 562,721.86 مرتين وفضل 56,272.19). نفس أرقام FinVero.
 */
class LoanStepEveryPeriodTest extends TestCase
{
    private array $dateIndexWithDate = [];
    private array $dateWithDateIndex = [];

    protected function setUp(): void
    {
        parent::setUp();
        for ($i = 0; $i < 120; $i++) {
            $this->dateIndexWithDate[$i] = date('Y-m-d', mktime(0, 0, 0, 1 + $i, 1, 2026));
        }
        $this->dateWithDateIndex = array_flip($this->dateIndexWithDate);
        app()->instance('dateIndexWithDate', $this->dateIndexWithDate);
    }

    /** the corridor of every month (20 %), as GeneralAndReserveAssumption::getBaseRatesPerMonths gives it */
    private function corridor(): array
    {
        $out = [];
        foreach ($this->dateIndexWithDate as $date) {
            $out[$date] = 20.0;
        }
        $out[$this->dateIndexWithDate[3]] = 22.0;   // one change, so the per-change path is used (Study::storeFixedLoans)

        return $out;
    }

    public static function services(): array
    {
        return ['paid at the end' => [CalculateFixedLoanAtEndService::class], 'paid at the beginning' => [CalculateFixedLoanAtBeginningService::class]];
    }

    private function loan(string $service, string $type, string $interval, int $intervalMonths, int $tenor, float $stepUp, float $stepDown, string $stepInterval): array
    {
        return (new $service())->__calculateBasedOnDiffBaseRates($this->corridor(), $type, $this->dateIndexWithDate[2], 1_000_000.0, 3.0, $tenor, $interval, $intervalMonths,
            $stepUp, $stepInterval, $stepDown, $stepInterval, 0, 2, $this->dateWithDateIndex, $this->dateIndexWithDate);
    }

    private function payments(array $schedule): array
    {
        return array_values(array_filter($schedule['schedulePayment'] ?? [], fn ($v) => (float) $v > 0));
    }

    #[DataProvider('services')]
    public function test_a_step_every_semester_steps_every_semester_and_repays_the_loan(string $service): void
    {
        $schedule = $this->loan($service, 'step-up', 'semi annually', 6, 12, 10, 0, 'semi annually');
        $payments = $this->payments($schedule);

        $this->assertCount(2, $payments);
        $this->assertEqualsWithDelta($payments[0] * 1.10, $payments[1], 0.01, 'the second installment steps up 10 %');
        $this->assertEqualsWithDelta(0, (float) end($schedule['endBalance']), 1, 'the loan is repaid (it ended with a balance)');
    }

    #[DataProvider('services')]
    public function test_a_step_every_quarter_steps_every_quarter_and_repays_the_loan(string $service): void
    {
        // step-down 5 % every quarter on a quarterly loan over 24 months: 8 installments, each 5 % below the one before
        $schedule = $this->loan($service, 'step-down', 'quarterly', 3, 24, 0, -5, 'quarterly');
        $payments = $this->payments($schedule);

        $this->assertCount(8, $payments);
        foreach (range(1, 7) as $i) {
            $this->assertEqualsWithDelta($payments[$i - 1] * 0.95, $payments[$i], 0.01, "installment ".($i + 1));
        }
        $this->assertEqualsWithDelta(0, (float) end($schedule['endBalance']), 1, 'the loan is repaid (it was overpaid by 268,635)');
    }

    public function test_the_example_matches_finvero(): void
    {
        // the figures of FinVero's LoanEngine for the same loan (tools/loan_parity, case
        // fixed-at-end|step-up|semi annually|12m|g0|semi annually, at a constant 20 % corridor)
        $corridor = array_fill_keys($this->dateIndexWithDate, 20.0);
        $schedule = (new CalculateFixedLoanAtEndService())->__calculateBasedOnDiffBaseRates($corridor, 'step-up', $this->dateIndexWithDate[2], 1_000_000.0, 3.0, 12,
            'semi annually', 6, 10, 'semi annually', 0, 'semi annually', 0, 2, $this->dateWithDateIndex, $this->dateIndexWithDate);

        $this->assertEqualsWithDelta([562721.86, 618994.05], array_map(fn ($v) => round((float) $v, 2), $this->payments($schedule)), 0.02);
    }
}
