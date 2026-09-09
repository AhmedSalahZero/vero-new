<?php

namespace Tests\Unit;

use App\Console\Commands\RestoreTimeOfDepositOfflineInterestsCommand;
use App\Console\Commands\RestoreTimeOfDepositPeriodInterestsCommand;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * * الفوايد دي ما راحتش أودو من الأصل و مش هتروح — تواريخها قبل تاريخ
 * * بداية التكامل بتاع الشركة ، فما اتعملش ليها قيود هناك
 *
 * * الفرق الجوهري عن الأمر التاني إن ده **مالوش أي علاقة بأودو** ، و إن
 * * فيه صفين متطابقين بالكامل (06-08-2025 بـ 1,125.81 مرتين) لازم
 * * يدخلوا الاتنين
 */
class RestoreTimeOfDepositOfflineInterestsTest extends TestCase
{
    /** @return array<int, array{date: string, amount: float}> */
    private function rows(): array
    {
        return (new ReflectionClass(RestoreTimeOfDepositOfflineInterestsCommand::class))->getConstant('ROWS');
    }

    private function source(): string
    {
        $source = file_get_contents(
            (new ReflectionClass(RestoreTimeOfDepositOfflineInterestsCommand::class))->getFileName()
        );
        $source = preg_replace('#/\*.*?\*/#s', '', $source);

        return preg_replace('#//[^\n]*#', '', $source);
    }

    /* ───────────── الصفوف ───────────── */

    public function test_it_carries_the_six_rows(): void
    {
        $this->assertCount(6, $this->rows());
    }

    public function test_the_rows_match_what_was_asked_for(): void
    {
        $expected = [
            ['date' => '2025-03-24', 'amount' => 17402.15],
            ['date' => '2025-04-05', 'amount' => 17402.15],
            ['date' => '2025-08-06', 'amount' => 1125.81],
            ['date' => '2025-08-06', 'amount' => 1125.81],
            ['date' => '2025-09-07', 'amount' => 1125.81],
            ['date' => '2025-10-02', 'amount' => 1064.96],
        ];

        $this->assertSame($expected, $this->rows());
    }

    /**
     * * آخر صفين اتكتبوا في الطلب بسنة 2026 ، و الأدلة التلاتة (النسخة
     * * القديمة للداتابيز ، تسلسل الفوايد الشهري ، و إن الوديعة استحقت
     * * في 2026-08-05) بتقول 2025
     */
    public function test_every_date_is_in_2025(): void
    {
        foreach ($this->rows() as $row) {
            $this->assertStringStartsWith('2025-', $row['date'], 'كل الصفوف قبل الاستحقاق');
        }
    }

    /**
     * * الفوايد المسترجعة في الأمر التاني بتبدأ من 04-11-2025 ، فالصفوف
     * * دي كلها لازم تكون قبلها — لو اتقاطعوا يبقى صف مكرر
     */
    public function test_the_rows_come_before_the_odoo_backed_ones(): void
    {
        $odooRows = (new ReflectionClass(RestoreTimeOfDepositPeriodInterestsCommand::class))->getConstant('ROWS');
        $firstOdooDate = min(array_column($odooRows, 'date'));

        foreach ($this->rows() as $row) {
            $this->assertLessThan($firstOdooDate, $row['date'], 'مفيش تقاطع مع الأمر التاني');
        }
    }

    /* ───────────── الصفين المكررين ───────────── */

    public function test_the_duplicate_row_is_deliberate_and_kept(): void
    {
        $duplicates = array_filter(
            $this->rows(),
            fn ($row) => $row['date'] === '2025-08-06' && $row['amount'] === 1125.81
        );

        $this->assertCount(2, $duplicates, 'الصفين دول حقيقيين مش تكرار بالغلط');
    }

    /**
     * * أهم تست هنا : لو المطابقة بالوجود بدل العدد ، الصف التاني هيتعتبر
     * * موجود و ما يدخلش خالص
     */
    public function test_matching_is_by_count_not_by_existence(): void
    {
        $source = $this->source();

        $this->assertStringContainsString('countExisting', $source);
        $this->assertStringContainsString('->count()', $source);
        $this->assertStringNotContainsString('findExisting', $source, 'المطابقة بالوجود بتبلع الصف المكرر');
        $this->assertStringContainsString('$wantedCount - $existingCount', $source, 'بيزوّد الفرق بس');
    }

    public function test_the_wanted_map_counts_the_duplicate_twice(): void
    {
        $method = new ReflectionMethod(RestoreTimeOfDepositOfflineInterestsCommand::class, 'wanted');
        $method->setAccessible(true);

        $wanted = $method->invoke(new RestoreTimeOfDepositOfflineInterestsCommand);

        $this->assertSame(2, $wanted['2025-08-06|1125.81'], 'المكرر لازم يتعدّ اتنين');
        $this->assertSame(1, $wanted['2025-03-24|17402.15']);
        $this->assertCount(5, $wanted, 'خمس مفاتيح لستة صفوف');
        $this->assertSame(6, array_sum($wanted));
    }

    /* ───────────── أودو ───────────── */

    /**
     * * الصفوف دي مالهاش قيود في أودو ، فممنوع الأمر يكتب أعمدة أودو أو
     * * ينادي أي خدمة
     */
    public function test_it_has_nothing_to_do_with_odoo(): void
    {
        $source = $this->source();

        foreach ([
            'interest_journal_entry_id',
            'interest_odoo_reference',
            'storePeriodInterestOdooRelations',
            'applyPeriodicInterestInStatement',
            'CashExpenseOdooService',
            'OdooPayment',
            'OdooSync',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, 'ممنوع: '.$forbidden);
        }
    }

    public function test_the_rows_carry_no_odoo_fields_at_all(): void
    {
        foreach ($this->rows() as $row) {
            $this->assertSame(['date', 'amount'], array_keys($row), 'التاريخ و المبلغ بس');
        }
    }

    /* ───────────── نفس ضمانات الأمر التاني ───────────── */

    public function test_it_does_not_depend_on_a_web_request(): void
    {
        $source = $this->source();

        $this->assertStringNotContainsString('handleDebitStatement', $source);
        $this->assertStringNotContainsString('getCurrentCompanyId', $source);
        $this->assertStringContainsString('storeCurrentAccountDebitBankStatement', $source);
    }

    public function test_it_marks_the_rows_as_period_interest(): void
    {
        $this->assertMatchesRegularExpression(
            '/storeCurrentAccountDebitBankStatement\(.*?true\s*\)/s',
            $this->source(),
            'لازم يتبعت isPeriodCdOrTdInterest = true'
        );
    }

    public function test_it_writes_inside_one_transaction(): void
    {
        $this->assertStringContainsString('DB::transaction', $this->source());
    }

    public function test_it_does_nothing_without_the_fix_flag(): void
    {
        $this->assertStringContainsString("if (! \$this->option('fix'))", $this->source());
    }

    public function test_the_two_commands_target_the_same_deposit(): void
    {
        $offline = (new ReflectionClass(RestoreTimeOfDepositOfflineInterestsCommand::class))->getConstant('DEPOSIT_ID');
        $odoo = (new ReflectionClass(RestoreTimeOfDepositPeriodInterestsCommand::class))->getConstant('DEPOSIT_ID');

        $this->assertSame(39, $offline);
        $this->assertSame($odoo, $offline);
    }
}
