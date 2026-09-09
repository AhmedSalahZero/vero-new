<?php

namespace Tests\Unit;

use App\Console\Commands\RestoreTimeOfDepositPeriodInterestsCommand;
use ReflectionClass;
use Tests\TestCase;

/**
 * * الفوايد الدورية بتاعة الوديعة 39 اتحذفت من عندنا بالغلط بس فضلت
 * * موجودة في أودو ، فالأمر بيرجّعها و بيربطها بقيود أودو الموجودة
 * * **من غير** ما يبعت أي حاجة لأودو تاني
 *
 * * التستات دي بتقفل على الحاجات اللي لو اتكسرت تعمل ضرر حقيقي:
 * * ازدواج قيود في أودو ، أو صفوف مكررة ، أو مبالغ غلط
 */
class RestoreTimeOfDepositPeriodInterestsTest extends TestCase
{
    /**
     * * اللي أودو رجّعه فعلاً لكل مرجع (اتأكدنا منه بـ search_read على
     * * account.move) — ده مصدر الحقيقة للمبالغ و أرقام القيود
     */
    private const ODOO_TRUTH = [
        'MISR/2025/00492' => ['id' => 14972, 'date' => '2025-11-04', 'amount' => 943.25],
        'MISR/2025/00493' => ['id' => 14973, 'date' => '2025-12-04', 'amount' => 1064.96],
        'MISR/2026/00020' => ['id' => 14974, 'date' => '2026-01-04', 'amount' => 943.25],
        'MISR/2026/00049' => ['id' => 15395, 'date' => '2026-02-02', 'amount' => 943.25],
        'MISR/2026/00076' => ['id' => 19265, 'date' => '2026-03-04', 'amount' => 1064.96],
        'MISR/2026/00153' => ['id' => 27439, 'date' => '2026-03-24', 'amount' => 17402.15],
        'MISR/2026/00094' => ['id' => 27373, 'date' => '2026-04-05', 'amount' => 943.25],
        'MISR/2026/00152' => ['id' => 27438, 'date' => '2026-04-05', 'amount' => 17402.15],
        'MISR/2026/00150' => ['id' => 27436, 'date' => '2026-04-20', 'amount' => 17402.15],
        'MISR/2026/00093' => ['id' => 27372, 'date' => '2026-04-28', 'amount' => 3862.47],
        'MISR/2026/00151' => ['id' => 27437, 'date' => '2026-04-30', 'amount' => 7110.55],
        'MISR/2026/00095' => ['id' => 27374, 'date' => '2026-05-03', 'amount' => 1064.96],
        'MISR/2026/00159' => ['id' => 27950, 'date' => '2026-06-02', 'amount' => 897.61],
        'MISR/2026/00191' => ['id' => 28550, 'date' => '2026-07-02', 'amount' => 1019.32],
    ];

    /** @return array<int, array<string, mixed>> */
    private function rows(): array
    {
        $reflection = new ReflectionClass(RestoreTimeOfDepositPeriodInterestsCommand::class);

        return $reflection->getConstant('ROWS');
    }

    public function test_it_carries_exactly_the_fourteen_deleted_interests(): void
    {
        $this->assertCount(14, $this->rows());
    }

    /**
     * * كل صف لازم يطابق أودو في التاريخ و المبلغ و رقم القيد
     */
    public function test_every_row_matches_what_odoo_actually_holds(): void
    {
        foreach ($this->rows() as $row) {
            $reference = strtok($row['reference'], ' ');

            $this->assertArrayHasKey($reference, self::ODOO_TRUTH, 'مرجع مش موجود في أودو: '.$reference);

            $truth = self::ODOO_TRUTH[$reference];

            $this->assertSame($truth['date'], $row['date'], $reference.' التاريخ');
            $this->assertSame($truth['amount'], $row['amount'], $reference.' المبلغ');
            $this->assertSame($truth['id'], $row['journal_entry_id'], $reference.' رقم القيد');
        }
    }

    /**
     * * المبلغين دول اتكتبوا في الطلب بأرقام متبادلة — أودو هو المرجع
     */
    public function test_the_two_transposed_amounts_follow_odoo_not_the_request(): void
    {
        $byReference = [];
        foreach ($this->rows() as $row) {
            $byReference[strtok($row['reference'], ' ')] = $row['amount'];
        }

        $this->assertSame(3862.47, $byReference['MISR/2026/00093'], 'مش 3862.74');
        $this->assertSame(897.61, $byReference['MISR/2026/00159'], 'مش 897.16');
    }

    public function test_no_reference_or_journal_entry_is_used_twice(): void
    {
        $references = array_column($this->rows(), 'reference');
        $journalEntries = array_column($this->rows(), 'journal_entry_id');

        $this->assertSame($references, array_unique($references), 'مرجع مكرر');
        $this->assertSame($journalEntries, array_unique($journalEntries), 'رقم قيد مكرر');
    }

    /**
     * * القيد ده موجود عندنا أصلاً ، فلو اتحط في القايمة كان هيتعمل صف
     * * مكرر
     */
    public function test_the_interest_we_still_have_is_not_in_the_list(): void
    {
        $references = array_map(fn ($row) => strtok($row['reference'], ' '), $this->rows());

        $this->assertNotContains('MISR/2026/00220', $references);
    }

    public function test_the_reference_format_matches_the_rows_the_app_writes(): void
    {
        foreach ($this->rows() as $row) {
            $this->assertMatchesRegularExpression(
                '#^MISR/\d{4}/\d{5} \(Interest Revenue\)$#',
                $row['reference'],
                'الصيغة لازم تطابق اللي المسار العادي بيكتبه'
            );
        }
    }

    /* ───────────── الحاجات اللي لو اتكسرت تعمل ضرر ───────────── */

    /**
     * * أهم تست هنا : لو الأمر عدّى على المسار العادي كان هيتعمل 14 قيد
     * * جديد في أودو — ازدواج في الإيرادات
     */
    public function test_it_never_reaches_odoo(): void
    {
        $source = $this->source();

        foreach ([
            'storePeriodInterestOdooRelations',
            'applyPeriodicInterestInStatement',
            'CashExpenseOdooService',
            'OdooPayment',
            'OdooSync',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, 'ممنوع ينادي '.$forbidden);
        }

        $this->assertStringContainsString('interest_journal_entry_id', $source, 'بيكتب رقم القيد الموجود');
        $this->assertStringContainsString('interest_odoo_reference', $source, 'بيكتب المرجع الموجود');
    }

    /**
     * * handleDebitStatement بتدوّر على الحساب بـ getCurrentCompanyId()
     * * اللي بتقرا رقم الشركة من الـ URL — و ده null في الـ CLI فالأمر
     * * كان بيقع. لازم يفضل مستقل عن أي سياق ويب عشان يشتغل على
     * * البرودكشن من الترمنال
     */
    public function test_it_does_not_depend_on_a_web_request(): void
    {
        $source = $this->source();

        $this->assertStringNotContainsString('handleDebitStatement', $source);
        $this->assertStringNotContainsString('getCurrentCompanyId', $source);
        $this->assertStringContainsString('storeCurrentAccountDebitBankStatement', $source);
    }

    public function test_it_marks_the_rows_as_period_interest(): void
    {
        $source = $this->source();

        $this->assertMatchesRegularExpression(
            '/storeCurrentAccountDebitBankStatement\(.*?true\s*\)/s',
            $source,
            'لازم يتبعت isPeriodCdOrTdInterest = true عشان الصف يتعامل معاملة الفايدة الدورية'
        );
    }

    public function test_it_skips_rows_that_already_exist(): void
    {
        $source = $this->source();

        $this->assertStringContainsString('findExisting', $source);
        $this->assertStringContainsString("whereDate('date'", $source);
        $this->assertStringContainsString('ROUND(debit, 2) = ?', $source, 'المطابقة بالتاريخ و المبلغ');
    }

    public function test_it_writes_inside_one_transaction(): void
    {
        $this->assertStringContainsString('DB::transaction', $this->source(), 'لو صف وقع مايفضلش نص إدخال');
    }

    public function test_it_does_nothing_without_the_fix_flag(): void
    {
        $source = $this->source();

        $this->assertStringContainsString("--fix", $source);
        $this->assertStringContainsString("if (! \$this->option('fix'))", $source, 'الافتراضي فحص بس');
    }

    /**
     * * بنشيل الكوميّنتات : الشرح جواها بيذكر أسماء الميثودز اللي احنا
     * * بنتأكد إن الكود مابيناديهاش ، فمن غير ده الفحص بيطلع إيجابي كاذب
     */
    private function source(): string
    {
        $source = file_get_contents(
            (new ReflectionClass(RestoreTimeOfDepositPeriodInterestsCommand::class))->getFileName()
        );

        $source = preg_replace('#/\*.*?\*/#s', '', $source);

        return preg_replace('#//[^\n]*#', '', $source);
    }
}
