<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * اسم تسهيل خطاب الضمان / الاعتماد المستندي لازم يكون unique للبنك الواحد
 * في الشركة الواحدة. الفاليديشن في الـ FormRequest بيمسك الحالة من الفورم ،
 * والفهرس ده بيقفل الباب على أي POST مباشر يتخطى الفاليديشن.
 *
 * ملحوظة: عمود name بيقبل NULL ، و MySQL بتسمح بتكرار الـ NULL في الفهرس
 * الـ unique — فالصفوف القديمة اللي اسمها NULL مش هتتأثر.
 */
return new class extends Migration
{
    private const TABLES = [
        'letter_of_guarantee_facilities' => 'lg_facilities_company_bank_name_unique',
        'letter_of_credit_facilities'    => 'lc_facilities_company_bank_name_unique',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $indexName) {
            if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->unique(['company_id', 'financial_institution_id', 'name'], $indexName);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table => $indexName) {
            if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropUnique($indexName);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn ($index) => $index['name'] === $indexName);
    }
};
