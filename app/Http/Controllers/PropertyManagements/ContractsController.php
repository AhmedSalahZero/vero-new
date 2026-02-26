<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenewContractRequest;
use App\Http\Requests\StorePropertyContractRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Contract;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\Tenant;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractsController extends Controller
{
    protected ContractService $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }
	
	
	public function index(Company $company, Request $request, Property $property)
	{
		$contracts = $this->contractService->getContractsForProperty($property);
		
		return view('property_managements.contracts.index', [
			'company' => $company,
			'contracts' => $contracts,
			'contractsData' => $contracts,
			'property'=>$property,
			'title' => __('Contracts for Properties') . ' - ' . $property->name,
		]);
	}
	

    /**
     * Show the form for creating a new contract
     */
    public function create(Company $company,Request $request,Property $property): View
    {
        return view('property_managements.contracts.form', [
            'property' => $property,
			'title' => __('Contract'),
        ]);
    }

    /**
     * Get old data for create/edit form
     */
    public function getOldData(Request $request,Company $company, Property $property): JsonResponse
    {
		
        $contractId = $request->get('contract_id');
        $contract = $contractId ? Contract::find($contractId) : null;
		$currenciesFormattedForVueSelect = [];
		$currencies = getCurrencies();
		foreach($currencies as $currency){
			$currenciesFormattedForVueSelect[] = ['id' => $currency, 'title' => __($currency)];
		}
        $model = $this->contractService->getFormattedContract($contract, $company);
        $model['property_id'] = $property->id;

        $selects = $this->getSelects($company);

        $submitUrl = $contractId
            ? route('property-managements.properties.contracts.update', [
                'company' => $company->id,
                'property' => $property->id,
                'contract' => $contractId,
            ])
            : route('property-managements.properties.contracts.store', ['company' => $company->id, 'property' => $property->id]);

        return response()->json([
            'model' => $model,
            'selects' => $selects,
            'submitUrl' => $submitUrl,
			'currencies' => $currenciesFormattedForVueSelect,
        ]);
    }

    /**
     * Store a newly created contract
     */
    public function store(StorePropertyContractRequest $request, Company $company, Property $property): JsonResponse
    {
      
             $this->contractService->store($request, $company);
            return response()->json([
                'success' => true,
                'message' => __('Contract created successfully'),
                'redirect' => route('property-managements.properties.contracts.index', ['company' => $company->id, 'property' => $property->id]),
            ]);
      
    }

    /**
     * Show the form for editing a contract
     */
    public function edit(Company $company, Property $property, Contract $contract): View
    {
        return view('property_managements.contracts.form', [
            'property' => $property,
            'contract' => $contract,
			'title' => __('Contract'),
        ]);
    }

    /**
     * Update the specified contract
     */
    public function update(StorePropertyContractRequest $request, Company $company, Property $property, Contract $contract): JsonResponse
    {
        try {
            $this->contractService->update($contract, $request);

            return response()->json([
                'success' => true,
                'message' => __('Contract updated successfully'),
                'redirect' => route('property-managements.properties.contracts.index', ['company' => $company->id, 'property' => $property->id]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while updating the contract') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified contract
     */
    public function destroy(Company $company, Property $property, Contract $contract): JsonResponse
    {
      
            $this->contractService->delete($contract);
            return response()->json([
                'success' => true,
                'message' => __('Contract deleted successfully'),
            ]);
        
    }

    /**
     * Mark contract as finished
     */
    public function markAsFinished(Request $request, Company $company, Property $property, Contract $contract): JsonResponse
    {
        $request->validate([
            'finished_date' => 'required',
        ]);

        try {
            // Format date from month picker
            $finishedDate = $this->formatDateFromMonthPicker($request->get('finished_date'));

            $this->contractService->markAsFinished($contract, $finishedDate);

            return response()->json([
                'success' => true,
                'message' => __('Contract marked as finished successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show renew contract form
     */
    public function showRenewForm(Company $company, Property $property, Contract $contract): JsonResponse
    {
        $data = [
            'contract_start_date' => [
                'month' => (int) date('n') - 1,
                'year' => (int) date('Y'),
            ],
            'contract_end_date' => [
                'month' => (int) date('n') - 1,
                'year' => (int) date('Y') + 1,
            ],
            'annually_increase_rate' => $contract->annually_increase_rate,
            'collection_policy' => $contract->collection_policy ?? $contract->collection_interval,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Renew a contract
     */
    public function renew(RenewContractRequest $request, Company $company, 	Property $property, Contract $contract): JsonResponse
    {
        try {
            $newContract = $this->contractService->renew($contract, $request);

            return response()->json([
                'success' => true,
                'message' => __('Contract renewed successfully'),
                'contract_id' => $newContract->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while renewing the contract') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get select options for forms
     */
    private function getSelects(Company $company): array
    {
        return [
            'tenantTypes' => [
                ['value' => 'individual', 'label' => __('Individual'),'tenants'=>Tenant::where('company_id',$company->id)->where('nature','individual')->get()->map(function($tenant){
                    return ['value' => $tenant->id, 'label' => $tenant->getName()];
                })->toArray()],
                ['value' => 'corporate', 'label' => __('Corporate'),
				'tenants'=>Tenant::where('company_id',$company->id)->where('nature','corporate')->get()->map(function($tenant){
					return ['value' => $tenant->id, 'label' => $tenant->getName()];
				})->toArray()],
			]
           ,
            'collectionIntervals' => [
                ['value' => 'monthly', 'label' => __('Monthly')],
                ['value' => 'quarterly', 'label' => __('Quarterly')],
                ['value' => 'semi-annually', 'label' => __('Semi-Annually')],
                ['value' => 'annually', 'label' => __('Annually')],
            ],
            'installmentTypes' => [
                ['value' => 'regular', 'label' => __('Regular Installment')],
                ['value' => 'variable', 'label' => __('Variable Installment')],
            ],
        ];
    }

    /**
     * Convert month picker format to date string
     */
    private function formatDateFromMonthPicker($dateData): string
    {
        if (is_string($dateData)) {
            return $dateData;
        }

        if (is_array($dateData) && isset($dateData['month']) && isset($dateData['year'])) {
            $month = str_pad($dateData['month'] + 1, 2, '0', STR_PAD_LEFT);
            return $dateData['year'] . '-' . $month . '-01';
        }

        return now()->format('Y-m-d');
    }
}
