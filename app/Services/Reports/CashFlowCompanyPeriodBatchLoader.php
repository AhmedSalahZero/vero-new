<?php

namespace App\Services\Reports;

use App\Enums\LcTypes;
use App\Enums\LgTypes;
use App\Models\CashExpense;
use App\Models\Cheque;
use App\Models\ForeignExchangeRate;
use App\Models\LetterOfCreditIssuance;
use App\Models\MoneyPayment;
use App\Models\MoneyReceived;
use App\Models\PayableCheque;
use App\Models\TimeOfDeposit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CashFlowCompanyPeriodBatchLoader
{
    private const LG_CASH_COVER_DATE_COLUMN = 'letter_of_guarantee_issuances.renewal_date';

    public static function apply(
        array &$result,
        Collection $foreignExchangeRates,
        string $mainFunctionalCurrency,
        int $companyId,
        string $periodStart,
        string $periodEnd,
        array $periodsByWeekKey,
    ): void {
        self::applyMoneyReceivedMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
        self::applyMoneyPaymentMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
        self::applyTimeOfDepositMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
        self::applyLetterOfGuaranteeMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
        self::applyLetterOfCreditMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
        self::applyCashExpenseMovements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey);
    }

    private static function applyMoneyReceivedMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        $totalCashInFlowKey = __('Total Cash Inflow');
        self::applyChequeSettlements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, Cheque::UNDER_COLLECTION, __('Cheques Under Collection'), $totalCashInFlowKey);
        self::applyChequeSettlements($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, Cheque::COLLECTED, __('Checks Collected'), $totalCashInFlowKey);
        self::applyMoneyReceivedByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyReceived::INCOMING_TRANSFER, __('Incoming Transfers'), $totalCashInFlowKey);
        self::applyMoneyReceivedByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyReceived::CASH_IN_BANK, __('Bank Deposits'), $totalCashInFlowKey);
        self::applyMoneyReceivedByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyReceived::CASH_IN_SAFE, __('Cash Collections'), $totalCashInFlowKey);
        self::applyChequeInSafe($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, $totalCashInFlowKey);
    }

    private static function applyChequeSettlements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey, string $chequeStatus, string $resultKey, string $totalCashInFlowKey): void
    {
        $dateColumn = $chequeStatus === Cheque::COLLECTED ? 'cheques.actual_collection_date' : 'cheques.expected_collection_date';
        $settlementAmountExpression = self::settlementAmountInReceivingCurrencySql();
        $query = DB::table('money_received')
            ->join('cheques', 'cheques.money_received_id', '=', 'money_received.id')
            ->join('settlements', 'money_received.id', '=', 'settlements.money_received_id')
            ->join('customer_invoices', 'customer_invoices.id', '=', 'settlements.invoice_id')
            ->where('money_received.company_id', $companyId)
            ->where('cheques.status', $chequeStatus)
            ->where('money_received.type', MoneyReceived::CHEQUE)
            ->where(function ($q) {
                $q->whereNull('money_received.down_payment_type')->orWhere('money_received.down_payment_type', '=', 'general');
            })
            ->whereBetween($dateColumn, [$periodStart, $periodEnd])
            ->selectRaw($settlementAmountExpression.' as received_amount, money_received.receiving_currency, '.$dateColumn.' as movement_date, customer_invoices.invoice_number');

        foreach ($query->cursor() as $row) {
            self::accumulateMoneyReceivedRow($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodsByWeekKey, $resultKey, $totalCashInFlowKey, $row, true);
        }
    }

    private static function applyChequeInSafe(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey, string $totalCashInFlowKey): void
    {
        $settlementAmountExpression = self::settlementAmountInReceivingCurrencySql();
        $query = DB::table('money_received')
            ->join('cheques', 'cheques.money_received_id', '=', 'money_received.id')
            ->join('settlements', 'money_received.id', '=', 'settlements.money_received_id')
            ->join('customer_invoices', 'customer_invoices.id', '=', 'settlements.invoice_id')
            ->where('money_received.company_id', $companyId)
            ->where('cheques.status', Cheque::IN_SAFE)
            ->where('money_received.type', MoneyReceived::CHEQUE)
            ->where(function ($q) {
                $q->whereNull('money_received.down_payment_type')->orWhere('money_received.down_payment_type', '=', 'general');
            })
            ->whereBetween('cheques.due_date', [$periodStart, $periodEnd])
            ->selectRaw($settlementAmountExpression.' as received_amount, money_received.receiving_currency, cheques.due_date as movement_date, customer_invoices.invoice_number');

        foreach ($query->cursor() as $row) {
            self::accumulateMoneyReceivedRow($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodsByWeekKey, __('Cheques In Safe'), $totalCashInFlowKey, $row, true);
        }
    }

    private static function applyMoneyReceivedByType(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey, string $moneyType, string $resultKey, string $totalCashInFlowKey): void
    {
        $query = DB::table('money_received')
            ->join('partners', 'partners.id', '=', 'money_received.partner_id')
            ->where('money_received.company_id', $companyId)
            ->where('money_received.type', $moneyType)
            ->whereBetween('money_received.receiving_date', [$periodStart, $periodEnd])
            ->selectRaw('money_received.received_amount, money_received.receiving_currency, money_received.receiving_date as movement_date, partners.name as partner_name');
        foreach ($query->cursor() as $row) {
            self::accumulateMoneyReceivedRow($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodsByWeekKey, $resultKey, $totalCashInFlowKey, $row, false);
        }
    }

    private static function accumulateMoneyReceivedRow(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, array $periodsByWeekKey, string $typeKey, string $totalCashInFlowKey, object $row, bool $useInvoiceDetail): void
    {
        $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
        if ($weekKey === null) {
            return;
        }
        $exchangeRate = ForeignExchangeRate::getExchangeRateAt((string) $row->receiving_currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
        $amount = (float) $row->received_amount * $exchangeRate;
        $label = $useInvoiceDetail && isset($row->invoice_number) ? (string) $row->invoice_number : (string) ($row->partner_name ?? '');

        $result['customers'][$typeKey][$label]['weeks'][$weekKey] = ($result['customers'][$typeKey][$label]['weeks'][$weekKey] ?? 0) + $amount;
        $result['customers'][$typeKey][$label]['total'][$weekKey] = ($result['customers'][$typeKey][$label]['total'][$weekKey] ?? 0) + $amount;
        $result['customers'][$typeKey]['total'][$weekKey] = ($result['customers'][$typeKey]['total'][$weekKey] ?? 0) + $amount;
        $result['customers'][$totalCashInFlowKey]['total'][$weekKey] = ($result['customers'][$totalCashInFlowKey]['total'][$weekKey] ?? 0) + $amount;
    }

    private static function applyMoneyPaymentMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        self::applyMoneyPaymentByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyPayment::OUTGOING_TRANSFER, null);
        self::applyMoneyPaymentByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyPayment::CASH_PAYMENT, null);
        self::applyMoneyPaymentByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyPayment::PAYABLE_CHEQUE, PayableCheque::PAID);
        self::applyMoneyPaymentByType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, MoneyPayment::PAYABLE_CHEQUE, PayableCheque::PENDING);
    }

    private static function applyMoneyPaymentByType(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey, string $moneyType, ?string $chequeStatus): void
    {
        $typeLabel = match ($moneyType) {
            MoneyPayment::OUTGOING_TRANSFER => __('Outgoing Transfers'),
            MoneyPayment::CASH_PAYMENT => __('Cash Payments'),
            MoneyPayment::PAYABLE_CHEQUE => $chequeStatus === PayableCheque::PAID ? __('Paid Payable Cheques') : __('Under Payment Payable Cheques'),
            default => $moneyType,
        };
        $query = DB::table('money_payments')->join('partners', 'partners.id', '=', 'money_payments.partner_id')->where('money_payments.company_id', $companyId)->where('money_payments.type', $moneyType);
        if ($chequeStatus !== null) {
            $query->join('payable_cheques', 'payable_cheques.money_payment_id', '=', 'money_payments.id')->where('payable_cheques.status', $chequeStatus);
            $dateField = $chequeStatus === PayableCheque::PAID ? 'payable_cheques.actual_payment_date' : 'payable_cheques.due_date';
        } else {
            $dateField = 'money_payments.delivery_date';
        }
        $query->whereBetween($dateField, [$periodStart, $periodEnd])->selectRaw('money_payments.paid_amount, money_payments.payment_currency, '.$dateField.' as movement_date, partners.name as partner_name');

        foreach ($query->cursor() as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $exchangeRate = ForeignExchangeRate::getExchangeRateAt((string) $row->payment_currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->paid_amount * $exchangeRate;
            $supplierName = (string) $row->partner_name;
            $result['suppliers'][$typeLabel][$supplierName]['weeks'][$weekKey] = ($result['suppliers'][$typeLabel][$supplierName]['weeks'][$weekKey] ?? 0) + (float) $row->paid_amount;
            $result['suppliers'][$typeLabel][$supplierName]['total'][$weekKey] = ($result['suppliers'][$typeLabel][$supplierName]['total'][$weekKey] ?? 0) + (float) $row->paid_amount;
            $result['suppliers'][$typeLabel]['total'][$weekKey] = ($result['suppliers'][$typeLabel]['total'][$weekKey] ?? 0) + $amount;
        }
    }

    private static function applyTimeOfDepositMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        $tdsTypes = [TimeOfDeposit::MATURED => __('Matured'), TimeOfDeposit::BROKEN => __('Broken'), TimeOfDeposit::RUNNING => __('Running')];
        $subType = __('Time Of Deposits');
        $totalCashInFlowKey = __('Total Cash Inflow');
        $rows = DB::table('time_of_deposits')
            ->where('time_of_deposits.company_id', $companyId)
            ->whereRaw("(CASE WHEN status = 'broken' THEN break_date WHEN status = 'matured' THEN deposit_date ELSE end_date END) BETWEEN ? AND ?", [$periodStart, $periodEnd])
            ->groupByRaw('status, currency, end_date')
            ->selectRaw("status,currency,CASE WHEN status = 'broken' THEN break_date WHEN status = 'matured' THEN deposit_date ELSE end_date END AS date,SUM(CASE WHEN status = 'matured' THEN amount + actual_interest_amount WHEN status = 'broken' THEN amount + break_interest_amount WHEN status = 'running' THEN amount + interest_amount ELSE 0 END) AS total_amount")
            ->get();
        foreach ($rows as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $status = $tdsTypes[$row->status] ?? $row->status;
            $exchangeRate = ForeignExchangeRate::getExchangeRateAt((string) $row->currency, $mainFunctionalCurrency, (string) $row->date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->total_amount * $exchangeRate;
            $result['customers'][$subType][$status]['weeks'][$weekKey] = ($result['customers'][$subType][$status]['weeks'][$weekKey] ?? 0) + $amount;
            $result['customers'][$subType][$status]['total'][$weekKey] = ($result['customers'][$subType][$status]['total'][$weekKey] ?? 0) + $amount;
            $result['customers'][$totalCashInFlowKey]['total'][$weekKey] = ($result['customers'][$totalCashInFlowKey]['total'][$weekKey] ?? 0) + $amount;
        }
    }

    private static function applyLetterOfGuaranteeMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        $lgsTypes = LgTypes::getAll();
        $feeType = __('LGs Commission & Fees');
        $coverType = __('Cancelled LGs Cash Cover');
        $totalCashInFlowKey = __('Total Cash Inflow');
        $feeRows = DB::table('current_account_bank_statements')
            ->where('current_account_bank_statements.company_id', $companyId)
            ->join('financial_institution_accounts', 'financial_institution_accounts.id', '=', 'current_account_bank_statements.financial_institution_account_id')
            ->join('letter_of_guarantee_issuances', 'letter_of_guarantee_issuances.id', '=', 'current_account_bank_statements.letter_of_guarantee_issuance_id')
            ->whereBetween('current_account_bank_statements.date', [$periodStart, $periodEnd])
            ->where('letter_of_guarantee_issuance_id', '>', 0)
            ->where(function ($q) {
                $q->where('is_renewal_fees', 1)->orWhere('is_commission_fees', 1)->orWhere('is_issuance_fees', 1);
            })
            ->groupByRaw('letter_of_guarantee_issuances.lg_type, financial_institution_accounts.currency, current_account_bank_statements.date')
            ->selectRaw('letter_of_guarantee_issuances.lg_type as lg_type, sum(credit) as paid_amount, financial_institution_accounts.currency as currency, current_account_bank_statements.date as movement_date')
            ->get();
        foreach ($feeRows as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $lgType = $lgsTypes[$row->lg_type] ?? $row->lg_type;
            $exchangeRate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate((string) $row->currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->paid_amount * $exchangeRate;
            $result['cash_expenses'][$feeType][$lgType]['weeks'][$weekKey] = ($result['cash_expenses'][$feeType][$lgType]['weeks'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$feeType][$lgType]['total'][$weekKey] = ($result['cash_expenses'][$feeType][$lgType]['total'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$feeType]['total'][$weekKey] = ($result['cash_expenses'][$feeType]['total'][$weekKey] ?? 0) + $amount;
        }
        $coverRows = DB::table('letter_of_guarantee_cash_cover_statements')
            ->where('letter_of_guarantee_cash_cover_statements.company_id', $companyId)
            ->join('letter_of_guarantee_issuances', 'letter_of_guarantee_issuances.id', '=', 'letter_of_guarantee_cash_cover_statements.letter_of_guarantee_issuance_id')
            ->whereBetween(self::LG_CASH_COVER_DATE_COLUMN, [$periodStart, $periodEnd])
            ->where('letter_of_guarantee_cash_cover_statements.letter_of_guarantee_issuance_id', '>', 0)
            ->groupByRaw('letter_of_guarantee_issuances.lg_type, letter_of_guarantee_cash_cover_statements.currency, '.self::LG_CASH_COVER_DATE_COLUMN)
            ->selectRaw('letter_of_guarantee_issuances.lg_type as lg_type, sum(debit) as total_amount, letter_of_guarantee_cash_cover_statements.currency as currency, '.self::LG_CASH_COVER_DATE_COLUMN.' as movement_date')
            ->get();
        foreach ($coverRows as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $lgType = $lgsTypes[$row->lg_type] ?? $row->lg_type;
            $exchangeRate = ForeignExchangeRate::getExchangeRateAt((string) $row->currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->total_amount * $exchangeRate;
            $result['customers'][$coverType][$lgType]['weeks'][$weekKey] = ($result['customers'][$coverType][$lgType]['weeks'][$weekKey] ?? 0) + $amount;
            $result['customers'][$coverType][$lgType]['total'][$weekKey] = ($result['customers'][$coverType][$lgType]['total'][$weekKey] ?? 0) + $amount;
            $result['customers'][$coverType]['total'][$weekKey] = ($result['customers'][$coverType]['total'][$weekKey] ?? 0) + $amount;
            $result['customers'][$totalCashInFlowKey]['total'][$weekKey] = ($result['customers'][$totalCashInFlowKey]['total'][$weekKey] ?? 0) + $amount;
        }
    }

    private static function applyLetterOfCreditMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        $lcsTypes = LcTypes::getAll();
        $feesType = __('LCs Commission & Fees');
        $remainType = __('LCs Remaining Amounts');
        $feeRows = DB::table('current_account_bank_statements')
            ->where('current_account_bank_statements.company_id', $companyId)
            ->join('financial_institution_accounts', 'financial_institution_accounts.id', '=', 'current_account_bank_statements.financial_institution_account_id')
            ->join('letter_of_credit_issuances', 'letter_of_credit_issuances.id', '=', 'current_account_bank_statements.letter_of_credit_issuance_id')
            ->whereBetween('current_account_bank_statements.date', [$periodStart, $periodEnd])
            ->where('letter_of_credit_issuance_id', '>', 0)
            ->where(function ($q) {
                $q->where('is_renewal_fees', 1)->orWhere('is_commission_fees', 1)->orWhere('is_issuance_fees', 1);
            })
            ->groupByRaw('letter_of_credit_issuances.lc_type, financial_institution_accounts.currency, current_account_bank_statements.date')
            ->selectRaw('letter_of_credit_issuances.lc_type as lc_type, sum(credit) as paid_amount, financial_institution_accounts.currency as currency, current_account_bank_statements.date as movement_date')
            ->get();
        foreach ($feeRows as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $lcType = $lcsTypes[$row->lc_type] ?? $row->lc_type;
            $exchangeRate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate((string) $row->currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->paid_amount * $exchangeRate;
            $result['cash_expenses'][$feesType][$lcType]['weeks'][$weekKey] = ($result['cash_expenses'][$feesType][$lcType]['weeks'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$feesType][$lcType]['total'][$weekKey] = ($result['cash_expenses'][$feesType][$lcType]['total'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$feesType]['total'][$weekKey] = ($result['cash_expenses'][$feesType]['total'][$weekKey] ?? 0) + $amount;
        }
        $lcRows = DB::table('letter_of_credit_issuances')
            ->where('letter_of_credit_issuances.company_id', $companyId)
            ->where('status', LetterOfCreditIssuance::RUNNING)
            ->whereBetween('letter_of_credit_issuances.due_date', [$periodStart, $periodEnd])
            ->selectRaw('letter_of_credit_issuances.due_date as movement_date, letter_of_credit_issuances.lc_type as lc_type, transaction_name, (amount_in_main_currency - cash_cover_amount) as paid_amount, lc_cash_cover_currency as currency')
            ->get();
        foreach ($lcRows as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $lcType = ($lcsTypes[$row->lc_type] ?? $row->lc_type).' [ '.$row->transaction_name.' ]';
            $exchangeRate = ForeignExchangeRate::getExchangeRateAt((string) $row->currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->paid_amount * $exchangeRate;
            $result['cash_expenses'][$remainType][$lcType]['weeks'][$weekKey] = ($result['cash_expenses'][$remainType][$lcType]['weeks'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$remainType][$lcType]['total'][$weekKey] = ($result['cash_expenses'][$remainType][$lcType]['total'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$remainType]['total'][$weekKey] = ($result['cash_expenses'][$remainType]['total'][$weekKey] ?? 0) + $amount;
        }
    }

    private static function applyCashExpenseMovements(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey): void
    {
        self::applyCashExpenseType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, CashExpense::OUTGOING_TRANSFER, 'payment_date', null);
        self::applyCashExpenseType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, CashExpense::CASH_PAYMENT, 'payment_date', null);
        self::applyCashExpenseType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, CashExpense::PAYABLE_CHEQUE, 'actual_payment_date', PayableCheque::PAID);
        self::applyCashExpenseType($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $periodsByWeekKey, CashExpense::PAYABLE_CHEQUE, 'due_date', PayableCheque::PENDING);
    }

    private static function applyCashExpenseType(array &$result, Collection $foreignExchangeRates, string $mainFunctionalCurrency, int $companyId, string $periodStart, string $periodEnd, array $periodsByWeekKey, string $moneyType, string $dateField, ?string $chequeStatus): void
    {
        $subTable = (new CashExpense())->getTable();
        $mainTable = match ($moneyType) {
            CashExpense::OUTGOING_TRANSFER => (new \App\Models\OutgoingTransfer())->getTable(),
            CashExpense::CASH_PAYMENT => (new \App\Models\CashPayment())->getTable(),
            default => (new PayableCheque())->getTable(),
        };
        $dateColumn = $moneyType === CashExpense::PAYABLE_CHEQUE ? 'payable_cheques.'.$dateField : 'cash_expenses.'.$dateField;
        $query = DB::table($mainTable)
            ->join($subTable, $subTable.'.id', '=', $mainTable.'.cash_expense_id')
            ->join('cash_expense_category_names', $subTable.'.cash_expense_category_name_id', '=', 'cash_expense_category_names.id')
            ->join('cash_expense_categories', 'cash_expense_category_names.cash_expense_category_id', '=', 'cash_expense_categories.id')
            ->where($subTable.'.type', $moneyType)
            ->where($subTable.'.company_id', $companyId)
            ->when($chequeStatus !== null, function ($builder) use ($chequeStatus, $mainTable) {
                $builder->where($mainTable.'.status', $chequeStatus);
            })
            ->whereBetween($dateColumn, [$periodStart, $periodEnd])
            ->groupByRaw('cash_expense_category_name_id, cash_expense_categories.name, cash_expense_category_names.name, '.$dateColumn)
            ->selectRaw('cash_expense_categories.name as category_name, cash_expense_category_names.name as expense_name, sum(paid_amount) as paid_amount, '.$subTable.'.currency as currency, '.$dateColumn.' as movement_date');
        foreach ($query->get() as $row) {
            $weekKey = CashFlowWeekBucketer::resolveWeekKey((string) $row->movement_date, $periodsByWeekKey);
            if ($weekKey === null) {
                continue;
            }
            $exchangeRate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate((string) $row->currency, $mainFunctionalCurrency, (string) $row->movement_date, $companyId, $foreignExchangeRates);
            $amount = (float) $row->paid_amount * $exchangeRate;
            $categoryName = (string) $row->category_name;
            $expenseName = (string) $row->expense_name;
            $result['cash_expenses'][$categoryName][$expenseName]['weeks'][$weekKey] = ($result['cash_expenses'][$categoryName][$expenseName]['weeks'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$categoryName][$expenseName]['total'][$weekKey] = ($result['cash_expenses'][$categoryName][$expenseName]['total'][$weekKey] ?? 0) + $amount;
            $result['cash_expenses'][$categoryName]['total'][$weekKey] = ($result['cash_expenses'][$categoryName]['total'][$weekKey] ?? 0) + $amount;
        }
    }

    private static function settlementAmountInReceivingCurrencySql(): string
    {
        return 'CASE
            WHEN money_received.currency IS NULL
                OR money_received.currency = money_received.receiving_currency
            THEN settlements.settlement_amount
            ELSE settlements.settlement_amount * money_received.exchange_rate
        END';
    }
}
