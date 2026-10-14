<?php

namespace App\Support\BankStatements;

/**
 * Month-end interest row types used by current-account and overdraft same-day ordering.
 * Only INTEREST_TYPES is needed here for ranking / nextFullDate; the rest of CashVero's
 * placeholder-constraint helpers are not part of the statement-ordering port.
 */
class GeneratedMonthEndInterestRows
{
    public const INTEREST_TYPES = ['end_of_month', 'end_of_month_final'];
}
