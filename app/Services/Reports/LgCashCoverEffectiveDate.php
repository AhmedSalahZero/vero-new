<?php

namespace App\Services\Reports;

use App\Models\LetterOfGuaranteeIssuance;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Cash Flow buckets "Cancelled LGs Cash Cover" by the date cash cover actually
 * returns — the for-cancellation cash-cover statement date when the LG was
 * cancelled, otherwise renewal_date (current expiry / last renewal).
 *
 * letter_of_guarantee_issuances.cancellation_date stores that date for the
 * index, but this report stays on the cash-cover statement date because that
 * is the day the cash cover is returned. Those rows are deleted when an LG
 * is returned to running, so a left join is enough.
 */
final class LgCashCoverEffectiveDate
{
    public const ALIAS = 'lg_cancellation';

    public static function joinTo(Builder $query): Builder
    {
        return $query->leftJoinSub(
            DB::table('letter_of_guarantee_cash_cover_statements')
                ->select('letter_of_guarantee_issuance_id', DB::raw('min(date) as cancellation_date'))
                ->where('type', LetterOfGuaranteeIssuance::FOR_CANCELLATION)
                ->groupBy('letter_of_guarantee_issuance_id'),
            self::ALIAS,
            self::ALIAS.'.letter_of_guarantee_issuance_id',
            '=',
            'letter_of_guarantee_issuances.id'
        );
    }

    public static function sql(): string
    {
        return 'COALESCE('.self::ALIAS.'.cancellation_date, letter_of_guarantee_issuances.renewal_date)';
    }
}
