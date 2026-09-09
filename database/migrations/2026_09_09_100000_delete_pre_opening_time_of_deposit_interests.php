<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * * فوايد الوديعة الزمنية رقم 37 (وديعة opening balance بدايتها
 * * 2024-01-31) اتكتبت في كشف الحساب الجاري بتواريخها الأصلية ، فطلعت
 * * أربع صفوف بتاريخ **قبل** تاريخ أول المدة بتاع الشركة 92
 * * (2025-07-31) — يعني الكشف بيبدأ برصيد مش الرصيد الافتتاحي المعتمد
 *
 * * الحذف نفسه في td:delete-pre-opening-interests مش هنا ، عشان يكون
 * * ليه dry-run و فحوصات تطابق ينفع تتشغل بالإيد قبل التنفيذ. المايجريشن
 * * بينادي الأمر بـ --fix بس
 *
 * * الأمر بيتحقق من كل صف قبل حذفه (المعرّف + التاريخ + المبلغ +
 * * الوديعة + إنه فايدة دورية + إنه قبل تاريخ أول المدة + إنه مالوش قيد
 * * أودو) و بيقف من غير ما يحذف حاجة لو أي شرط اتكسر ، و بيتخطى الصفوف
 * * المحذوفة أصلاً — فتشغيله أكتر من مرة مالوش أثر إضافي
 */
return new class extends Migration
{
    public function up(): void
    {
        // $exitCode = Artisan::call('td:delete-pre-opening-interests', ['--fix' => true]);

        // echo Artisan::output();

        // if ($exitCode !== 0) {
        //     throw new RuntimeException('td:delete-pre-opening-interests فشل — راجع الرسالة فوق. مفيش صفوف اتحذفت.');
        // }
    }

    /**
     * * مفيش رجوع : الصفوف دي فوايد اتسجلت بتواريخ غلط ، و إعادة
     * * إنشائها كانت هترجّع نفس المشكلة
     */
    public function down(): void
    {
        //
    }
};
