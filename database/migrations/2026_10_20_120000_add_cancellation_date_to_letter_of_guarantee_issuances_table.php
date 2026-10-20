<?php

use App\Models\LetterOfGuaranteeIssuance;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * cancel() و backToRunningStatus() بيكتبوا cancellation_date من زمان،
 * بس العمود عمره ما اتعمله migration. الخطابات الملغية القديمة التاريخ
 * بتاعها موجود بس على صف for-cancellation في letter_of_guarantee_statements.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('letter_of_guarantee_issuances', 'cancellation_date')) {
            Schema::table('letter_of_guarantee_issuances', function (Blueprint $table) {
                $table->date('cancellation_date')->nullable()->after('renewal_date');
            });
        }

        DB::update(
            'UPDATE letter_of_guarantee_issuances
            INNER JOIN (
                SELECT letter_of_guarantee_issuance_id, MIN(`date`) AS cancellation_date
                FROM letter_of_guarantee_statements
                WHERE type = ?
                GROUP BY letter_of_guarantee_issuance_id
            ) AS cancellation
                ON cancellation.letter_of_guarantee_issuance_id = letter_of_guarantee_issuances.id
            SET letter_of_guarantee_issuances.cancellation_date = cancellation.cancellation_date
            WHERE letter_of_guarantee_issuances.status = ?
              AND letter_of_guarantee_issuances.cancellation_date IS NULL',
            [LetterOfGuaranteeIssuance::FOR_CANCELLATION, LetterOfGuaranteeIssuance::CANCELLED]
        );
    }

    public function down(): void
    {
        if (! Schema::hasColumn('letter_of_guarantee_issuances', 'cancellation_date')) {
            return;
        }

        Schema::table('letter_of_guarantee_issuances', function (Blueprint $table) {
            $table->dropColumn('cancellation_date');
        });
    }
};
