<?php

namespace App\Traits\Models;

use App\Helpers\HArr;
use App\Models\Contract;
use App\Models\ForeignExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared "Forecasted Project Collection" (customer side) / "Forecasted
 * Suppliers Contract Payments" (supplier side) calculation.
 *
 * Formula per order (confirmed with project owner, 2026-08):
 *
 *   Forecast = Order Amount − Unused Down Payment Balance
 *              − Σ(each linked invoice's own net_balance)
 *
 * Both halves are already settlement-aware at the data level, so no
 * special-case branching is needed here for "invoice fully/partially
 * settled by down payment":
 *   - "Unused Down Payment Balance" reads down_payment_balance — NOT
 *     the original down_payment_amount — which is the portion of a
 *     down payment still not applied to any invoice.
 *   - Each invoice's own net_balance already reflects any down
 *     payment settlement applied against IT specifically (see
 *     DownPaymentContractsController / IsInvoice::calculateNetBalanceInEditMode).
 *     A fully down-payment-settled invoice has net_balance = 0 and so
 *     contributes nothing here (avoiding double-counting against the
 *     down payment deduction above); a partially-settled invoice
 *     contributes only its remaining balance.
 *
 * Previously this exact formula was duplicated once in CustomerInvoice
 * and once in SupplierInvoice, both with the same bug: the down
 * payment/invoice subtraction was reversed (`down_payment - invoiced`
 * instead of the other way around), which — combined with then
 * subtracting THAT from the order amount — silently turned a
 * subtraction into an addition, and both used gross amounts instead
 * of their settlement-aware balances. Consolidated here so a future
 * fix only has to happen once.
 *
 * ── Supplier side / PO allocations (added 2026-08) ──────────────────
 * A Purchase Order belongs to the SUPPLIER's own contract, never to a
 * Customer contract directly. So when $contractId is a Customer
 * contract (as it always is on the single Contract Cash Flow report),
 * "find POs directly owned by this contract" finds nothing — the only
 * link is the po_allocations table (Customer contract -> allocated PO
 * -> that PO's real Supplier contract), the same table the "Suppliers
 * Invoices" row already relies on. When $poAllocations is passed
 * (supplier side only), each allocated PO is ALSO included here,
 * weighted by its allocation_percentage — on top of, not instead of,
 * any PO the contract directly owns (relevant when $contractId is
 * itself a genuine Supplier contract, e.g. picked directly on the
 * Consolidated Cash Flow report), so no existing behavior changes.
 *
 * ── Supplier side / child contracts (contract_scope, added 2026-09) ─
 * po_allocations is the OPTIONAL, explicit way to attach a PO to a
 * Customer contract (the "Allocate" modal on a Purchase Order). The
 * ordinary one is contracts.parent_id: a Supplier contract created
 * under a Customer contract is that contract's child, and its POs are
 * what the Customer contract's "Supplier Contracts" popup lists. With
 * 'contract_scope' => 'supplier_children' the contract query walks
 * that link instead (children of $contractId), which is how
 * SupplierInvoice::getForecastedProjectPayment builds the "Forecasted
 * Project Payment" row. Two notes on that mode:
 *   - Currency is NOT filtered. A Supplier contract routinely bills in
 *     a different currency from the Customer contract it hangs under
 *     (e.g. a EUR supplier under a USD project), and every amount is
 *     converted to the main functional currency below anyway — the
 *     same thing the "Suppliers Invoices" row already does.
 *   - POs already reachable through po_allocations for this same
 *     Customer contract are skipped ($excludeOrderIds), so a PO that
 *     happens to be linked BOTH ways is counted once, by the
 *     po_allocations row, and never twice.
 *
 * ── Currency selection ──────────────────────────────────────────────
 * $currency accepts either a single currency code or a LIST of codes.
 * The list form is what the Consolidated Cash Flow report passes (it
 * has an explicit multi-currency picker) and always narrows contracts
 * to exactly those currencies. For the single-code form, the
 * company-wide report viewing the MAIN functional currency tab widens
 * to every currency instead — each contract's own amounts are then
 * converted via its own FX rate below — while any specific foreign
 * currency tab, or a single contract, keeps the strict
 * same-currency-only filter.
 */
trait HasForecastedProjectCollection
{
    /**
     * @param  array<string,mixed>  $config  {
     *   main_result_type: 'customers'|'suppliers',
     *   result_key: string,                  // row label, e.g. 'Forecasted Project Collection'
     *   invoice_table: string,                // 'customer_invoices' | 'supplier_invoices'
     *   order_relation: string,               // Contract relation name: 'salesOrders' | 'purchasesOrders'
     *   order_number_key: string,             // key inside the order's own array: 'so_number' | 'po_number'
     *   invoice_order_number_column: string,  // matching column on the invoice table: 'sales_order_number' | 'purchases_order_number'
     *   down_payment_table: string,           // 'down_payment_settlements' | 'down_payment_money_payment_settlements'
     *   down_payment_order_id_column: string, // 'sales_order_id' | 'purchase_order_id'
     *   add_to_cash_inflow_total: bool,       // true for customer (cash IN); false for supplier (cash OUT, not part of inflow total)
     *   paid_or_collected_status: string,      // invoice_status value meaning "fully settled": SupplierInvoice::COLLETED_OR_PAID | CustomerInvoice::COLLETED_OR_PAID
     *   contract_scope?: 'self'|'supplier_children', // default 'self' — see class docblock
     * }
     * @param  Collection|null  $poAllocations  Supplier side only — PoAllocation rows (each already
     *                                          joined to its purchase_orders + contracts row, so it
     *                                          carries the PO's own columns plus allocation_percentage
     *                                          and the supplier contract's code) linking a Customer
     *                                          contract to Purchase Orders on a different, Supplier
     *                                          contract. Null/omitted on the customer side.
     */
    protected static function computeForecastedProjectCollection(
        array &$result,
        string $startDate,
        string $endDate,
        $currency,
        $companyId,
        array $datesWithWeekNumber,
        ?int $contractId,
        $foreignExchangeRates,
        ?string $mainFunctionalCurrency,
        array $config,
        ?Collection $poAllocations = null
    ): void {
        $mainResultType = $config['main_result_type'];
        $resultKey = $config['result_key'];
        $invoiceTable = $config['invoice_table'];
        $orderRelation = $config['order_relation'];
        $orderNumberKey = $config['order_number_key'];
        $invoiceOrderNumberColumn = $config['invoice_order_number_column'];
        $downPaymentTable = $config['down_payment_table'];
        $downPaymentOrderIdColumn = $config['down_payment_order_id_column'];
        $addToCashInflowTotal = $config['add_to_cash_inflow_total'];
        $paidOrCollectedStatus = $config['paid_or_collected_status'];
        $contractScope = $config['contract_scope'] ?? 'self';
        $useSupplierChildren = $contractScope === 'supplier_children';

        // 'supplier_children' only means anything relative to a specific
        // Customer contract — without one there is no parent to walk down
        // from, and an unscoped query would sweep in every Supplier
        // contract in the company.
        if ($useSupplierChildren && ! $contractId) {
            return;
        }

        // A PO reachable BOTH as a child contract's PO and through
        // po_allocations must be counted once — the allocation row wins
        // (it carries the allocation_percentage), so skip it here.
        $excludeOrderIds = [];
        if ($useSupplierChildren) {
            $excludeOrderIds = DB::table('po_allocations')
                ->where('contract_id', $contractId)
                ->whereNotNull('purchase_order_id')
                ->pluck('purchase_order_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        // An explicit LIST of currencies (Consolidated Cash Flow's
        // multi-currency picker) always means "exactly these" — the
        // widen-to-everything rule below only applies to the single
        // currency form used by the Company/Contract Cash Flow report.
        $currencyIsExplicitList = is_array($currency);
        $currencyList = $currencyIsExplicitList
            ? array_values(array_filter(array_map('strval', $currency)))
            : [(string) $currency];

        // Company-wide + main functional currency tab -> include contracts in
        // every currency (each gets converted below via its own currency's
        // FX rate). Any specific foreign-currency tab, or a single contract,
        // keeps the original same-currency-only filter.
        $showAllCurrenciesConverted = ! $currencyIsExplicitList
            && ! $contractId
            && $mainFunctionalCurrency !== null
            && $currencyList !== []
            && $currencyList[0] === $mainFunctionalCurrency;

        // Child Supplier contracts bill in their own currency, which is
        // routinely NOT the parent Customer contract's — filtering them
        // by it would silently drop them (everything is converted to the
        // main functional currency further down anyway).
        $filterByCurrency = ! $showAllCurrenciesConverted && ! $useSupplierChildren;

        // NOT filtered by contracts.end_date. It used to be
        // ->where('end_date', '<=', $endDate), i.e. "only contracts that
        // FINISH inside the report window" — which dropped the whole
        // contract, orders and all, whenever it ran past the window's
        // end. A contract running to July 2027 whose phases are invoiced
        // and collected in Oct/Nov/Dec 2026 is entirely normal, and its
        // Forecasted Project Collection row came out empty.
        //
        // The window is a property of each PHASE's collection date, not
        // of the contract: phase dates live on the order
        // (sales_orders/purchase_orders.end_date_N + collection_days_N),
        // so no contracts.* date can stand in for them. Every phase is
        // already checked against [$startDate, $endDate] in
        // applyForecastedOrderBalance() and an order with nothing inside
        // the window writes no row at all, so this was only ever a
        // coarse pre-filter — and a wrong one. Dropping it costs
        // nothing: the whole system holds a couple of hundred contracts,
        // at most ~130 for a single company.
        $contracts = Contract::where('company_id', $companyId)
            ->when($filterByCurrency, function ($query) use ($currencyList) {
                count($currencyList) === 1
                    ? $query->where('currency', $currencyList[0])
                    : $query->whereIn('currency', $currencyList);
            })
            ->when($contractId, function ($query) use ($contractId, $useSupplierChildren) {
                $useSupplierChildren
                    ? $query->where('parent_id', $contractId)->where('model_type', Contract::FOR_SUPPLIER)
                    : $query->where('id', $contractId);
            })
            ->with($orderRelation)
            ->get();

        // ── Orders the contract directly owns (customer side always;
        // supplier side only when $contractId happens to be that
        // Supplier's own contract — see class docblock). ─────────────
        foreach ($contracts as $contract) {
            foreach ($contract->{$orderRelation} as $order) {
                if (in_array((int) $order->id, $excludeOrderIds, true)) {
                    continue;
                }

                $phases = HArr::getNonZeroExecutionPhases($order->toArray());
                if (! $phases) {
                    continue;
                }

                self::applyForecastedOrderBalance(
                    $result, $phases, $contract, $order->id, $contract->id,
                    $startDate, $endDate, $currency, $companyId, $datesWithWeekNumber,
                    $foreignExchangeRates, $mainFunctionalCurrency,
                    $mainResultType, $resultKey, $invoiceTable, $orderNumberKey,
                    $invoiceOrderNumberColumn, $downPaymentTable, $downPaymentOrderIdColumn,
                    $addToCashInflowTotal, $paidOrCollectedStatus, 1.0
                );
            }
        }

        // ── Orders allocated to this (Customer) contract via
        // po_allocations, weighted by allocation_percentage. ──────────
        if ($poAllocations !== null) {
            foreach ($poAllocations as $poAllocation) {
                $phases = HArr::getNonZeroExecutionPhases($poAllocation->toArray());
                if (! $phases) {
                    continue;
                }

                // po_allocations links a Customer contract to a PO that
                // belongs to a DIFFERENT (Supplier) contract — fetch that
                // real Supplier contract fresh rather than trying to
                // reuse the joined row's attributes for it.
                $supplierContract = Contract::find($poAllocation->supplier_contract_id);
                if (! $supplierContract) {
                    continue;
                }

                $allocationPercentage = ((float) ($poAllocation->allocation_percentage ?? 0)) / 100;
                if ($allocationPercentage <= 0) {
                    continue;
                }

                self::applyForecastedOrderBalance(
                    $result, $phases, $supplierContract, $poAllocation->purchase_order_id, $poAllocation->customer_contract_id,
                    $startDate, $endDate, $currency, $companyId, $datesWithWeekNumber,
                    $foreignExchangeRates, $mainFunctionalCurrency,
                    $mainResultType, $resultKey, $invoiceTable, $orderNumberKey,
                    $invoiceOrderNumberColumn, $downPaymentTable, $downPaymentOrderIdColumn,
                    $addToCashInflowTotal, $paidOrCollectedStatus, $allocationPercentage
                );
            }
        }
    }

    /**
     * One order's (Sales Order / Purchase Order) contribution to the
     * forecast row — shared by both the "directly owned" and the
     * "allocated via po_allocations" paths above.
     *
     * ── Per-execution-phase split (fixed 2026-09) ───────────────────
     * $phases is every non-zero execution phase of the order, oldest
     * end_date first (HArr::getNonZeroExecutionPhases). Each phase is
     * invoiced at its own end_date and collected collection_days
     * later, so each lands in its OWN period bucket, carrying its own
     * share of the order amount.
     *
     * Before this, only the phase with the furthest end_date was read
     * and the WHOLE order amount was dropped into that single bucket —
     * a 1,000,000 contract split 50/20/30 across Sep/Oct/Nov showed as
     * 1,000,000 in December instead of 500,000 / 200,000 / 300,000
     * across October / November / December.
     *
     * ── Where the deduction lands (confirmed with project owner) ────
     * "Unused down payment + open invoices" is one number for the
     * whole order, but the forecast is now several buckets, so it has
     * to be spent somewhere. It eats the phases OLDEST FIRST: money
     * already received, or already invoiced, covers the phases that
     * have actually been executed, and only what is left over stays in
     * the later phases at their own dates.
     *
     * The deduction walk runs over EVERY phase in order — including
     * phases whose collection date falls outside the report window —
     * before the window check, otherwise an early out-of-window phase
     * would keep its share of the deduction unspent and the in-window
     * phases would be over-deducted.
     */
    private static function applyForecastedOrderBalance(
        array &$result,
        array $phases,
        Contract $contract,
        $orderId,
        $downPaymentContractId,
        string $startDate,
        string $endDate,
        $currency,
        $companyId,
        array $datesWithWeekNumber,
        $foreignExchangeRates,
        ?string $mainFunctionalCurrency,
        string $mainResultType,
        string $resultKey,
        string $invoiceTable,
        string $orderNumberKey,
        string $invoiceOrderNumberColumn,
        string $downPaymentTable,
        string $downPaymentOrderIdColumn,
        bool $addToCashInflowTotal,
        string $paidOrCollectedStatus,
        float $weightMultiplier
    ): void {
        $totalCashInFlowKey = __('Total Cash Inflow');

        // Order-level columns (amount, so_number/po_number) are copied
        // onto every phase, so any phase answers for the whole order.
        $orderAmount = (float) $phases[0]['amount'];
        $orderNumber = $phases[0][$orderNumberKey];

        $contractCode = $contract->getCode();
        $contractName = $contract->getName();
        $customerName = $contract->getClientName();

        // Sum of each linked invoice's OWN net_balance — already
        // settlement-aware (see trait docblock), so a fully
        // down-payment-settled invoice contributes 0 automatically,
        // and a partially-settled one contributes only what's left.
        // Explicitly excludes fully paid/collected invoices too (not
        // just relying on net_balance reaching 0 for them) — confirmed
        // with the project owner as the safer, authoritative signal.
        $invoicesNetBalance = (float) DB::table($invoiceTable)
            ->where('company_id', $companyId)
            ->where('currency', $contract->getCurrency())
            ->where($invoiceOrderNumberColumn, $orderNumber)
            ->where('contract_code', $contractCode)
            ->where('invoice_status', '!=', $paidOrCollectedStatus)
            ->sum('net_balance');

        // The portion of any down payment NOT yet applied to an
        // invoice — down_payment_balance, not the original
        // down_payment_amount, so it isn't double-counted once
        // it's been used to settle an invoice above.
        $unusedDownPaymentBalance = (float) DB::table($downPaymentTable)
            ->where('company_id', $companyId)
            ->where($downPaymentOrderIdColumn, $orderId)
            ->where('contract_id', $downPaymentContractId)
            ->sum('down_payment_balance');

        $remainingDeduction = $unusedDownPaymentBalance + $invoicesNetBalance;
        $rowLabel = $customerName.'-'.$contractName;

        foreach ($phases as $phase) {
            $phaseAmount = $orderAmount * $phase['share'];

            // min() is what keeps the forecast from going negative and
            // carries any excess on to the next phase.
            $deducted = min($remainingDeduction, $phaseAmount);
            $remainingDeduction -= $deducted;
            $phaseNetBalance = $phaseAmount - $deducted;

            $currentCollectionDate = Carbon::make($phase['end_date'])
                ->addDays((int) $phase['collection_days']);
            if (! $currentCollectionDate->between($startDate, $endDate)) {
                continue;
            }

            $currentCollectionDateFormatted = $currentCollectionDate->format('Y-m-d');
            if (! isset($datesWithWeekNumber[$currentCollectionDateFormatted])) {
                continue;
            }
            $currentWeekYear = $datesWithWeekNumber[$currentCollectionDateFormatted];

            // Each phase converts at the rate of ITS own collection
            // date, not one rate for the whole order.
            $exchangeRate = ForeignExchangeRate::getExchangeRateAtOrOne($contract->getCurrency(), $mainFunctionalCurrency, $currentCollectionDateFormatted, $companyId, $foreignExchangeRates);
            $phaseNetBalance = $phaseNetBalance * $exchangeRate * $weightMultiplier;

            $result[$mainResultType][$resultKey][$rowLabel]['weeks'][$currentWeekYear] =
                ($result[$mainResultType][$resultKey][$rowLabel]['weeks'][$currentWeekYear] ?? 0) + $phaseNetBalance;
            $result[$mainResultType][$resultKey][$rowLabel]['total'] =
                ($result[$mainResultType][$resultKey][$rowLabel]['total'] ?? 0) + $phaseNetBalance;
            $result[$mainResultType][$resultKey]['total'][$currentWeekYear] =
                ($result[$mainResultType][$resultKey]['total'][$currentWeekYear] ?? 0) + $phaseNetBalance;

            if ($addToCashInflowTotal) {
                $result['customers'][$totalCashInFlowKey]['total'][$currentWeekYear] =
                    ($result['customers'][$totalCashInFlowKey]['total'][$currentWeekYear] ?? 0) + $phaseNetBalance;
            }
        }
    }
}
