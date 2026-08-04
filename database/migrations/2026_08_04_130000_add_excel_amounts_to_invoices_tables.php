<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * منطق excel_collected_amount / excel_paid_amount — منقول من cashvero.evoqas.com
 * ------------------------------------------------------------------
 * مصدر تحصيل/سداد ثالث للفاتورة جنب اللي موجود:
 *   collected_amount       -> اللي اتسجل من داخل النظام
 *   odoo_collected_amount  -> الجاي من أودو
 *   excel_collected_amount -> الجاي من استيراد إكسل   ← الجديد
 *
 * الأعمدة نفس تعريف نظائرها في cashvero بالظبط: decimal(14,5) nullable default 0،
 * وهي كمان نفس شكل odoo_collected_amount الموجود أصلًا هنا.
 *
 * ملحوظة: عمود الـ _in_main_currency بيتحسب في التريجر
 * (excel_* × exchange_rate) زي أودو بالظبط، فمش بيتكتب من التطبيق.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $columnsByTable = [
        'customer_invoices' => ['excel_collected_amount', 'excel_collected_amount_in_main_currency'],
        'supplier_invoices' => ['excel_paid_amount', 'excel_paid_amount_in_main_currency'],
    ];

    /** العمود اللي بنحط الجديد بعده — عشان الترتيب يفضل منطقي جنب أعمدة أودو */
    private array $afterColumn = [
        'customer_invoices' => 'odoo_collected_amount_in_main_currency',
        'supplier_invoices' => 'odoo_paid_amount_in_main_currency',
    ];

    public function up(): void
    {
        foreach ($this->columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $after = Schema::hasColumn($table, $this->afterColumn[$table])
                ? $this->afterColumn[$table]
                : null;

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column, $after) {
                    $definition = $blueprint->decimal($column, 14, 5)->nullable()->default(0);
                    if ($after) {
                        $definition->after($after);
                    }
                });

                $after = $column;
            }
        }
    }

    public function down(): void
    {
        foreach ($this->columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $existing = array_values(array_filter(
                $columns,
                fn (string $column) => Schema::hasColumn($table, $column)
            ));

            if ($existing === []) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($existing) {
                $blueprint->dropColumn($existing);
            });
        }
    }
};
