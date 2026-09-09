<?php

namespace App\Console\Commands;

use App\Models\CurrentAccountBankStatement;
use App\Models\TimeOfDeposit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * RestoreTimeOfDepositOfflineInterestsCommand
 * ------------------------------------------------------------------
 * بيرجّع الفوايد الدورية بتاعة الوديعة الزمنية 39 اللي **ما راحتش أودو
 * من الأصل** و مش هتروح — تواريخها قبل تاريخ بداية التكامل بتاع
 * الشركة ، فما اتعملش ليها قيود هناك و مالهاش أي علاقة بأودو خالص.
 *
 * الفرق عن td:restore-period-interests
 * ------------------------------------
 * الأمر التاني بيرجّع الفوايد اللي **ليها** قيود موجودة في أودو ،
 * فبيكتب interest_journal_entry_id و interest_odoo_reference عشان
 * يربطها بيها. الفوايد اللي هنا مالهاش قيود أصلاً ، فالعمودين دول
 * بيفضلوا فاضيين — و ده الصح ، مش نقص.
 *
 * الصفوف المكررة
 * ---------------
 * فيه فايدتين بنفس التاريخ و نفس المبلغ (06-08-2025 بـ 1,125.81) —
 * دول صفين حقيقيين مش تكرار بالغلط. عشان كده المطابقة هنا **بالعدد**
 * مش بالوجود: لو المطلوب اتنين و الموجود واحد ، بيتزاد واحد بس.
 *
 * الأمان
 * ------
 * idempotent : بيحسب الناقص لكل (تاريخ + مبلغ) و بيزوّد الفرق بس ،
 * فتشغيله أكتر من مرة مالوش أثر إضافي.
 *
 * الاستخدام
 * ---------
 *   php artisan td:restore-offline-interests          # فحص و طباعة الخطة بس
 *   php artisan td:restore-offline-interests --fix    # تنفيذ فعلي
 */
class RestoreTimeOfDepositOfflineInterestsCommand extends Command
{
    protected $signature = 'td:restore-offline-interests
        {--fix : نفّذ الإدخال فعليًا. بدونها بيفحص و يطبع الخطة من غير أي كتابة}';

    protected $description = 'يرجّع الفوايد الدورية للوديعة 39 اللي ما راحتش أودو من الأصل';

    private const DEPOSIT_ID = 39;

    /**
     * * التاريخ و المبلغ بس — مفيش أي حاجة تخص أودو
     *
     * * السنة في آخر صفين اتأكدت من النسخة القديمة للداتابيز و من
     * * تسلسل الفوايد الشهري (06-08 ثم 07-09 ثم 02-10 ثم 04-11) و من
     * * إن الوديعة استحقت في 2026-08-05 — يعني 2025 مش 2026
     *
     * @var array<int, array{date: string, amount: float}>
     */
    private const ROWS = [
        ['date' => '2025-03-24', 'amount' => 17402.15],
        ['date' => '2025-04-05', 'amount' => 17402.15],
        ['date' => '2025-08-06', 'amount' => 1125.81],
        ['date' => '2025-08-06', 'amount' => 1125.81],
        ['date' => '2025-09-07', 'amount' => 1125.81],
        ['date' => '2025-10-02', 'amount' => 1064.96],
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

        $this->info('الوديعة '.self::DEPOSIT_ID.' — حساب الاستحقاق '.$accountNumber);
        $this->newLine();

        $toInsert = [];

        foreach ($this->wanted() as $key => $wantedCount) {
            [$date, $amount] = explode('|', $key);
            $existingCount = $this->countExisting($date, (float) $amount);
            $shortfall = $wantedCount - $existingCount;

            $this->line(sprintf(
                '  %s  %12s   مطلوب %s  موجود %s  %s',
                $date,
                number_format((float) $amount, 2),
                $wantedCount,
                $existingCount,
                $shortfall > 0 ? 'هيتزاد '.$shortfall : 'تمام'
            ));

            for ($i = 0; $i < $shortfall; $i++) {
                $toInsert[] = ['date' => $date, 'amount' => (float) $amount];
            }
        }

        $this->newLine();

        if (! count($toInsert)) {
            $this->info('مفيش حاجة تتزاد.');

            return self::SUCCESS;
        }

        $this->info('الناقص: '.count($toInsert).' صف');

        if (! $this->option('fix')) {
            $this->warn('فحص بس — مفيش أي كتابة. ضيف --fix للتنفيذ.');

            return self::SUCCESS;
        }

        $comment = __('Time Of Deposit').' '.$accountNumber;

        DB::transaction(function () use ($deposit, $account, $comment, $toInsert) {
            foreach ($toInsert as $row) {
                /**
                 * * نفس اللي المسار العادي بيعمله للحساب الجاري — من غير
                 * * ما نعدي على handleDebitStatement لأنها بتدوّر على
                 * * الحساب بـ getCurrentCompanyId() اللي بتقرا من الـ URL
                 * * و بترجع null في الـ CLI
                 *
                 * * و من غير أي نداء لأودو : الصفوف دي مالهاش قيود هناك
                 */
                $statement = $deposit->storeCurrentAccountDebitBankStatement(
                    $row['date'],
                    $row['amount'],
                    $account->id,
                    false,
                    $comment,
                    $comment,
                    true
                );

                $this->line('  اترجّع  '.$row['date'].'  '.number_format($row['amount'], 2).'  #'.$statement->id);
            }
        });

        $this->newLine();
        $this->info('اترجّع '.count($toInsert).' صف. مفيش أي نداء اتبعت لأودو و مفيش أعمدة أودو اتكتبت.');

        return self::SUCCESS;
    }

    /**
     * * المطلوب لكل (تاريخ + مبلغ) و عدده — عشان الصفين المكررين
     * * يتعاملوا كصفين حقيقيين
     *
     * @return array<string, int>
     */
    private function wanted(): array
    {
        $wanted = [];

        foreach (self::ROWS as $row) {
            $key = $row['date'].'|'.$row['amount'];
            $wanted[$key] = ($wanted[$key] ?? 0) + 1;
        }

        return $wanted;
    }

    private function countExisting(string $date, float $amount): int
    {
        return CurrentAccountBankStatement::where('time_of_deposit_id', self::DEPOSIT_ID)
            ->whereDate('date', $date)
            ->whereRaw('ROUND(debit, 2) = ?', [round($amount, 2)])
            ->count();
    }
}
