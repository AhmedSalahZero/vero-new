<?php

namespace App\Models\PropertyManagement;

use App\Equations\MonthlyFixedRepeatingAmountEquation;
use App\Helpers\HDate;
use App\Models\Company;
use App\Models\User;
use App\ReadyFunctions\CollectionPolicyService;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCollectionOrPaymentStatement;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory, HasBasicStoreRequest,HasCompany;

    protected $connection = PROPERTY_MANAGEMENT_CONNECTION_NAME;
	protected static function newFactory()
	{
		return ContractFactory::new();
	}
    protected $guarded = ['id'];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'finished_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'annually_increase_rate' => 'decimal:2',
		'rent_revenues' => 'array',
        'rent_collections' => 'array',
		'installments'=>'array',
    ];

    public function getInsuranceAmount(): float
    {
        return $this->insurance_amount?:0;
    }

    /**
     * Get the property that owns the contract
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the installment for the contract
     */
    // public function installment(): HasMany
    // {
    //     return $this->hasMany(ContractInstallment::class);
    // }

    /**
     * Get the creator of the contract
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater of the contract
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if contract is expired based on end date
     */
    public function checkAndUpdateExpiredStatus(): void
    {
        if ($this->status === 'running' && $this->contract_end_date < now()) {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * Mark contract as finished
     */
    public function markAsFinished(string $finishedDate): bool
    {
        if ($this->status !== 'running') {
            return false;
        }

        return $this->update([
            'status' => 'finished',
            'finished_date' => $finishedDate,
        ]);
    }
	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class);
	}
	public function getTenantName():string
	{
		return $this->tenant? $this->tenant->getName() : '';
	}
	public function getTenantNature():string
	{
		return $this->tenant? $this->tenant->getNature() : '';
	}

    /**
     * Get collection interval label
     */
    public function getCollectionIntervalLabel(): string
    {
        return match ($this->collection_interval) {
            'monthly' => __('Monthly'),
            'quarterly' => __('Quarterly'),
            'semi-annually' => __('Semi-Annually'),
            'annually' => __('Annually'),
            default => $this->collection_interval,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'running' => __('Running'),
            'finished' => __('Finished'),
            'expired' => __('Expired'),
            default => $this->status,
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'running' => 'success',
            'finished' => 'info',
            'expired' => 'danger',
            default => 'secondary',
        };
    }
	public function getMinAmount(): float
	{
		return $this->min_amount?:0;
	}
	public function getVariableFromTenantRevenuesPercentage(): float
	{
		return $this->variable_from_tenant_revenues_percentage?:0;
	}
	public function calculateRentRevenues(): array
	{
		$amount = $this->getMinAmount()?:$this->monthly_rent;
		$startDateAsIndex = 0;
		$increaseInterval = 'annually';
		$endDateAsIndex = $this->contract_end_date->diffInMonths($this->contract_start_date)+1;
		$vatRate = $this->vat_rate?:0;
		$withholdRate = $this->withhold_rate?:0;
		$monthlyRevenues = (new MonthlyFixedRepeatingAmountEquation())->calculate($amount, $startDateAsIndex, $endDateAsIndex, $increaseInterval, $this->annually_increase_rate, false, $vatRate, $withholdRate,[],null,1);
		return [
			'before_vat'=>$monthlyRevenues['total_before_vat'],
			'after_vat'=>$monthlyRevenues['total_after_vat'],
		];
	}
	public function calculateRentCollections(array $afterVatRevenues, float $insuranceAmount): array
	{
		$result = (new CollectionPolicyService())->applyCollectionPolicy(true,'system_default', $this->collection_interval, $afterVatRevenues);
		$result[0] = isset($result[0])?$result[0]+$insuranceAmount:$insuranceAmount;
		return $result;
	}
	public function isRunning(): bool
	{
		return $this->status === 'running';
	}
	public function isFinished(): bool
	{
		return $this->status === 'finished';
	}
	public function isExpired(): bool
	{
		return $this->status === 'expired';
	}
	public function isRunningAt(string $date):bool
	{
		return $this->contract_start_date <= $date && $this->contract_end_date >= $date;
	}
	public function replaceDateDayWithOne(string $key )
	{
		$resultFormatted = [];
		foreach($this->{$key} as $dateAsString=>$amount){
			$dateFormatted = Carbon::make($dateAsString)->format('Y-m-01');
			$resultFormatted[$dateFormatted] = $amount;
		}
		return $resultFormatted;
	}
	public function getRevenueContractWithInDate(array $studyDates,Company $company):array 
	{
		$result= [];
		foreach($studyDates as $dateAsIndex=>$dateAsString){
			$amount = $this->replaceDateDayWithOne('rent_revenues')[$dateAsString] ?? 0;
			$exchangeRateAtDate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate($this->contract_currency, $this->collection_currency, $dateAsString, $company->id);
			$result[$dateAsIndex] = $amount * $exchangeRateAtDate ;
			
		}

		return $result;
	}
	public function getCollectionContractWithInDate(array $studyDates,Company $company):array 
	{
		$result= [];
		foreach($studyDates as $dateAsIndex=>$dateAsString){
			$amount = $this->replaceDateDayWithOne('rent_collections')[$dateAsString] ?? 0;
			$exchangeRateAtDate = ForeignExchangeRate::getExchangeRateForCurrencyAndClosestDate($this->contract_currency, $this->collection_currency, $dateAsString, $company->id);
			$result[$dateAsIndex] = $amount * $exchangeRateAtDate ;
		}
		return $result;
	}
	public function getMonthlyRent():float
	{
		return $this->monthly_rent?:0;
	}
	public function getCollectionInterval():string
	{
		return $this->collection_interval?:'monthly';
	}
	public function reCalculateRentRevenuesAndRentCollections(): void
	{
		$contractRevenuesAfterAndBeforeVat = $this->calculateRentRevenues();
		$dates = HDate::generateDatesBetweenStartDateAndDuration(0,$this->contract_start_date, $this->contract_end_date->diffInMonths($this->contract_start_date)+1,'monthly');
			$this->rent_revenues = replaceIndexKeysWithDate($contractRevenuesAfterAndBeforeVat['before_vat'], $dates);
			$this->rent_collections = replaceIndexKeysWithDate($this->calculateRentCollections($contractRevenuesAfterAndBeforeVat['after_vat'],$this->getInsuranceAmount()), $dates);
			$this->save();
			
	}
	public function getRentRevenueAtEndDate():float 
	{
		return $this->rent_revenues[$this->contract_end_date->format('Y-m-d')] ?? 0;
	}
	public function getEndDateAsString():?string 
	{
		return $this->contract_end_date;
	}
	public function getEndDateAsIndex(Study $study):int
	{
		$contractEnd = $this->contract_end_date->format('Y-m-01');
		return $study->getIndexDateFromString($contractEnd)??0;
	}
	
	public function getRentRevenueAtContractEndDate():float
	{
		return HDate::getValueFromDateAsYearAndMonth($this->rent_revenues, $this->contract_end_date->format('Y-m-d'));
	}

	/**
	 * Sum rent_revenues from contract start up to (and including) the given date.
	 */
	public function getSumRentRevenuesToDate(?string $toDate = null): float
	{
		$toDate = $toDate ?? now()->format('Y-m-d');
		$normalized = $this->replaceDateDayWithOne('rent_revenues');
		$sum = 0;
		foreach ($normalized as $dateAsString => $amount) {
			if ($dateAsString <= $toDate) {
				$sum += (float) $amount;
			}
		}
		return $sum;
	}

	/**
	 * Sum rent_collections from contract start up to (and including) the given date.
	 */
	public function getSumRentCollectionsToDate(?string $toDate = null): float
	{
		$toDate = $toDate ?? now()->format('Y-m-d');
		$collections = $this->rent_collections ?? [];
		$normalized = [];
		foreach ($collections as $dateAsString => $amount) {
			$dateFormatted = Carbon::make($dateAsString)->format('Y-m-01');
			$normalized[$dateFormatted] = ($normalized[$dateFormatted] ?? 0) + (float) $amount;
		}
		$sum = 0;
		foreach ($normalized as $dateAsString => $amount) {
			if ($dateAsString <= $toDate) {
				$sum += (float) $amount;
			}
		}
		return $sum;
	}
	public function formatForView(array $dates):array
	{
		return [
			'id'=>$this->id,
			'revenue_contract'=>$this->getRevenueContractWithInDate($dates,$this->company),
			'collection_contract'=>$this->getCollectionContractWithInDate($dates,$this->company),
			'tenant_name'=>$this->getTenantName(),
			'contract_start_date'=>Carbon::make($this->contract_start_date)->format('d-m-Y'),
			'contract_end_date'=>Carbon::make($this->contract_end_date)->format('d-m-Y'),
			'monthly_rent'=>$this->getMonthlyRent(),
			'collection_interval'=>$this->getCollectionInterval()
		];	
	}
}
