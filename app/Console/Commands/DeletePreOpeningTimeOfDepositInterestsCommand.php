<?php

namespace App\Console\Commands;

use App\Models\CurrentAccountBankStatement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * DeletePreOpeningTimeOfDepositInterestsCommand
 * ------------------------------------------------------------------
 * بيحذف فوايد الوديعة الزمنية اللي اتسجلت في كشف الحساب الجاري
 * بتاريخ **قبل** تاريخ أول المدة بتاع الشركة.
 *
 * المشكلة اللي بيحلها
 * -------------------
 * الوديعة رقم 37 وديعة opening balance (deducted_from_account_id = 0)
 * وبدايتها 2024-01-31 ، يعني قبل تاريخ أول المدة بتاع الشركة 92
 * (2025-07-31). لما اتنزلت عليها فوايد دورية ، الفوايد اتكتبت في كشف
 * الحساب الجاري بتواريخها الأصلية — فطلعت صفوف قبل رصيد أول المدة ،
 * وده بيخلي الكشف يبدأ برصيد مش الرصيد الافتتاحي المعتمد.
 *
 * الصفوف دي مالهاش أي قيد في أودو (interest_journal_entry_id = NULL)
 * لأن تواريخها قبل تاريخ بداية التكامل ، فالحذف محلي بالكامل ومفيش
 * حاجة تتفك من أودو.
 *
 * ليه الحذف بيعدّي على الموديل مش على DB::table مباشرة
 * ---------------------------------------------------
 * كشف الحساب الجاري بيمشي بالرصيد المتحرك (beginning_balance /
 * end_balance) ، فحذف صف لازم يعيد حساب كل اللي بعده.
 * deleteButTriggerChangeOnLastElement بتعمل ده: بتحذف العناصر
 * بـ query builder ما عدا الأقدم تاريخًا ، اللي بيتحذف عن طريق الموديل
 * فيشغّل الـ observer اللي بيعيد تشكيل الأرصدة من عنده و نازل — مرة
 * واحدة بدل مرة لكل صف.
 *
 * الأمان
 * ------
 * الأمر بيتحقق من كل صف قبل ما يحذفه: لازم يطابق المعرّف والتاريخ
 * والمبلغ والوديعة المتوقعين ، ولازم يكون فايدة دورية ، ولازم يكون
 * تاريخه فعلاً قبل تاريخ أول المدة ، ولازم ما يكونش ليه قيد أودو.
 * لو أي شرط اتكسر بيقف من غير ما يحذف حاجة.
 *
 * الأمر idempotent : الصفوف اللي اتحذفت قبل كده بيتخطاها بصمت.
 *
 * الاستخدام
 * ---------
 *   php artisan td:delete-pre-opening-interests          # فحص وطباعة الخطة بس
 *   php artisan td:delete-pre-opening-interests --fix    # تنفيذ فعلي
 */
class DeletePreOpeningTimeOfDepositInterestsCommand extends Command
{
    protected $signature = 'td:delete-pre-opening-interests
        {--fix : نفّذ الحذف فعليًا. بدونها بيفحص ويطبع الخطة من غير أي كتابة}';

    protected $description = 'يحذف فوايد الوديعة الزمنية المسجلة في كشف الحساب الجاري بتاريخ قبل تاريخ أول المدة';

    /**
     * * الصفوف المستهدفة بالظبط — مكتوبة صراحةً عشان الأمر ما يمسّش
     * * أي صف غيرها مهما اتغيرت الداتا حواليها
     *
     * @var array<int, array{id: int, date: string, debit: string, time_of_deposit_id: int}>
     */
    private const TARGETS = [
        ['id' => 2829, 'date' => '2025-03-24', 'debit' => '17402.15', 'time_of_deposit_id' => 37],
        ['id' => 2830, 'date' => '2025-04-05', 'debit' => '17402.15', 'time_of_deposit_id' => 37],
        ['id' => 2831, 'date' => '2025-04-20', 'debit' => '17402.15', 'time_of_deposit_id' => 37],
        ['id' => 2832, 'date' => '2025-04-30', 'debit' => '7110.55', 'time_of_deposit_id' => 37],
    ];

    private const COMPANY_ID = 92;

    public function handle(): int
    {
        $openingDate = DB::table('opening_balances')->where('company_id', self::COMPANY_ID)->value('date');

        if (! $openingDate) {
            $this->error('مفيش تاريخ أول مدة مسجل للشركة '.self::COMPANY_ID.' — وقفت من غير ما أحذف حاجة.');

            return self::FAILURE;
        }

        $this->info('تاريخ أول المدة للشركة '.self::COMPANY_ID.' : '.$openingDate);
        $this->newLine();

        $toDelete = [];
        $skipped = 0;

        foreach (self::TARGETS as $target) {
            $statement = CurrentAccountBankStatement::find($target['id']);

            if (! $statement) {
                $this->line('  تخطّي #'.$target['id'].' — محذوف بالفعل');
                $skipped++;

                continue;
            }

            if (! $this->matches($statement, $target, $openingDate)) {
                return self::FAILURE;
            }

            $toDelete[] = $statement;
            $this->line(sprintf(
                '  هيتحذف #%-5s %s  مدين %12s  وديعة %s',
                $statement->id,
                $statement->date,
                number_format((float) $statement->debit, 2),
                $statement->time_of_deposit_id
            ));
        }

        $this->newLine();

        if (! count($toDelete)) {
            $this->info('مفيش حاجة تتحذف'.($skipped ? ' — كل الصفوف اتحذفت قبل كده' : '').'.');

            return self::SUCCESS;
        }

        if (! $this->option('fix')) {
            $this->warn('فحص بس — مفيش أي كتابة. ضيف --fix للتنفيذ.');

            return self::SUCCESS;
        }

        /**
         * * لازم يتبعتوا مرتبين من الأحدث للأقدم : الميثود بتحذف الأخير
         * * في القايمة عن طريق الموديل عشان الـ observer يبدأ من أقدم
         * * تاريخ و يعيد حساب كل اللي بعده
         *
         * * و لازم تكون Eloquent Collection مش Support Collection — دي
         * * الـ type hint بتاع deleteButTriggerChangeOnLastElement ،
         * * فبنعيد جلبهم مرتبين بدل ما نرتب المصفوفة في الذاكرة
         */
        $ordered = CurrentAccountBankStatement::whereIn('id', collect($toDelete)->pluck('id'))
            ->orderByDesc('full_date')
            ->get();

        DB::transaction(function () use ($ordered) {
            CurrentAccountBankStatement::deleteButTriggerChangeOnLastElement($ordered);
        });

        $this->info('اتحذف '.$ordered->count().' صف واتعاد حساب الأرصدة بعدهم.');

        return self::SUCCESS;
    }

    /**
     * * بنتأكد إن الصف اللي في الداتابيز هو نفسه اللي متوقعينه — لو أي
     * * حاجة مختلفة بنقف بدل ما نحذف صف غلط
     */
    private function matches(CurrentAccountBankStatement $statement, array $target, string $openingDate): bool
    {
        $checks = [
            'الشركة' => [(int) $statement->company_id, self::COMPANY_ID],
            'التاريخ' => [substr((string) $statement->date, 0, 10), $target['date']],
            'المبلغ المدين' => [number_format((float) $statement->debit, 2, '.', ''), number_format((float) $target['debit'], 2, '.', '')],
            'الوديعة' => [(int) $statement->time_of_deposit_id, $target['time_of_deposit_id']],
            'فايدة دورية' => [(int) $statement->is_period_cd_or_td_interest, 1],
        ];

        foreach ($checks as $label => [$actual, $expected]) {
            if ($actual != $expected) {
                $this->error('الصف #'.$statement->id.' مش مطابق ('.$label.'): المتوقع '.$expected.' واللي موجود '.$actual.' — وقفت من غير ما أحذف حاجة.');

                return false;
            }
        }

        if (substr((string) $statement->date, 0, 10) >= substr((string) $openingDate, 0, 10)) {
            $this->error('الصف #'.$statement->id.' تاريخه مش قبل تاريخ أول المدة — وقفت من غير ما أحذف حاجة.');

            return false;
        }

        if ($statement->interest_journal_entry_id) {
            $this->error('الصف #'.$statement->id.' ليه قيد في أودو ('.$statement->interest_journal_entry_id.') — الحذف ده مش بيفك قيود أودو ، وقفت.');

            return false;
        }

        return true;
    }
}
