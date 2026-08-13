<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * * بيضيف نفس اعمدة تتبع الفشل الموجودة في كل الجداول اللي بتتعامل مع
 * * اودو (synced_with_odoo, odoo_error_message) لجدولي settlements و
 * * payment_settlements.
 *
 * * الجدولين اصلا فيهم odoo_reference / odoo_reference_name لحالة النجاح،
 * * فالميجريشن دي بتضيف الناقص لحالة الفشل بس — عشان لما تسوية دفعة مقدمة
 * * تفشل مع فاتورة معينة يبقي ينفع نعرضها في صفحة الدفعات المقدمة بدل ما
 * * تظهر مرة واحدة كرسالة وتضيع.
 *
 * * الديفولت متطابق مع باقي الجداول (زي cash_expenses): synced_with_odoo
 * * بـ 1، لان التسوية اللي مجربتش تتزامن مع اودو اصلا (شركة مش مربوطة)
 * * مينفعش تتحسب فشل.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('settlements', 'synced_with_odoo')) {
                $table->boolean('synced_with_odoo')->default(1)->after('odoo_reference_name');
            }
            if (! Schema::hasColumn('settlements', 'odoo_error_message')) {
                $table->text('odoo_error_message')->nullable()->after('synced_with_odoo');
            }
        });

        Schema::table('payment_settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settlements', 'synced_with_odoo')) {
                $table->boolean('synced_with_odoo')->default(1)->after('odoo_reference_name');
            }
            if (! Schema::hasColumn('payment_settlements', 'odoo_error_message')) {
                $table->text('odoo_error_message')->nullable()->after('synced_with_odoo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settlements', function (Blueprint $table) {
            $table->dropColumn(['synced_with_odoo', 'odoo_error_message']);
        });

        Schema::table('payment_settlements', function (Blueprint $table) {
            $table->dropColumn(['synced_with_odoo', 'odoo_error_message']);
        });
    }
};
