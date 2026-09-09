<?php

namespace App\Console\Commands;

use App\Models\CurrentAccountBankStatement;
use App\Models\TimeOfDeposit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * RestoreTimeOfDepositPeriodInterestsCommand
 * ------------------------------------------------------------------
 * بيرجّع الفوايد الدورية بتاعة الوديعة الزمنية رقم 39 اللي اتحذفت
 * بالغلط من عندنا — من غير ما يبعت أي حاجة لأودو.
 *
 * ليه من غير أودو
 * ---------------
 * القيود موجودة في أودو أصلاً و ما اتحذفتش منه ، الحذف كان محلي بس.
 * فلو عدّينا على المسار العادي (applyPeriodicInterestInStatement)
 * كان هيتعمل قيد **جديد** في أودو لكل صف — يعني ازدواج في الإيرادات.
 * عشان كده الأمر ده بيبني الصف بنفس الطريقة بالظبط لكن بيكتب رقم
 * القيد و المرجع المعروفين بدل ما ينشئ قيد جديد.
 *
 * إزاي بيبني الصف
 * ---------------
 * بينادي handleDebitStatement بنفس الوسائط اللي
 * applyPeriodicInterestInStatement بتبعتها (بما فيها
 * isPeriodInterest = true) ، فالصف بيطلع مطابق لصف حقيقي في كل حاجة:
 * الأرصدة المتحركة ، full_date ، التعليقات ، is_debit ، days_count.
 * الفرق الوحيد إن أعمدة أودو بتتكتب من القايمة اللي تحت.
 *
 * الأمان
 * ------
 * idempotent : أي صف موجود بالفعل لنفس الوديعة بنفس التاريخ و المبلغ
 * بيتخطى. فتشغيله على بيئة الصفوف فيها موجودة مالوش أي أثر — و ده
 * المقصود: تشغّله على البرودكشن بعد الرفع من غير قلق.
 *
 * الاستخدام
 * ---------
 *   php artisan td:restore-period-interests          # فحص و طباعة الخطة بس
 *   php artisan td:restore-period-interests --fix    # تنفيذ فعلي
 */
class RestoreTimeOfDepositPeriodInterestsCommand extends Command
{
    protected $signature = 'td:restore-period-interests
        {--fix : نفّذ الإدخال فعليًا. بدونها بيفحص و يطبع الخطة من غير أي كتابة}';

    protected $description = 'يرجّع الفوايد الدورية المحذوفة للوديعة الزمنية 39 من غير ما يبعتها لأودو تاني';

    private const DEPOSIT_ID = 39;

    /**
     * * التاريخ و المبلغ و رقم قيد أودو و مرجعه — القيود دي موجودة في
     * * أودو فعلاً ، فالأمر بيربط بيها مش بينشئ غيرها
     *
     * @var array<int, array{date: string, amount: float, journal_entry_id: int, reference: string}>
     */
    private const ROWS = [
        ['date' => '2025-11-04', 'amount' => 943.25, 'journal_entry_id' => 14972, 'reference' => 'MISR/2025/00492 (Interest Revenue)'],
        ['date' => '2025-12-04', 'amount' => 1064.96, 'journal_entry_id' => 14973, 'reference' => 'MISR/2025/00493 (Interest Revenue)'],
        ['date' => '2026-01-04', 'amount' => 943.25, 'journal_entry_id' => 14974, 'reference' => 'MISR/2026/00020 (Interest Revenue)'],
        ['date' => '2026-02-02', 'amount' => 943.25, 'journal_entry_id' => 15395, 'reference' => 'MISR/2026/00049 (Interest Revenue)'],
        ['date' => '2026-03-04', 'amount' => 1064.96, 'journal_entry_id' => 19265, 'reference' => 'MISR/2026/00076 (Interest Revenue)'],
        ['date' => '2026-03-24', 'amount' => 17402.15, 'journal_entry_id' => 27439, 'reference' => 'MISR/2026/00153 (Interest Revenue)'],
        ['date' => '2026-04-05', 'amount' => 943.25, 'journal_entry_id' => 27373, 'reference' => 'MISR/2026/00094 (Interest Revenue)'],
        ['date' => '2026-04-05', 'amount' => 17402.15, 'journal_entry_id' => 27438, 'reference' => 'MISR/2026/00152 (Interest Revenue)'],
        ['date' => '2026-04-20', 'amount' => 17402.15, 'journal_entry_id' => 27436, 'reference' => 'MISR/2026/00150 (Interest Revenue)'],
        ['date' => '2026-04-28', 'amount' => 3862.47, 'journal_entry_id' => 27372, 'reference' => 'MISR/2026/00093 (Interest Revenue)'],
        ['date' => '2026-04-30', 'amount' => 7110.55, 'journal_entry_id' => 27437, 'reference' => 'MISR/2026/00151 (Interest Revenue)'],
        ['date' => '2026-05-03', 'amount' => 1064.96, 'journal_entry_id' => 27374, 'reference' => 'MISR/2026/00095 (Interest Revenue)'],
        ['date' => '2026-06-02', 'amount' => 897.61, 'journal_entry_id' => 27950, 'reference' => 'MISR/2026/00159 (Interest Revenue)'],
        ['date' => '2026-07-02', 'amount' => 1019.32, 'journal_entry_id' => 28550, 'reference' => 'MISR/2026/00191 (Interest Revenue)'],
    ];

    public function handle(): int
    {
        /** @var TimeOfDeposit|null $deposit */
        $deposit = TimeOfDeposit::find(self::DEPOSIT_ID);

        if (! $deposit) {
            $this->error('الوديعة رقم '.self::DEPOSIT_ID.' مش موجودة — وقفت.');

            return self::FAILURE;
        }

        $account = $deposit->maturityAmountAddedToAccount;
        $accountNumber = $deposit->getMaturityAmountAddedToAccountNumber();

        if (! $account || ! $accountNumber) {
            $this->error('الوديعة مالهاش حساب استحقاق — وقفت.');

            return self::FAILURE;
        }

        $accountId = $account->id;

        $this->info('الوديعة '.self::DEPOSIT_ID.' — حساب الاستحقاق '.$accountNumber);
        $this->newLine();

        $missing = [];
        $present = 0;

        foreach (self::ROWS as $row) {
            $existing = $this->findExisting($row);

            if ($existing) {
                $present++;
                $note = $existing->interest_journal_entry_id
                    ? ''
                    : '  ⚠ موجود من غير قيد أودو';
                $this->line(sprintf('  موجود  %s  %12s  #%s%s', $row['date'], number_format($row['amount'], 2), $existing->id, $note));

                continue;
            }

            $missing[] = $row;
            $this->line(sprintf('  ناقص   %s  %12s  قيد أودو %s', $row['date'], number_format($row['amount'], 2), $row['journal_entry_id']));
        }

        $this->newLine();
        $this->info('موجود: '.$present.'   ناقص: '.count($missing));

        if (! count($missing)) {
            $this->info('مفيش حاجة تترجّع.');

            return self::SUCCESS;
        }

        if (! $this->option('fix')) {
            $this->warn('فحص بس — مفيش أي كتابة. ضيف --fix للتنفيذ.');

            return self::SUCCESS;
        }

        $comment = __('Time Of Deposit').' '.$accountNumber;

        DB::transaction(function () use ($deposit, $accountId, $comment, $missing) {
            foreach ($missing as $row) {
                /**
                 * * نفس اللي handleDebitStatement بتعمله للحساب الجاري
                 * * بالظبط ، بس من غير ما نعدي عليها : هي بتدوّر على
                 * * الحساب بـ getCurrentCompanyId() اللي بيقرا رقم الشركة
                 * * من الـ URL — و ده بيرجع null في الـ CLI فالأمر كان
                 * * بيقع. احنا عارفين الحساب أصلاً من الوديعة نفسها
                 */
                $statement = $deposit->storeCurrentAccountDebitBankStatement(
                    $row['date'],
                    $row['amount'],
                    $accountId,
                    false,
                    $comment,
                    $comment,
                    true
                );

                /**
                 * * الفرق الوحيد عن المسار العادي : بنكتب قيد أودو
                 * * الموجود بدل ما ننشئ واحد جديد
                 */
                $statement->interest_journal_entry_id = $row['journal_entry_id'];
                $statement->interest_odoo_reference = $row['reference'];
                $statement->save();

                $this->line('  اترجّع  '.$row['date'].'  #'.$statement->id);
            }
        });

        $this->newLine();
        $this->info('اترجّع '.count($missing).' صف. مفيش أي نداء اتبعت لأودو.');

        return self::SUCCESS;
    }

    /**
     * * الصف بيتعتبر موجود لو نفس الوديعة و نفس التاريخ و نفس المبلغ
     */
    private function findExisting(array $row): ?CurrentAccountBankStatement
    {
        return CurrentAccountBankStatement::where('time_of_deposit_id', self::DEPOSIT_ID)
            ->whereDate('date', $row['date'])
            ->whereRaw('ROUND(debit, 2) = ?', [round($row['amount'], 2)])
            ->first();
    }
}
