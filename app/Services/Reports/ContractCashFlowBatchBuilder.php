<?php

namespace App\Services\Reports;

use App\Http\Controllers\CashFlowReportController;
use App\Models\CashExpense;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomerInvoice;
use App\Models\ForeignExchangeRate;
use App\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ContractCashFlowBatchBuilder
{
    public function build(
        Company $company,
        Request $request,
        Collection $contracts,
        Collection $poByContract,
        array $sharedTimeline,
        CashFlowReportController $controller,
    ): array {
        if ($contracts->isEmpty()) {
            return [];
        }

        ForeignExchangeRate::beginRequestMemo();
        try {
            return $this->buildInternal($company, $request, $contracts, $poByContract, $sharedTimeline, $controller);
        } finally {
            ForeignExchangeRate::endRequestMemo();
        }
    }

    private function buildInternal(
        Company $company,
        Request $request,
        Collection $contracts,
        Collection $poByContract,
        array $sharedTimeline,
        CashFlowReportController $controller,
    ): array {
        $mainFunctionalCurrency = (string) $sharedTimeline['mainFunctionalCurrency'];
        $foreignExchangeRates = $sharedTimeline['foreignExchangeRates'];
        $datesWithWeekNumber = $sharedTimeline['datesWithWeekNumber'];
        $weeks = $sharedTimeline['weeks'];
        $dates = $sharedTimeline['dates'];
        $formStartDate = (string) $sharedTimeline['formStartDate'];
        $formEndDate = (string) $sharedTimeline['formEndDate'];
        $periodStart = (string) $sharedTimeline['startDate'];
        $periodEnd = (string) $sharedTimeline['endDate'];

        $resultsByContractCode = [];
        $contractByCode = [];
        $contractIds = [];

        foreach ($contracts as $contract) {
            $code = (string) ($contract->getCode() ?? '');
            if ($code === '') {
                continue;
            }
            $contractIds[] = (int) $contract->id;
            $contractByCode[$code] = $contract;
            $resultsByContractCode[$code] = $this->initContractResult();
        }

        $contractCodes = array_keys($resultsByContractCode);

        foreach ($contracts as $contract) {
            $code = (string) ($contract->getCode() ?? '');
            if ($code === '' || ! isset($resultsByContractCode[$code])) {
                continue;
            }
            $contractId = (int) $contract->id;
            $contractCode = $code;
            $contractCurrency = (string) $contract->getCurrency();
            $result = &$resultsByContractCode[$code];
            $pastDueSupplierInvoicesForContracts = collect([]);
            $poAllocations = $poByContract->get($contractId, collect());

            CashExpense::getProjectionOtherCashOut($result, $company, 0, true);
            CustomerInvoice::getProjectionOtherCashIn($result, $company, 0, true);
            CustomerInvoice::getForecastedProjectCollection($result, $formStartDate, $formEndDate, $contractCurrency, $company->id, $datesWithWeekNumber, $contractId, $foreignExchangeRates, $mainFunctionalCurrency);
            SupplierInvoice::getForecastedProjectCollection($result, $formStartDate, $formEndDate, $contractCurrency, $company->id, $datesWithWeekNumber, $contractId, $foreignExchangeRates, $mainFunctionalCurrency);
            CustomerInvoice::getCustomerInvoicesUnderCollectionAtDatesForContracts($result, $company->id, $contractCode, $datesWithWeekNumber, $formEndDate);
            SupplierInvoice::getSupplierInvoicesForPoUnderCollectionAtDates($result, $company->id, $datesWithWeekNumber, $formStartDate, $formEndDate, $poAllocations, $pastDueSupplierInvoicesForContracts);
        }

        CashFlowPeriodBatchLoader::applyContractPeriodMovements(
            $resultsByContractCode,
            $foreignExchangeRates,
            $mainFunctionalCurrency,
            (int) $company->id,
            $periodStart,
            $periodEnd,
            $contractCodes,
            $dates,
            $contractIds,
        );

        foreach ($contractByCode as $code => $contract) {
            $this->applyWeekSupplement($resultsByContractCode[$code], $company, $contract, $foreignExchangeRates, $mainFunctionalCurrency, $weeks, $dates);
        }

        $summaries = [];
        $inflowKey = __('Total Cash Inflow');
        $outflowKey = __('Total Cash Outflow');
        $netKey = __('Net Cash (+/-)');

        foreach ($contractByCode as $code => $contract) {
            $result = $resultsByContractCode[$code];
            $customerDueInvoices = json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')
                ->where('weekly_cashflow_custom_due_invoices.company_id', $company->id)
                ->where('invoice_type', 'CustomerInvoice')
                ->where('cashflow_report_id', 0)
                ->where('is_contract', true)
                ->join('customer_invoices', 'customer_invoices.id', '=', 'weekly_cashflow_custom_due_invoices.invoice_id')
                ->where('customer_invoices.contract_code', $code)
                ->groupBy('week_start_date')
                ->selectRaw('week_start_date,sum(amount) as amount')
                ->get()), true);
            $supplierDueInvoices = json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')
                ->where('weekly_cashflow_custom_due_invoices.company_id', $company->id)
                ->where('invoice_type', 'SupplierInvoice')
                ->where('cashflow_report_id', 0)
                ->where('is_contract', true)
                ->join('supplier_invoices', 'supplier_invoices.id', '=', 'weekly_cashflow_custom_due_invoices.invoice_id')
                ->where('supplier_invoices.contract_code', $code)
                ->groupBy('week_start_date')
                ->selectRaw('week_start_date,sum(amount) as amount')
                ->get()), true);
            $pastDueLoanInstallments = json_decode(json_encode(DB::table('weekly_cashflow_custom_past_due_schedules')
                ->where('company_id', $company->id)
                ->groupBy('week_start_date')
                ->selectRaw('week_start_date,sum(amount) as amount')
                ->get()), true);

            $controller->finalizeContractCashFlowTotals(
                $result,
                $company,
                (string) $contract->getCurrency(),
                $code,
                $datesWithWeekNumber,
                $weeks,
                0,
                true,
                (int) $contract->id,
                $formStartDate,
                $formEndDate,
                [],
                $customerDueInvoices,
                $supplierDueInvoices,
                $pastDueLoanInstallments,
                $foreignExchangeRates,
                $mainFunctionalCurrency,
            );

            $summaries[] = [
                'contract_id' => (int) $contract->id,
                'contract_name' => (string) $contract->getName(),
                'contract_code' => $code,
                'cash_inflow' => is_array($result['customers'][$inflowKey]['total'] ?? null) ? $result['customers'][$inflowKey]['total'] : [],
                'cash_outflow' => is_array($result['cash_expenses'][$outflowKey]['total'] ?? null) ? $result['cash_expenses'][$outflowKey]['total'] : [],
                'net_cash' => is_array($result['cash_expenses'][$netKey]['total'] ?? null) ? $result['cash_expenses'][$netKey]['total'] : [],
            ];
        }

        return $summaries;
    }

    private function initContractResult(): array
    {
        return [
            'customers' => [
                'Checks Collected' => [],
                'Incoming Transfers' => [],
                'Bank Deposits' => [],
                'Cash Collections' => [],
                'Cheques Under Collection' => [],
                'Cheques In Safe' => [],
                'Cancelled LGs Cash Cover' => [],
                'Customers Invoices' => [],
                'Customers Past Due Invoices' => [],
                'Forecasted Project Collection' => [],
                'Projected Other Cash In Items' => [],
                __('Total Cash Inflow') => ['total' => []],
            ],
            'suppliers' => [],
            'cash_expenses' => [],
        ];
    }

    private function applyWeekSupplement(
        array &$result,
        Company $company,
        Contract $contract,
        Collection $foreignExchangeRates,
        string $mainFunctionalCurrency,
        array $weeks,
        array $dates,
    ): void {
        CashFlowContractPeriodSupplementBatchLoader::apply(
            $result,
            collect([$contract]),
            $foreignExchangeRates,
            $mainFunctionalCurrency,
            (int) $company->id,
            (string) min(array_column($dates, 'start_date')),
            (string) max(array_column($dates, 'end_date')),
            $dates,
        );
    }
}
