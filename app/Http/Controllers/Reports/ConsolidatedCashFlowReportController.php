<?php

namespace App\Http\Controllers\Reports;

use App\Models\Company;
use App\Models\Contract;
use App\Services\Reports\ConsolidatedCashFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsolidatedCashFlowReportController
{
    public function index(Company $company): View
    {
        $activeContracts = Contract::query()
            ->where('company_id', $company->id)
            ->where('model_type', Contract::FOR_CUSTOMER)
            ->whereIn('status', [Contract::RUNNING, Contract::RUNNING_AND_AGAINST])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'currency', 'end_date']);

        return view('reports.consolidated_cash_flow.index', [
            'company' => $company,
            'activeContracts' => $activeContracts,
            'years' => $this->buildYearOptions($activeContracts),
        ]);
    }

    public function result(Company $company, Request $request, ConsolidatedCashFlowService $service): View|RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'report_interval' => ['required', 'in:daily,weekly,monthly'],
            'contract_ids' => ['nullable', 'array'],
            'contract_ids.*' => ['integer', Rule::exists('contracts', 'id')->where('company_id', $company->id)],
            'currencies' => ['nullable', 'array'],
            'currencies.*' => ['string', 'max:32'],
            'currency' => ['nullable', 'string', 'max:32'], // legacy single-currency bookmarks
            'year' => ['nullable', 'integer', 'min:1900', 'max:2999'],
        ]);

        $request->merge($validated);

        try {
            $payload = $service->build($company, $request);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reports.consolidated-cash-flow.index', ['company' => $company->id])
                ->with('fail', $e->getMessage());
        }

        return view('reports.consolidated_cash_flow.result', array_merge($payload, [
            'company' => $company,
        ]));
    }

    /**
     * Selectable years for the contract filter: a continuous range covering the
     * contract end dates on screen plus the current year, capped so a single
     * far-future end date cannot blow up the list.
     *
     * @param  \Illuminate\Support\Collection<int, Contract>  $contracts
     * @return list<int>
     */
    private function buildYearOptions(Collection $contracts): array
    {
        $currentYear = (int) now()->format('Y');

        $endYears = $contracts
            ->map(static fn (Contract $contract): ?int => $contract->getEndYear())
            ->filter()
            ->all();

        $min = $endYears === [] ? $currentYear : min(min($endYears), $currentYear);
        $max = $endYears === [] ? $currentYear : max(max($endYears), $currentYear);

        return range(max($min, $currentYear - 20), min($max, $currentYear + 20));
    }
}
