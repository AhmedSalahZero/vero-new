<?php

namespace App\Services\Reports;

use App\Http\Controllers\CashFlowReportController;
use App\Models\CashExpense;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomerInvoice;
use App\Models\ForeignExchangeRate;
use App\Models\LoanSchedule;
use App\Models\PoAllocation;
use App\Models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsolidatedCashFlowService
{
    public function build(Company $company, Request $request): array
    {
        $contractIds = $this->resolveContractIds($company, $request);
        return $this->buildReport($company, $request, $contractIds);
    }

    /**
     * Reads one side's past-due tier plan.
     *
     * Sent as two flat, parallel arrays matched by index, NOT as one array
     * of {percentage, days} objects: a GET query string doesn't reliably
     * keep an object's keys together inside an array — [{percentage:100},
     * {days:120}] can arrive instead of [{percentage:100,days:120}], so
     * every tier silently failed the "both fields present" check and
     * nothing was ever applied. Two parallel arrays are the same reliable
     * shape contract_ids/currencies already use.
     *
     * @return list<array{percentage: float, days: int}>
     */
    private function normalizeTiers(Request $request, string $percentageField, string $daysField): array
    {
        $percentages = $request->input($percentageField, []);
        $days = $request->input($daysField, []);
        if (! is_array($percentages) || ! is_array($days)) {
            return [];
        }

        $tiers = [];
        $count = max(count($percentages), count($days));
        for ($i = 0; $i < $count; $i++) {
            $percentage = (float) ($percentages[$i] ?? 0);
            $tierDays = (int) ($days[$i] ?? 0);
            if ($percentage <= 0 || $tierDays <= 0) {
                continue;
            }
            $tiers[] = ['percentage' => $percentage, 'days' => $tierDays];
        }

        return $tiers;
    }

    /**
     * Applies the past-due collection/payment tier plan. Runs per invoice:
     * each tier's percentage of that invoice's own balance is bucketed into
     * the week at "today + tier days", then routed to the invoice's own
     * contract block if included, or into the unallocated arrays otherwise.
     * Mutates $contractsSection, $companyUnallocatedCashIn and
     * $companyUnallocatedCashOut in place.
     *
     * @param  list<array<string, mixed>>  $contractsSection
     * @param  array<string, float|int>  $companyUnallocatedCashIn
     * @param  array<string, float|int>  $companyUnallocatedCashOut
     * @param  Collection<int, Collection>  $poByContract
     * @param  array<string, string>  $datesWithWeekNumber
     * @param  list<array{percentage: float, days: int}>  $customerTiers
     * @param  list<array{percentage: float, days: int}>  $supplierTiers
     */
    private function applyPastDueTierMovements(
        array &$contractsSection,
        array &$companyUnallocatedCashIn,
        array &$companyUnallocatedCashOut,
        CashFlowReportController $controller,
        Collection $poByContract,
        string $currency,
        int $companyId,
        string $mainFunctionalCurrency,
        array $datesWithWeekNumber,
        array $customerTiers,
        array $supplierTiers,
    ): void {
        $indexByContractCode = [];
        $indexByContractId = [];
        foreach ($contractsSection as $i => $block) {
            $code = (string) ($block['contract_code'] ?? '');
            if ($code !== '') {
                $indexByContractCode[$code] = $i;
            }
            $indexByContractId[(int) ($block['contract_id'] ?? 0)] = $i;
        }

        $applyTier = function (int $index, string $rowKey, string $weekKey, float $amount) use (&$contractsSection): void {
            $contractsSection[$index][$rowKey][$weekKey] = ($contractsSection[$index][$rowKey][$weekKey] ?? 0) + $amount;
            // Contract-level Net Cash = cash_inflow − cash_outflow (no cash & banks
            // component at this level — see ContractCashFlowBatchBuilder), so it
            // has to be kept in sync with whichever side this injection touched.
            $inflow = (float) ($contractsSection[$index]['cash_inflow'][$weekKey] ?? 0);
            $outflow = (float) ($contractsSection[$index]['cash_outflow'][$weekKey] ?? 0);
            $contractsSection[$index]['net_cash'][$weekKey] = $inflow - $outflow;
        };

        // ── Customer past-due invoices — direct contract_code match. ──
        if ($customerTiers !== []) {
            $pastDueCustomerInvoices = $controller->getPastDueCustomerInvoices(
                'CustomerInvoice', $currency, $companyId, null, $mainFunctionalCurrency
            );
            foreach ($pastDueCustomerInvoices as $invoice) {
                $balance = (float) ($invoice['net_balance_in_main_currency'] ?? $invoice['net_balance'] ?? 0);
                if ($balance <= 0) {
                    continue;
                }
                $contractCode = (string) ($invoice['contract_code'] ?? '');
                $matchedIndex = $contractCode !== '' ? ($indexByContractCode[$contractCode] ?? null) : null;

                foreach ($customerTiers as $tier) {
                    $weekKey = $this->resolvePastDueTierWeek($tier['days'], $datesWithWeekNumber);
                    if ($weekKey === null) {
                        continue;
                    }
                    $amount = $balance * $tier['percentage'] / 100;

                    if ($matchedIndex !== null) {
                        $applyTier($matchedIndex, 'cash_inflow', $weekKey, $amount);
                    } else {
                        $companyUnallocatedCashIn[$weekKey] = ($companyUnallocatedCashIn[$weekKey] ?? 0) + $amount;
                    }
                }
            }
        }

        // ── Supplier past-due invoices — routed via po_allocations,
        // same link/weighting the Supplier Invoices row already uses. ──
        if ($supplierTiers !== []) {
            $poAllocationLookup = [];
            foreach ($poByContract as $contractId => $allocations) {
                $index = $indexByContractId[(int) $contractId] ?? null;
                if ($index === null) {
                    continue;
                }
                foreach ($allocations as $allocation) {
                    $key = ((string) $allocation->code).'|'.((string) $allocation->po_number);
                    $poAllocationLookup[$key][] = [
                        'index' => $index,
                        'percentage' => (float) ($allocation->allocation_percentage ?? 0) / 100,
                    ];
                }
            }

            $pastDueSupplierInvoices = $controller->getPastDueCustomerInvoices(
                'SupplierInvoice', $currency, $companyId, null, $mainFunctionalCurrency
            );
            foreach ($pastDueSupplierInvoices as $invoice) {
                $balance = (float) ($invoice['net_balance_in_main_currency'] ?? $invoice['net_balance'] ?? 0);
                if ($balance <= 0) {
                    continue;
                }
                $key = ((string) ($invoice['contract_code'] ?? '')).'|'.((string) ($invoice['purchases_order_number'] ?? ''));
                $matches = $poAllocationLookup[$key] ?? [];

                foreach ($supplierTiers as $tier) {
                    $weekKey = $this->resolvePastDueTierWeek($tier['days'], $datesWithWeekNumber);
                    if ($weekKey === null) {
                        continue;
                    }
                    $amount = $balance * $tier['percentage'] / 100;

                    if ($matches !== []) {
                        foreach ($matches as $match) {
                            $applyTier($match['index'], 'cash_outflow', $weekKey, $amount * $match['percentage']);
                        }
                    } else {
                        $companyUnallocatedCashOut[$weekKey] = ($companyUnallocatedCashOut[$weekKey] ?? 0) + $amount;
                    }
                }
            }
        }
    }

    /**
     * "Today + N days" resolved to this report's week-key grouping — null if
     * that date falls outside the report's own date range (same convention as
     * every other date-bucketed row here; the amount simply doesn't appear
     * rather than erroring).
     */
    private function resolvePastDueTierWeek(int $days, array $datesWithWeekNumber): ?string
    {
        $date = Carbon::now()->addDays($days)->format('Y-m-d');

        return $datesWithWeekNumber[$date] ?? null;
    }

    private function resolveContractIds(Company $company, Request $request): array
    {
        $currencies = $this->resolveCurrencies($company, $request);
        $contractIds = $request->input('contract_ids', []);
        if (! is_array($contractIds)) {
            $contractIds = [];
        }
        $contractIds = array_values(array_unique(array_filter(array_map('intval', $contractIds))));

        $baseQuery = Contract::query()
            ->where('company_id', $company->id)
            ->where('model_type', Contract::FOR_CUSTOMER)
            ->whereIn('status', [Contract::RUNNING, Contract::RUNNING_AND_AGAINST])
            ->whereIn('currency', $currencies);

        if (($year = $this->resolveYear($request)) !== null) {
            // Keep contracts still running into that year; open-ended contracts
            // (end_date is a nullable DATE column) never expire so they always qualify.
            $baseQuery->where(function ($query) use ($year) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $year . '-01-01');
            });
        }

        if ($contractIds === []) {
            return $baseQuery->orderBy('name')->pluck('id')->all();
        }

        return $baseQuery
            ->whereIn('id', $contractIds)
            ->orderBy('name')
            ->pluck('id')
            ->all();
    }

    private function resolveYear(Request $request): ?int
    {
        $year = $request->input('year');
        if ($year === null || $year === '' || ! is_numeric($year)) {
            return null;
        }

        return (int) $year;
    }

    /**
     * @return list<string>
     */
    private function resolveCurrencies(Company $company, Request $request): array
    {
        $main = strtoupper(trim((string) $company->getMainFunctionalCurrency()));
        $raw = $request->input('currencies', null);
        if (! is_array($raw) || $raw === []) {
            $legacy = $request->input('currency');
            $raw = is_string($legacy) && trim($legacy) !== '' ? [$legacy] : [$main];
        }

        $out = [];
        foreach ($raw as $code) {
            $normalized = strtoupper(trim((string) $code));
            if ($normalized !== '' && ! in_array($normalized, $out, true)) {
                $out[] = $normalized;
            }
        }

        return $out !== [] ? $out : [$main];
    }

    private function buildReport(Company $company, Request $request, array $contractIds): array
    {
        $controller = app(CashFlowReportController::class);
        $subRequest = $this->buildBaseCashFlowRequest($company, $request);

        ForeignExchangeRate::beginRequestMemo();
        try {
            $sharedTimeline = $controller->buildSharedTimelineContext($company, $subRequest);
            if ($sharedTimeline instanceof RedirectResponse) {
                throw new \RuntimeException(__('Company cash flow could not be built. Ensure the date range includes today, matching the main cash flow report rules.'));
            }

            $companyPayload = $this->loadCompanyReport($controller, $company, $request, $sharedTimeline);
            $weeks = $companyPayload['weeks'];
            $dates = $companyPayload['dates'];
            $currencyName = implode(', ', $this->resolveCurrencies($company, $request));
            $reportInterval = (string) $companyPayload['reportInterval'];
            $companyResult = $companyPayload['result'];
            $banksSection = $this->extractBanksSection($companyResult);

            $contracts = Contract::query()->where('company_id', $company->id)->whereIn('id', $contractIds)->get();
            $poByContract = $this->preloadPoAllocations($contractIds);
            $contractsSection = app(ContractCashFlowBatchBuilder::class)->build($company, $request, $contracts, $poByContract, $sharedTimeline, $controller);

            $companyUnallocatedCashOut = $this->computeUnallocatedCashOut($companyResult, $contractsSection, $weeks);
            $companyUnallocatedCashIn = $this->computeUnallocatedCashIn($companyResult, $contractsSection, $weeks);

            // ── Past-due collection/payment plan — a fixed up-to-4-tier
            // repeater per side ("X% collected/paid within N days"), applied
            // per invoice, then routed exactly like every other row: to the
            // invoice's own contract if it's one of the included ones,
            // otherwise into the unallocated rows above. Past-due invoices
            // otherwise contribute nothing to any weekly figure at all — this
            // tier plan is what turns that dead, informational-only amount
            // into an actual forecasted week. Entirely opt-in: empty tiers
            // ⇒ zero effect, same as leaving it unset.
            $customerTiers = $this->normalizeTiers($request, 'customer_past_due_percentages', 'customer_past_due_days');
            $supplierTiers = $this->normalizeTiers($request, 'supplier_past_due_percentages', 'supplier_past_due_days');
            if ($customerTiers !== [] || $supplierTiers !== []) {
                $this->applyPastDueTierMovements(
                    $contractsSection,
                    $companyUnallocatedCashIn,
                    $companyUnallocatedCashOut,
                    $controller,
                    $poByContract,
                    // The past-due lookup runs company-wide across every
                    // currency, converted — so it is driven by the main
                    // functional currency, NOT $currencyName (which on this
                    // report is a comma-joined list like "EGP, USD" and would
                    // match no invoice at all).
                    (string) $sharedTimeline['mainFunctionalCurrency'],
                    (int) $company->id,
                    (string) $sharedTimeline['mainFunctionalCurrency'],
                    (array) $sharedTimeline['datesWithWeekNumber'],
                    $customerTiers,
                    $supplierTiers,
                );
            }

            // ⚠️ Bug fix: both unallocated rows displayed correctly on their
            // own, but neither was actually folded into the grand totals —
            // Total Cash Inflow/Outflow, Net Cash and Accumulated Net Cash
            // were all silently computed without them. Summed here, after the
            // tier injection above, so the contract blocks' (possibly
            // now-larger) figures and the boosted unallocated rows both flow
            // through correctly.
            $sumInflow = [];
            $sumOutflow = [];
            foreach ($contractsSection as $row) {
                $sumInflow = $this->sumByWeek($sumInflow, $row['cash_inflow']);
                $sumOutflow = $this->sumByWeek($sumOutflow, $row['cash_outflow']);
            }
            $sumInflow = $this->sumByWeek($sumInflow, $companyUnallocatedCashIn);
            $sumOutflow = $this->sumByWeek($sumOutflow, $companyUnallocatedCashOut);

            $cashAndBanks = $banksSection[self::CASH_AND_BANKS_BALANCE_KEY]['total'] ?? [];
            $sumNet = $this->netCashWithBanks($sumInflow, $cashAndBanks, $sumOutflow, $weeks);
            $grandTotal = [
                'cash_and_banks' => $cashAndBanks,
                'cash_inflow' => $sumInflow,
                'cash_outflow' => $sumOutflow,
                'net_cash' => $sumNet,
                'accumulated_net' => $this->accumulateNetOverWeeks($sumNet, $weeks),
            ];

            return [
                'weeks' => $weeks,
                'dates' => $dates,
                'reportInterval' => $reportInterval,
                'banksSection' => $banksSection,
                'contractsSection' => $contractsSection,
                'companyUnallocatedCashOut' => $companyUnallocatedCashOut,
                'companyUnallocatedCashIn' => $companyUnallocatedCashIn,
                'grandTotal' => $grandTotal,
                'currencyName' => $currencyName,
                // عملة الفلتر تختار العقود فقط، أما كل الأرقام المعروضة فمحوّلة للعملة الوظيفية
                'displayCurrency' => (string) $sharedTimeline['mainFunctionalCurrency'],
                'title' => __('Consolidated Cash Flow Report').' [ '.$reportInterval.' ]',
            ];
        } finally {
            ForeignExchangeRate::endRequestMemo();
        }
    }

    private function loadCompanyReport(CashFlowReportController $controller, Company $company, Request $request, array $sharedTimeline): array
    {
        $subRequest = $this->buildBaseCashFlowRequest($company, $request);
        $currencies = $this->resolveCurrencies($company, $request);
        $currency = $currencies[0];
        $companyId = (int) $company->id;
        $cashflowReportId = 0;
        $isContract = false;

        $weeks = $sharedTimeline['weeks'];
        $dates = $sharedTimeline['dates'];
        $periodStart = (string) $sharedTimeline['startDate'];
        $periodEnd = (string) $sharedTimeline['endDate'];
        $foreignExchangeRates = $sharedTimeline['foreignExchangeRates'];
        $mainFunctionalCurrency = $sharedTimeline['mainFunctionalCurrency'];
        $formStartDate = $sharedTimeline['formStartDate'];
        $formEndDate = $sharedTimeline['formEndDate'];
        $datesWithWeekNumber = $sharedTimeline['datesWithWeekNumber'];

        $result = $this->initCompanyCashFlowResult();
        CustomerInvoice::getCashAndBankBalanceAtDate($result, $foreignExchangeRates, $mainFunctionalCurrency, $periodStart, array_key_first($weeks), $companyId);
        LoanSchedule::getLoanInstallmentsAtDates($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $datesWithWeekNumber, $periodEnd);
        CashExpense::getProjectionOtherCashOut($result, $company, $cashflowReportId, $isContract);
        CustomerInvoice::getProjectionOtherCashIn($result, $company, $cashflowReportId, $isContract);
        CustomerInvoice::getForecastedProjectCollection($result, $periodStart, $periodEnd, $currencies, $companyId, $datesWithWeekNumber, null, $foreignExchangeRates, $mainFunctionalCurrency);
        SupplierInvoice::getForecastedProjectCollection($result, $periodStart, $periodEnd, $currencies, $companyId, $datesWithWeekNumber, null, $foreignExchangeRates, $mainFunctionalCurrency);
        CustomerInvoice::getCustomerInvoicesUnderCollectionAtDatesForContracts($result, $companyId, null, $datesWithWeekNumber, $periodEnd);
        SupplierInvoice::getSupplierInvoicesUnderCollectionAtDates($result, $companyId, $datesWithWeekNumber, $periodStart, $periodEnd);

        CashFlowCompanyPeriodBatchLoader::apply($result, $foreignExchangeRates, $mainFunctionalCurrency, $companyId, $periodStart, $periodEnd, $dates);

        $pastDueCustomerInvoices = $controller->getPastDueCustomerInvoices('CustomerInvoice', $currency, $companyId, null);
        $pastDueSupplierInvoices = $controller->getPastDueCustomerInvoices('SupplierInvoice', $currency, $companyId, null);
        $pastDueInstallments = $controller->getPastDueLoanSchedules($currency, $companyId);

        $customerDueInvoices = json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('weekly_cashflow_custom_due_invoices.company_id', $companyId)->where('invoice_type', 'CustomerInvoice')->where('cashflow_report_id', $cashflowReportId)->where('is_contract', false)->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()), true);
        $supplierDueInvoices = json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('weekly_cashflow_custom_due_invoices.company_id', $companyId)->where('invoice_type', 'SupplierInvoice')->where('cashflow_report_id', $cashflowReportId)->where('is_contract', false)->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()), true);
        $pastDueLoanInstallments = json_decode(json_encode(DB::table('weekly_cashflow_custom_past_due_schedules')->where('company_id', $companyId)->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()), true);

        $controller->finalizeContractCashFlowTotals(
            $result,
            $company,
            $currency,
            null,
            $datesWithWeekNumber,
            $weeks,
            $cashflowReportId,
            $isContract,
            null,
            $formStartDate,
            $formEndDate,
            [],
            $customerDueInvoices,
            $supplierDueInvoices,
            $pastDueLoanInstallments,
            $foreignExchangeRates,
            $mainFunctionalCurrency,
        );

        // Same fixed row order the Company Cash Flow report already applies —
        // without it the suppliers block came out in whatever order the
        // loaders happened to populate it.
        $orderByKeys = [
            'Cash Payments',
            'Outgoing Transfers',
            'Paid Payable Cheques',
            'Under Payment Payable Cheques',
            'Suppliers Invoices',
            'Suppliers Past Due Invoices',
            'Loan Past Due Installments',
            'Forecasted Suppliers Contract Payments',
        ];

        $result['suppliers'] = collect($result['suppliers'])->sortBy(function ($value, $key) use ($orderByKeys) {
            return array_search($key, $orderByKeys);
        })->toArray();

        return [
            'result' => $result,
            'dates' => $dates,
            'contractCode' => null,
            'pastDueCustomerInvoices' => [$currency => $pastDueCustomerInvoices],
            'currencyName' => $currency,
            'reportInterval' => $sharedTimeline['reportInterval'],
            'weeks' => $weeks,
            'pastDueSupplierInvoices' => $pastDueSupplierInvoices,
            'pastDueInstallments' => $pastDueInstallments,
        ];
    }

    private function initCompanyCashFlowResult(): array
    {
        return [
            'customers' => [
                'Cash & Banks Balance' => [],
                'Checks Collected' => [],
                'Incoming Transfers' => [],
                'Bank Deposits' => [],
                'Cash Collections' => [],
                'Time Of Deposits' => [],
                'Cheques Under Collection' => [],
                'Cheques In Safe' => [],
                'Cancelled LGs Cash Cover' => [],
                'Customers Invoices' => [],
                'Customers Past Due Invoices' => [],
                'Forecasted Project Collection' => [],
                'Projected Other Cash In Items' => [],
                __('Total Cash Inflow') => [],
            ],
            'suppliers' => [],
            'cash_expenses' => [],
        ];
    }

    private const CASH_AND_BANKS_BALANCE_KEY = 'Cash & Banks Balance';

    private function extractBanksSection(array $result): array
    {
        $block = $result['customers'][self::CASH_AND_BANKS_BALANCE_KEY] ?? [];
        $totals = is_array($block) && isset($block['total']) && is_array($block['total']) ? $block['total'] : [];
        return [self::CASH_AND_BANKS_BALANCE_KEY => ['total' => $totals]];
    }

    /**
     * "Total Cash Inflow" minus just the "Forecasted Project Collection" row.
     * Still-open invoices and past-due amounts stay in the comparison (as
     * long as they're not tied to a customer contract); only the
     * forward-looking contract forecast itself is excluded.
     *
     * @return array<string, float|int>
     */
    public static function totalCashInflowExcludingForecast(array $customersResult): array
    {
        $inflowKey = __('Total Cash Inflow');
        $forecastKey = 'Forecasted Project Collection';

        $totalInflow = $customersResult[$inflowKey]['total'] ?? [];
        $forecast = $customersResult[$forecastKey]['total'] ?? [];
        $totalInflow = is_array($totalInflow) ? $totalInflow : [];
        $forecast = is_array($forecast) ? $forecast : [];

        $weekKeys = array_unique(array_merge(array_keys($totalInflow), array_keys($forecast)));
        $result = [];
        foreach ($weekKeys as $weekKey) {
            $result[$weekKey] = (float) ($totalInflow[$weekKey] ?? 0) - (float) ($forecast[$weekKey] ?? 0);
        }

        return $result;
    }

    /**
     * Company-level cash INFLOW not covered by the selected contracts —
     * unlike computeUnallocatedCashOut(), this deliberately does NOT compare
     * the raw "Total Cash Inflow" figures as-is. Those include "Forecasted
     * Project Collection" (a forward-looking projection), which inflates this
     * figure: a contract's forecasted-but-not-yet-collected amount is baked
     * into the company-wide total with nothing on the per-contract side
     * reliably cancelling it back out. Only that forecast row is excluded —
     * see totalCashInflowExcludingForecast().
     *
     * @param  list<array{cash_inflow_excl_forecast: array<string, float|int>}>  $contractsSection
     * @param  array<string, string|int>  $weeks
     * @return array<string, float|int>
     */
    private function computeUnallocatedCashIn(array $companyResult, array $contractsSection, array $weeks): array
    {
        $companyInflow = self::totalCashInflowExcludingForecast($companyResult['customers'] ?? []);
        $contractsInflow = [];

        foreach ($contractsSection as $block) {
            foreach (($block['cash_inflow_excl_forecast'] ?? []) as $weekKey => $amount) {
                $contractsInflow[$weekKey] = ($contractsInflow[$weekKey] ?? 0.0) + (float) $amount;
            }
        }

        $unallocated = [];
        foreach (array_keys($weeks) as $weekKey) {
            $diff = (float) ($companyInflow[$weekKey] ?? 0) - (float) ($contractsInflow[$weekKey] ?? 0);
            $unallocated[$weekKey] = max(0.0, $diff);
        }

        return $unallocated;
    }

    private function computeUnallocatedCashOut(array $companyResult, array $contractsSection, array $weeks): array
    {
        $companyOutflow = $this->extractTotalCashOutflow($companyResult);
        $contractsOutflow = [];
        foreach ($contractsSection as $block) {
            foreach ($block['cash_outflow'] as $weekKey => $amount) {
                $contractsOutflow[$weekKey] = ($contractsOutflow[$weekKey] ?? 0.0) + (float) $amount;
            }
        }
        $unallocated = [];
        foreach (array_keys($weeks) as $weekKey) {
            $unallocated[$weekKey] = max(0.0, (float) ($companyOutflow[$weekKey] ?? 0) - (float) ($contractsOutflow[$weekKey] ?? 0));
        }
        return $unallocated;
    }

    private function extractTotalCashOutflow(array $result): array
    {
        $outflowKey = __('Total Cash Outflow');
        $totals = $result['cash_expenses'][$outflowKey]['total'] ?? [];
        return is_array($totals) ? $totals : [];
    }

    private function preloadPoAllocations(array $contractIds): Collection
    {
        if ($contractIds === []) {
            return collect();
        }

        $grouped = PoAllocation::withSupplierPurchaseOrderDetails()
            ->whereIn('po_allocations.contract_id', $contractIds)
            ->get()
            // ⚠️ Bug fix: was ->groupBy('contract_id') — before
            // PoAllocation::scopeWithSupplierPurchaseOrderDetails()
            // existed, the hydrated `contract_id` attribute actually
            // resolved to purchase_orders.contract_id (the SUPPLIER
            // contract) due to a raw "SELECT *" column collision, not
            // po_allocations' own (Customer) contract_id — so this was
            // silently grouping allocations under the wrong contract.
            ->groupBy('customer_contract_id');

        return collect($grouped->all())->map(static fn (Collection $items): Collection => collect($items->all()));
    }

    private function buildBaseCashFlowRequest(Company $company, Request $request): Request
    {
        $start = Carbon::make($request->input('start_date'))->format('Y-m-d');
        $end = Carbon::make($request->input('end_date'))->format('Y-m-d');
        $interval = $request->input('report_interval', 'monthly');
        $currencies = $this->resolveCurrencies($company, $request);

        // The company-level section (Cash & Banks Balance, LG fees, etc.)
        // always renders in the main functional currency — every number in
        // this report ends up converted to it anyway (see 'displayCurrency').
        // The currency filter's only job is picking which contracts are
        // eligible; it was never meant to steer this sub-report, and doesn't
        // cleanly map to one value now that several currencies can be picked
        // at once. 'currencies' is still forwarded for the contract-scoped
        // forecast rows, which do read the full list.
        return Request::create('/', 'GET', [
            'start_date' => $start,
            'end_date' => $end,
            'report_interval' => $interval,
            'currency' => $company->getMainFunctionalCurrency(),
            'currencies' => $currencies,
        ]);
    }

    private function sumByWeek(array $a, array $b): array
    {
        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = (float) ($a[$k] ?? 0) + (float) ($b[$k] ?? 0);
        }
        return $out;
    }

    /**
     * Section C Net Cash: Total Cash Inflow + Cash & Banks Balance − Total Cash Outflow.
     *
     * @param  array<string, float|int>  $inflow
     * @param  array<string, float|int>  $cashAndBanks
     * @param  array<string, float|int>  $outflow
     * @param  array<string, string|int>  $weeks
     * @return array<string, float|int>
     */
    private function netCashWithBanks(array $inflow, array $cashAndBanks, array $outflow, array $weeks): array
    {
        $out = [];
        foreach (array_keys($weeks) as $wk) {
            $out[$wk] = (float) ($inflow[$wk] ?? 0)
                + (float) ($cashAndBanks[$wk] ?? 0)
                - (float) ($outflow[$wk] ?? 0);
        }

        return $out;
    }

    private function accumulateNetOverWeeks(array $netByWeek, array $weeks): array
    {
        $acc = [];
        $running = 0.0;
        foreach (array_keys($weeks) as $wk) {
            $running += (float) ($netByWeek[$wk] ?? 0);
            $acc[$wk] = $running;
        }
        return $acc;
    }
}
