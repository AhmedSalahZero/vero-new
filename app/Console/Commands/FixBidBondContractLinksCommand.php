<?php

namespace App\Console\Commands;

use App\Enums\LgTypes;
use App\Models\LetterOfGuaranteeIssuance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * FixBidBondContractLinksCommand
 * ------------------------------------------------------------------
 * تفريغ ربط العقد وأمر الشراء من كل خطابات الضمان من نوع
 * Bid Bond (خطاب دخول مناقصة).
 *
 * المشكلة اللي بيحلها
 * -------------------
 * الـ Bid Bond بطبيعته مش مربوط بعقد — لسه مفيش عقد أصلاً، ده خطاب
 * لدخول مناقصة. وحقول Contract / Purchase Order مخفية في الفورم
 * لما يكون النوع Bid Bond.
 *
 * لكن الفورم كان بيفضّي الحقول دي بس لما المستخدم يغيّر نوع الخطاب
 * *بنفسه* إلى Bid Bond. أي سجل كان محمّل أصلاً كـ Bid Bond (مثلاً
 * خطاب كان مربوط بعقد وبعدين اتغيّر نوعه، أو اتعدّل قبل ما الإصلاح
 * ده يوجد) كان بيحتفظ بالـ contract_id القديم في الحقل المخفي،
 * وإعادة الحفظ كانت بتحافظ عليه بصمت.
 *
 * النتيجة: خطاب Bid Bond ملغي كان بيظهر في تقرير التدفق النقدي
 * لعقد معيّن، رغم إن الـ Bid Bond مفروض ما يترتبطش بعقد إطلاقاً —
 * وده بيأثر على أرقام "Cancelled LGs Cash Cover" و
 * "Issued LG Cash Cover" في تقرير تدفق العقد.
 *
 * الحل
 * ----
 *  1. الجزء المستقبلي (اتعمل خلاص): LetterOfGuaranteeIssuanceController::store()
 *     بيفرض contract_id/purchase_order_id/purchase_order_date = null
 *     على مستوى السيرفر لأي Bid Bond، مهما كان اللي اتبعت من الفورم.
 *     و update() بيمرّ على نفس الدالة (بيحذف ويعيد الإنشاء)، فالاتنين
 *     مغطيين.
 *  2. الجزء ده: تنضيف السجلات القديمة اللي اتسجلت قبل الإصلاح.
 *
 * ⚠️ التحديث هنا عن طريق query builder مباشرة وليس عن طريق الموديل،
 * عن قصد: الهدف تصحيح بيانات غلط اتخزّنت، مش تنفيذ عملية عمل جديدة.
 * المرور بالموديل كان هيولّع model events (مزامنة أودو، إعادة حساب
 * كشوف الحسابات والـ statements) على حاجة مالية ما اتغيرش فيها ولا
 * مليم — ودي آثار جانبية مش مطلوبة هنا خالص.
 *
 * الاستخدام
 * ---------
 *   php artisan lg:fix-bid-bond-links                 # فحص فقط (بدون أي كتابة)
 *   php artisan lg:fix-bid-bond-links --fix           # تنفيذ فعلي
 *   php artisan lg:fix-bid-bond-links --fix --company=92
 */
class FixBidBondContractLinksCommand extends Command
{
    protected $signature = 'lg:fix-bid-bond-links
        {--fix : نفّذ التعديل فعليًا. بدونها الأمر بيفحص ويطبع الخطة من غير أي كتابة}
        {--company= : قصر التنفيذ على شركة واحدة}';

    protected $description = 'يفرّغ contract_id و purchase_order_id من كل خطابات الضمان من نوع Bid Bond (لا تُربط بعقد أبداً)';

    /**
     * الحقول اللي بتتفضّى — نفس الحقول بالظبط اللي
     * LetterOfGuaranteeIssuanceController::store() بيفرّغها عند الحفظ،
     * عشان التنضيف الأثري والسلوك المستقبلي يفضلوا متطابقين.
     */
    private const NULLABLE_COLUMNS = ['contract_id', 'purchase_order_id', 'purchase_order_date'];

    public function handle(): int
    {
        $companyId = $this->option('company');

        $rows = DB::table('letter_of_guarantee_issuances')
            ->where('lg_type', LgTypes::BID_BOND)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) {
                foreach (self::NULLABLE_COLUMNS as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->orderBy('company_id')
            ->orderBy('id')
            ->get(array_merge(['id', 'company_id', 'lg_code', 'status'], self::NULLABLE_COLUMNS));

        $totalBidBonds = DB::table('letter_of_guarantee_issuances')
            ->where('lg_type', LgTypes::BID_BOND)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->count();

        $this->line('');
        $this->line("إجمالي خطابات Bid Bond: {$totalBidBonds}");

        if ($rows->isEmpty()) {
            $this->info('✔ كل خطابات الـ Bid Bond مفكوكة من العقود وأوامر الشراء بالفعل — مفيش أي حاجة تتصلح.');

            return self::SUCCESS;
        }

        $this->warn('خطابات محتاجة تنضيف: '.$rows->count());
        $this->line('');

        $table = [];
        $perColumn = array_fill_keys(self::NULLABLE_COLUMNS, 0);

        foreach ($rows as $row) {
            $dirty = [];
            foreach (self::NULLABLE_COLUMNS as $column) {
                if ($row->{$column} !== null) {
                    $dirty[] = $column.'='.$row->{$column};
                    $perColumn[$column]++;
                }
            }

            $table[] = [
                $row->company_id,
                $row->id,
                $row->lg_code ?: '—',
                $row->status ?: '—',
                implode(', ', $dirty),
            ];
        }

        $this->table(['الشركة', 'الخطاب', 'كود الخطاب', 'الحالة', 'القيم اللي هتتفضّى'], $table);

        foreach ($perColumn as $column => $count) {
            $this->line("  {$column}: {$count} خطاب");
        }
        $this->line('');

        if (! $this->option('fix')) {
            $this->warn('ده فحص فقط ولم تُكتب أي بيانات. أضف --fix للتنفيذ الفعلي.');

            return self::SUCCESS;
        }

        $ids = $rows->pluck('id')->all();

        // ترانزاكشن واحدة: يا الكل يتصلح يا ولا واحد — عشان ما نسيبش
        // نص السجلات متفكوكة والنص التاني لأ لو حصل أي خطأ في النص.
        DB::transaction(function () use ($ids) {
            DB::table('letter_of_guarantee_issuances')
                ->whereIn('id', $ids)
                ->update(array_fill_keys(self::NULLABLE_COLUMNS, null));
        });

        $remaining = DB::table('letter_of_guarantee_issuances')
            ->where('lg_type', LgTypes::BID_BOND)
            ->whereIn('id', $ids)
            ->where(function ($query) {
                foreach (self::NULLABLE_COLUMNS as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->count();

        if ($remaining > 0) {
            $this->error("لسه فيه {$remaining} خطاب ما اتفكّش — راجع الأمر.");

            return self::FAILURE;
        }

        $this->info('✔ تم تنضيف '.count($ids).' خطاب Bid Bond بنجاح.');

        return self::SUCCESS;
    }
}
