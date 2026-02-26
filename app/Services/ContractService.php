<?php

namespace App\Services;

use App\Models\Company;
use App\Models\PropertyManagement\Contract;
use App\Models\PropertyManagement\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractService
{
    /**
     * Store a new contract
     */
    public function store(Request $request,Company $company): Contract
    {
        return DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->transaction(function () use ($request, $company) {
            // Calculate insurance amount
            $insuranceAmount = $request->get('insurance_months_count') * $request->get('monthly_rent');

            // Create contract
            $contractData = $this->getContractData($request);
            $contractData['insurance_amount'] = $insuranceAmount;
            $contractData['company_id'] = $company->id;
			/**
			 * @var Contract $contract
			 */
            $contract = Contract::create($contractData);
			$contract->reCalculateRentRevenuesAndRentCollections();
            return $contract;
        });
    }

    /**
     * Update an existing contract
     */
    public function update(Contract $contract, Request $request): Contract
    {
        return DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->transaction(function () use ($contract, $request) {
            // Calculate insurance amount
            $insuranceAmount = $request->get('insurance_months_count') * $request->get('monthly_rent');

            // Update contract
            $contractData = $this->getContractData($request);
            $contractData['insurance_amount'] = $insuranceAmount;
          //  $contractData['company_id'] = $contract->company_id;

            $contract->update($contractData);
			$contract->reCalculateRentRevenuesAndRentCollections();
            // Update or create installment
            // if ($request->has('installment')) {
            //     $this->updateInstallment($contract, $request->get('installment'));
            // }

            return $contract->fresh();
        });
    }

    /**
     * Delete a contract
     */
    public function delete(Contract $contract): bool
    {
        return $contract->delete();
    }

    /**
     * Get contracts by status for a property
     */
    // public function getContractsByStatus(int $propertyId, string $status): array
    // {
    //     $contracts = Contract::where('property_id', $propertyId)
    //         ->where('status', $status)
    //         ->with(['installment'])
    //         ->orderBy('contract_start_date', 'desc')
    //         ->get();

    //     return $contracts->map(function ($contract)  {
    //         return $this->formatContract($contract, $contract->company);
    //     })->toArray();
    // }

    /**
     * Get all contracts for a property grouped by status
     */
    // public function getAllContractsByProperty(int $propertyId): array
    // {
    //     // Update expired contracts first
    //     $this->updateExpiredContracts($propertyId);

    //     return [
    //         'running' => $this->getContractsByStatus($propertyId, 'running'),
    //         'finished' => $this->getContractsByStatus($propertyId, 'finished'),
    //         'expired' => $this->getContractsByStatus($propertyId, 'expired'),
    //     ];
    // }
	
	public function getContractsForProperty(Property $property)
    {
        // Update expired contracts first
        $this->updateExpiredContracts($property->id);
        return  $property->contracts->map(function ($contract) {
			$result = $this->formatContract($contract, $contract->company);
			$result['contract_start_date'] =$contract->contract_start_date->format('d-m-Y');
			$result['contract_end_date'] =$contract->contract_end_date->format('d-m-Y');
            return $result ;
        });
    }

    /**
     * Mark contract as finished
     */
    public function markAsFinished(Contract $contract, string $finishedDate): bool
    {
        if ($contract->status !== 'running') {
            throw new \Exception(__('Only running contracts can be marked as finished'));
        }

        return $contract->markAsFinished($finishedDate);
    }

    /**
     * Renew a contract (create new contract from existing one)
     */
    public function renew(Contract $oldContract, Request $request): Contract
    {
        return DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->transaction(function () use ($oldContract, $request) {
            // Mark old contract as finished or expired based on its current status
            if ($oldContract->status === 'running') {
                $oldContract->update(['status' => 'finished', 'finished_date' => now()]);
            } elseif ($oldContract->status === 'expired') {
                // Already expired, no need to update
            }

            // Create new contract with updated data
            $newContractData = [
                'property_id' => $oldContract->property_id,
                'tenant_name' => $oldContract->tenant_name,
                'tenant_type' => $oldContract->tenant_type,
                'monthly_rent' => $oldContract->monthly_rent,
                'contract_start_date' => $this->formatDateFromMonthPicker($request->get('contract_start_date')),
                'contract_end_date' => $this->formatDateFromMonthPicker($request->get('contract_end_date')),
                'collection_interval' => $request->get('collection_policy') ?? $oldContract->collection_interval,
                'insurance_months_count' => $oldContract->insurance_months_count,
                'insurance_amount' => $oldContract->insurance_amount,
                'annually_increase_rate' => $request->get('annually_increase_rate'),
                'collection_policy' => $request->get('collection_policy'),
                'status' => 'running',
				'contract_currency' => $oldContract->contract_currency,
				'collection_currency' => $oldContract->collection_currency,
            ];

            return Contract::create($newContractData);
        });
    }

    /**
     * Update expired contracts
     */
    private function updateExpiredContracts(int $propertyId): void
    {
        Contract::where('property_id', $propertyId)
            ->where('status', 'running')
            ->where('contract_end_date', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get contract data from request
     */
    private function getContractData(Request $request): array
    {
        return [
            'property_id' => $request->get('property_id'),
			'tenant_id' => $request->get('tenant_id'),
            'monthly_rent' => $request->get('monthly_rent'),
			'min_amount' => $request->get('min_amount'),
			'variable_from_tenant_revenues_percentage' => $request->get('variable_from_tenant_revenues_percentage'),
            'contract_start_date' => $this->formatDateFromMonthPicker($request->get('contract_start_date')),
            'contract_end_date' => $this->formatDateFromMonthPicker($request->get('contract_end_date')),
            'collection_interval' => $request->get('collection_interval'),
            'insurance_months_count' => $request->get('insurance_months_count'),
            'annually_increase_rate' => $request->get('annually_increase_rate'),
            'collection_policy' => $request->get('collection_policy'),
			'contract_currency' => $request->get('contract_currency'),
			'collection_currency' => $request->get('collection_currency'),
        ];
    }

    /**
     * Store installment for a contract
     */
    // private function storeInstallment(Contract $contract, array $installmentData): void
    // {
    //     $data = [
    //         'contract_id' => $contract->id,
    //         'installment_type' => $installmentData['installment_type'],
    //     ];

    //     if ($installmentData['installment_type'] === 'regular') {
    //         $data['installment_amount'] = $installmentData['installment_amount'];
    //         $data['start_date'] = $this->formatDateFromMonthPicker($installmentData['start_date']);
    //         $data['end_date'] = $this->formatDateFromMonthPicker($installmentData['end_date']);
    //         $data['number_of_months'] = $installmentData['number_of_months'];

    //         // Optional annual fields
    //         if (!empty($installmentData['annual_start_date'])) {
    //             $data['annual_start_date'] = $this->formatDateFromMonthPicker($installmentData['annual_start_date']);
    //             $data['annual_amount'] = $installmentData['annual_amount'];
    //             $data['annual_count'] = $installmentData['annual_count'];
    //         }
    //     } elseif ($installmentData['installment_type'] === 'variable') {
    //         // Format variable installment details
    //         $details = [];
    //         if (!empty($installmentData['installment_details'])) {
    //             foreach ($installmentData['installment_details'] as $detail) {
    //                 $details[] = [
    //                     'date' => $this->formatDateFromMonthPicker($detail['date']),
    //                     'amount' => $detail['amount'],
    //                 ];
    //             }
    //         }
    //         $data['installment_details'] = $details;
    //     }

    //     ContractInstallment::create($data);
    // }

    /**
     * Update installment for a contract
     */
    // private function updateInstallment(Contract $contract, array $installmentData): void
    // {
    //     // Delete old installments
    //     $contract->installment()->delete();

    //     // Create new installment
    //     $this->storeInstallment($contract, $installmentData);
    // }

    /**
     * Format contract for frontend
     */
    private function formatContract(Contract $contract,Company $company): array
    {
		
        $formatted = [
            'id' => $contract->id,
            'property_id' => $contract->property_id,
            'tenant_id' => $contract->tenant_id,
            'tenant_nature' => $contract->getTenantNature(),
			'tenant_name' => $contract->getTenantName(),
            'monthly_rent' => $contract->monthly_rent,
			'contract_currency' => $contract->contract_currency,
			'collection_currency' => $contract->collection_currency,
			'variable_from_tenant_revenues_percentage' => $contract->getVariableFromTenantRevenuesPercentage(),
			'min_amount' => $contract->getMinAmount(),
            'contract_start_date' => $contract->contract_start_date->format('Y-m-d'),
            'contract_end_date' => $contract->contract_end_date->format('Y-m-d'),
            'collection_interval' => $contract->collection_interval,
            'collection_interval_label' => $contract->getCollectionIntervalLabel(),
            'insurance_months_count' => $contract->insurance_months_count,
            'insurance_amount' => $contract->insurance_amount,
            'status' => $contract->status,
            'status_label' => $contract->getStatusLabel(),
            'status_color' => $contract->getStatusColor(),
            'finished_date' => $contract->finished_date ? $this->formatDateToMonthPicker($contract->finished_date) : null,
            'annually_increase_rate' => $contract->annually_increase_rate,
            'collection_policy' => $contract->collection_policy,
			'company_id' => $company->id,
        ];

        // Add installment data if exists
        // if ($contract->installment->isNotEmpty()) {
        //     $installment = $contract->installment->first();
        //     $formatted['installment'] = [
        //         'id' => $installment->id,
        //         'installment_type' => $installment->installment_type,
        //         'installment_amount' => $installment->installment_amount,
        //         'start_date' => $installment->start_date ? $this->formatDateToMonthPicker($installment->start_date) : null,
        //         'end_date' => $installment->end_date ? $this->formatDateToMonthPicker($installment->end_date) : null,
        //         'number_of_months' => $installment->number_of_months,
        //         'annual_start_date' => $installment->annual_start_date ? $this->formatDateToMonthPicker($installment->annual_start_date) : null,
        //         'annual_amount' => $installment->annual_amount,
        //         'annual_count' => $installment->annual_count,
        //         'installment_details' => $this->formatVariableInstallmentDetails($installment->installment_details),
        //     ];
        // }

        return $formatted;
    }

    /**
     * Format variable installment details for frontend
     */
    // private function formatVariableInstallmentDetails(?array $details): array
    // {
    //     if (empty($details)) {
    //         return [];
    //     }

    //     return array_map(function ($detail) {
    //         return [
    //             'date' => $this->formatDateToMonthPicker($detail['date']),
    //             'amount' => $detail['amount'],
    //         ];
    //     }, $details);
    // }

    /**
     * Convert month picker format {month: 0, year: 2024} to date string
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

    /**
     * Convert date string to month picker format {month: 0, year: 2024}
     */
    private function formatDateToMonthPicker($date): array
    {
        if (is_string($date)) {
            $dateObj = new \DateTime($date);
            return [
                'month' => (int) $dateObj->format('n') - 1,
                'year' => (int) $dateObj->format('Y'),
            ];
        }

        return [
            'month' => (int) date('n') - 1,
            'year' => (int) date('Y'),
        ];
    }

    /**
     * Get formatted contract for edit
     */
    public function getFormattedContract(?Contract $contract, Company $company): array
    {
        if (!$contract) {
            return $this->getDefaultContractData($company);
        }

        return $this->formatContract($contract, $company);
    }

    /**
     * Get default contract data for create form
     */
    private function getDefaultContractData(Company $company): array
    {
        return [
            'id' => null,
            'property_id' => null,
            'tenant_id' => null,
			
            'monthly_rent' => 0,
            'contract_start_date' => [
                'month' => (int) date('n') - 1,
                'year' => (int) date('Y'),
            ],
            'contract_end_date' => [
                'month' => (int) date('n') - 1,
                'year' => (int) date('Y'),
            ],
            'collection_interval' => 'monthly',
			'contract_currency' => 'EGP',
			'collection_currency' => 'EGP',
			'company_id'=>$company->id,
			'variable_from_tenant_revenues_percentage' => 0,
			'min_amount' => 0,
            'insurance_months_count' => 0,
            'insurance_amount' => 0,
            'annually_increase_rate' => null,
            'collection_policy' => null,
            'installment' => [
                'installment_type' => 'regular',
                'installment_amount' => 0,
                'start_date' => [
                    'month' => (int) date('n') - 1,
                    'year' => (int) date('Y'),
                ],
                'end_date' => [
                    'month' => (int) date('n') - 1,
                    'year' => (int) date('Y'),
                ],
                'number_of_months' => 0,
                'annual_start_date' => null,
                'annual_amount' => 0,
                'annual_count' => 0,
                'installment_details' => [],
            ],
        ];
    }
}
