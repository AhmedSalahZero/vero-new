<?php
namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\PropertyManagement\Category;
use App\Models\PropertyManagement\PropertyContractPartialRentRenewal;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\PropertyManagements\HasDueInstallment;
use App\Traits\HasBasicStoreRequest;
use App\Traits\HasCompany;
use Carbon\Carbon;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property string|null $code
 * @property string|null $nature_id
 * @property int|null $category_id
 * @property int|null $type_id
 * @property int|null $ownership_id
 * @property numeric|null $area
 * @property string|null $unit_of_measurement
 * @property numeric $acquisition_cost
 * @property string|null $acquisition_date
 * @property numeric $current_book_value
 * @property string|null $book_value_date
 * @property array<array-key, mixed>|null $depreciations
 * @property numeric $month_depreciation
 * @property int $duration_in_months
 * @property int $company_id
 * @property int|null $country_id
 * @property int|null $governorate_id
 * @property int|null $city_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $parent_property_id For units inside complex/building
 * @property array<array-key, mixed>|null $tax_rates
 * @property array<array-key, mixed>|null $market_values
 * @property-read \App\Models\PropertyManagement\Category|null $category
 * @property-read \App\Models\PropertyManagement\City|null $city
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property-read bool|null $contracts_exists
 * @property-read \App\Models\PropertyManagement\Country|null $country
 * @property-read \App\Models\PropertyManagement\PropertyDueInstallment|null $dueInstallment
 * @property-read float $latest_market_value
 * @property-read \App\Models\PropertyManagement\Governorate|null $governorate
 * @property-read \App\Models\PropertyManagement\Property|null $parentProperty
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\PropertyToBeDelivered> $propertiesToBeDelivered
 * @property-read int|null $properties_to_be_delivered_count
 * @property-read bool|null $properties_to_be_delivered_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\PropertyContractPartialRentRenewal> $propertyContractPartialRentRenewals
 * @property-read int|null $property_contract_partial_rent_renewals_count
 * @property-read bool|null $property_contract_partial_rent_renewals_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\PropertyExpense> $propertyExpenses
 * @property-read int|null $property_expenses_count
 * @property-read bool|null $property_expenses_exists
 * @property-read \App\Models\PropertyManagement\PropertyType|null $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\Property> $units
 * @property-read int|null $units_count
 * @property-read bool|null $units_exists
 * @method static \Database\Factories\PropertyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereAcquisitionCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereAcquisitionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereBookValueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereCurrentBookValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereDepreciations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereDurationInMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereGovernorateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereMarketValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereMonthDepreciation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereNatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereOwnershipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereParentPropertyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereTaxRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereUnitOfMeasurement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Property whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Property extends Model
{
	use HasFactory;
	const FULL_COVERAGE = 'full-coverage';
	const PARTIAL_COVERAGE = 'partial-coverage';
	const TO_BE_DELIVERED = 'to-be-delivered';
	const PROPERTY_FORECASTED = 'forecasted-property';
    use HasBasicStoreRequest, CompanyScope,HasCompany,HasDueInstallment;
	protected static function newFactory()
	{
		return PropertyFactory::new();
	}
    protected $connection= 'property_management';
	protected $appends = ['latest_market_value'];
    protected $guarded = ['id'];
    const COMPLEX = 'complex';
    const BUILDING = 'building';
    const LAND = 'land';
	const UNIT = 'unit';
   protected $casts =[
	'tax_rates' => 'array',
	'market_values' => 'array',
	'depreciations' => 'array',
   ];
   public static function boot()
   {
	parent::boot();
	static::saving(function(self $model){
		$depreciationInMonths = $model->duration_in_months?:0;
		if($depreciationInMonths <= 0){
			$model->depreciations = [];
			return ;
		}
		$monthlyDepreciationAmount = $model->month_depreciation?:0;
		$bookValueDate = $model->book_value_date?:null;
		$depreciations = [];
		for($i = 0; $i < $depreciationInMonths; $i++){
			$depreciations[] = [
				'date' => $bookValueDate ? Carbon::make($bookValueDate)->addMonths($i)->format('Y-m-d') : null,
				'amount' => $monthlyDepreciationAmount ,
			];
		}
		$model->depreciations = $depreciations;
		// $model->depreciations = $model->depreciations ? json_encode($model->depreciations) : null;
	});
	
   }
   public function getTaxRates():array
   {
	return $this->tax_rates?:[];
   }
   public static function getTaxRatesFormatted(?Property $property = null):array
   {
	return $property && count($property->getTaxRates()) ? collect($property->getTaxRates())->map(function ($tax) {
		return [
			'rate' => $tax['rate'],
			'date' => formatDateForVueDatePicker($tax['date']) ,
		];
	})->toArray() : [
		[ 'rate' => 0, 'date' =>formatDateForVueDatePicker(now()->format('Y-m-d')) ],
	];
   }
   public function setTaxRatesAttribute($value)
   {
	$taxRates = [];
	foreach($value as $tax){
		$taxRates[] = [
			'rate' => $tax['rate'],
			'date' => formatDateFromMonthPicker($tax['date']) ,
		];
	}
	$this->attributes['tax_rates'] = json_encode($taxRates);
   }
   public function setMarketValuesAttribute($value)
   {
	$marketValues = [];
	foreach($value as $marketValue){
		$marketValues[] = [
			'value' => $marketValue['value'],
			'date' => formatDateFromMonthPicker($marketValue['date']) ,
		];
	}
	$this->attributes['market_values'] = json_encode($marketValues);
   }
   public function getMarketValues():array
   {
	return $this->market_values?:[];
   }
   public static function getMarketValuesFormatted(?Property $property = null):array
   {
	return $property && count($property->getMarketValues()) ? collect($property->getMarketValues())->map(function ($marketValue) {
		return [
			'value' => $marketValue['value'],
			'date' => formatDateForVueDatePicker($marketValue['date']) ,
		];
		})->toArray() : [
		[ 'value' => 0, 'date' => formatDateForVueDatePicker(now()->format('Y-m-d')) ],
	];
   }
    /**
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'property_id');
    }
	public function getActiveContract():?Contract
	{
		return $this->contracts->where('contract_end_date', '>=', now()->format('Y-m-d'))->first();
	}

	/** Latest running contract (status = running), by contract_start_date desc */
	public function getLatestRunningContract():?Contract
	{
		return $this->contracts()
			->where('status', 'running')
			->orderByDesc('contract_start_date')
			->first();
	}
	public function getContractMonthlyRent():float
	{
		$activeContract = $this->getActiveContract();
		return $activeContract ? $activeContract->getMonthlyRent() : 0;
	}
	public function getContractMonthlyRentFormatted():string
	{
		return number_format($this->getContractMonthlyRent(),0);
	}
	public function getVacantDate():string 
	{
		return $this->hasActiveContracts() ? $this->getActiveContract()->getEndDateAsString() : $this->getAcquisitionDate();
	}
    public function units(): HasMany
    {
        return $this->hasMany(Property::class, 'parent_property_id')->where('nature_id', 'unit');
    }
    
    public function parentProperty():BelongsTo
    {
        return $this->belongsTo(Property::class, 'parent_property_id');
    }
    public function getName():string
    {
        return $this->name;
    }
    public function getCode():string
    {
        return $this->code?:'';
    }
    public function getNatureId():string
    {
        return $this->nature_id?:'unit' ;
    }
   
    public function getOwnershipId():int
    {
        return $this->ownership_id?:0;
    }
  
    public function getArea():int
    {
        return $this->area?:0;
    }
	public function getLocation():string
    {
        return $this->location?:0;
    }
    public function getUnitOfMeasurement():string
    {
        return $this->unit_of_measurement?:'';
    }
    public function getAcquisitionCost():float
    {
        return $this->acquisition_cost?:'';
    }
    public function getAcquisitionDate():string
    {
        return $this->acquisition_date?:'';
    }
	public function getAcquisitionDateFormattedForVueDatePicker():array
	{
		return $this->acquisition_date ? formatDateForVueDatePicker($this->acquisition_date) : formatDateForVueDatePicker(now()->format('Y-m-d'));
	}
    public function getCurrentBookValue():float
    {
        return $this->current_book_value?:0;
    }
	public function getBookValueDate():string
	{
		return $this->book_value_date?:'';
	}
	public function setBookValueDateAttribute($value)
	{
		$this->attributes['book_value_date'] = $value ? formatDateFromMonthPicker($value) : null;
	}
	public function getBookValueDateFormatted():string
	{
		return $this->book_value_date ? Carbon::make($this->book_value_date)->format('d-m-Y') : '';
	}
	
	public function getBookValueDateFormattedForVueDatePicker():array
	{
		return $this->book_value_date ? formatDateForVueDatePicker($this->book_value_date) : formatDateForVueDatePicker(now()->format('Y-m-d'));
	}
	public function getLatestMarketValueAttribute():float
    {
        return collect($this->market_values)->last()['value']??0;
    }
    public function getMonthDepreciation():float
    {
        return $this->month_depreciation?:0;
    }
    public function getDurationInMonths():float
    {
        return $this->duration_in_months?:0;
    }
	
	
    
    public function getCategoryId():int
    {
        return $this->category_id?:0;
    }
	public function category():BelongsTo
	{
		return $this->belongsTo(Category::class, 'category_id');
	}
	public function getCategoryName():string
	{
		return $this->category ? $this->category->getName() : '';
	}
    public function getTypeId():int
    {
        return $this->type_id?:0;
    }
	public function type():BelongsTo
	{
		return $this->belongsTo(PropertyType::class, 'type_id');
	}
	public function getTypeName():string
	{
		return $this->type ? $this->type->getName() : '';
	}
	public function getCountryId():?int
    {
        return $this->country_id;
    }
    
    public function getGovernorateId():?int
    {
        return $this->governorate_id;
    }
    
    public function getCityId():?int
    {
        return $this->city_id;
    }
	
	// Relationships
	public function country():BelongsTo
	{
		return $this->belongsTo(Country::class, 'country_id');
	}
	
	public function governorate():BelongsTo
	{
		return $this->belongsTo(Governorate::class, 'governorate_id');
	}
	
	public function city():BelongsTo
	{
		return $this->belongsTo(City::class, 'city_id');
	}
	
	public function setAcquisitionDateAttribute($value)
	{
		$this->attributes['acquisition_date'] = $value ? formatDateFromMonthPicker($value) : null;
	}

	
	public function getParentPropertyId(): ?int
	{
		return $this->parent_property_id;
	}
	
	public function isUnit(): bool
	{
		return $this->nature_id === 'unit';
	}
	
	public function isLand(): bool
	{
		return $this->nature_id === 'land';
	}
	
	public function isComplex(): bool
	{
		return $this->nature_id === 'complex';
	}
	
	public function isBuilding(): bool
	{
		return $this->nature_id === 'building';
	}
	
	public static function getPropertyFormatted(Company $company, ?Property $property = null):array
	{
		return [
			'id' => $property ? $property->id : 0,
			'company_id' => $company->id,
			'nature_id' => $property ? $property->getNatureId() : 'unit',
			'name' => $property ? $property->getName() : '',
			'code' => $property ? $property->getCode() : '',
			'ownership_id' => $property ? $property->getOwnershipId() : null,
			'country_id' => $property ? $property->getCountryId() : 1,
			'governorate_id' => $property ? $property->getGovernorateId() : null,
			'type_id' => $property ? $property->getTypeId() : null,
			'category_id' => $property ? $property->getCategoryId() : null,
			'city_id' => $property ? $property->getCityId() : null,
			'area' => $property ? $property->getArea() : 0,
			'location' => $property ? $property->getLocation() : '',
			'unit_of_measurement' => $property ? $property->getUnitOfMeasurement() : null,
			'acquisition_cost' => $property ? $property->getAcquisitionCost() : 0,
			'acquisition_date' => $property ? $property->getAcquisitionDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'current_book_value' => $property ? $property->getCurrentBookValue() : 0,
			'book_value_date' => $property ? $property->getBookValueDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'latest_market_value' => $property ? $property->latest_market_value : 0,
			'month_depreciation' => $property ? $property->getMonthDepreciation() : 0,
			'duration_in_months' => $property ? $property->getDurationInMonths() : 0,
			'tax_rates' => Property::getTaxRatesFormatted($property)  ,
			'market_values' => Property::getMarketValuesFormatted($property)  ,
			
		];
	}
	public function dueInstallment(): HasOne
	{
		return $this->hasOne(PropertyDueInstallment::class, 'property_id');
	}
	public function getInstallmentDeliveryDate():?string 
	{
		return $this->dueInstallment? $this->dueInstallment->getDeliveryDate() : null;
	}
	public function getDueInstallmentsFormatted():array
	{
		$dueInstallment = $this->dueInstallment;
		/**
		 * @var PropertyDueInstallment|null $dueInstallment
		 */
		return [
			'id' => $dueInstallment ? $dueInstallment->id : 0,
			'delivery_date'=>$dueInstallment ? $dueInstallment->getDeliveryDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'ready_to_use_date'=>$dueInstallment ? $dueInstallment->getReadyToUseDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'installment_type' => $dueInstallment ? $dueInstallment->getInstallmentType() : 'regular',
			'signing_payment' => $dueInstallment ? $dueInstallment->getSigningPayment() : 0,
			'signing_payment_date' =>$dueInstallment ? $dueInstallment->getSigningPaymentDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'reservation_payment' => $dueInstallment ? $dueInstallment->getReservationPayment() :0,
			'reservation_payment_date' => $dueInstallment ? $dueInstallment->getReservationPaymentFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'regular_installments_amounts' => $dueInstallment ? $dueInstallment->getRegularInstallmentAmounts() : PropertyDueInstallment::getDefaultRegularInstallmentAmounts(),
			'annual_start_date' => $dueInstallment ? $dueInstallment->getAnnualStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'annual_amount' => $dueInstallment ? $dueInstallment->getAnnualAmount() : 0,
			'annual_count' => $dueInstallment ? $dueInstallment->getAnnualCount() : 0,
			'delivery_payments_start_date' => $dueInstallment ? $dueInstallment->getDeliveryPaymentsStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'delivery_payments_amount' => $dueInstallment ? $dueInstallment->getDeliveryPaymentsAmount() : 0,
			'delivery_payments_payment_interval' => $dueInstallment ? $dueInstallment->getDeliveryPaymentsPaymentInterval() : 'monthly',
			'maintenance_payments_start_date' => $dueInstallment ? $dueInstallment->getMaintenancePaymentsStartDateFormattedForVueDatePicker() : formatDateForVueDatePicker(now()->format('Y-m-d')),
			'maintenance_payments_amount' => $dueInstallment ? $dueInstallment->getMaintenancePaymentsAmount() : 0,
			'maintenance_payments_count' => $dueInstallment ? $dueInstallment->getMaintenancePaymentsCount() : 0,
			'maintenance_payments_payment_interval' => $dueInstallment ? $dueInstallment->getMaintenancePaymentsPaymentInterval() : 'monthly',
			'delivery_payments_count' => $dueInstallment ? $dueInstallment->getDeliveryPaymentsCount() : 0,
	//		'installment_payments' => $dueInstallment ? $dueInstallment->getInstallmentPayments() : [],
			'variable_installment_amounts' => $dueInstallment ? $dueInstallment->getVariableInstallmentAmounts() : PropertyDueInstallment::getDefaultVariableInstallmentAmounts(),
			'has_annually_installments'=>$dueInstallment ? $dueInstallment->getHasAnnuallyInstallments() : 0,
			'has_delivery_payments'=>$dueInstallment ? $dueInstallment->getHasDeliveryPayments() : 0,
			'has_maintenance_payments'=>$dueInstallment ? $dueInstallment->getHasMaintenancePayments() : 0,
		];
	}
	
	public function hasContracts():bool
	{
		return $this->contracts->count() > 0;
	}
	// public function isRunningAt(string $date):bool
	// {
	// 	return $this->contracts->where('contract_start_date', '<=', $date)->where('contract_end_date', '>=', $date)->count() > 0;
	// }
	public function hasActiveContracts():bool
	{
		return $this->contracts->where('contract_end_date', '>=', now()->format('Y-m-d'))->count() > 0;
	}
	public function hasDueInstallments():bool
	{
		return !!$this->dueInstallment;
	}
	/**
	 * * انا استلمت الشقه
	 */
	public function isDelivered():bool
	{
		return $this->dueInstallment && $this->dueInstallment->getDeliveryDate() ? Carbon::make($this->dueInstallment->getDeliveryDate())->isFuture() : true;
	}
	/**
	 * * هل مشغولة ولا لا
	 */
	public function isOccupied():bool
	{
		return $this->hasActiveContracts() > 0 ;
	}
	public function isReadyToUse():bool 
	{
		return $this->dueInstallment && $this->dueInstallment->getReadyToUseDate() ? Carbon::make($this->dueInstallment->getReadyToUseDate())->isPast() : false;
	}
	public function hasDeliveryDate():bool
	{
		return $this->dueInstallment && $this->dueInstallment->getDeliveryDate();
	}
	public function deliveryDateIsFuture():bool
	{
		return $this->hasDeliveryDate() ? Carbon::make($this->dueInstallment->getDeliveryDate())->isFuture() : false;
	}
	// public function isVacant():bool
	// {
	// 	return !$this->isOccupied() && $this->hasInstallments();
	// }
	public function getStatusFormatted():string 
	{
		if($this->hasDeliveryDate() && $this->deliveryDateIsFuture()){
			return __('To Be Delivered');
		}
		if($this->isOccupied() && $this->hasInstallments()){
			return __('Occupied / Installments');
		}
		elseif(
			 !$this->isOccupied() &&
			 $this->hasInstallments()){
			return __('Vacant / Installments');
		}
		elseif($this->isOccupied()){
			return __('Occupied');
		}
		else{
			return __('Vacant');
		}
		// return '-';
		
		// if($this->isReadyToUse()){
		// 	return 'ready to use';
		// }
		// return 'empty';
		
	}
	public function isEmpty():bool
	{
		return !$this->isOccupied() && $this->isReadyToUse()  ;
	}
	
	public function hasInstallments():bool
	{
		return $this->hasDueInstallments() > 0;
	}
	public function propertyContractPartialRentRenewals():HasMany
	{
		return $this->hasMany(PropertyContractPartialRentRenewal::class,'property_id','id');
	}
	public function propertiesToBeDelivered():HasMany
	{
		return $this->hasMany(PropertyToBeDelivered::class,'property_id','id');
	}
	public static function getTypesFormatted(Company $company):array
	{
		
		$properties = Property::where('company_id', $company->id)->with(['type'])->get();
		$propertyTypesFormatted = [];
		foreach($properties->pluck('type') as $propertyType){
			if($propertyType){
				$propertyTypesFormatted[$propertyType->id] = [
					'id' => $propertyType->id,
					'name' => $propertyType->getName(),
				];
			}
			
		}
		return $propertyTypesFormatted;
		
	}
	public function propertyExpenses():HasMany
	{
		return $this->hasMany(PropertyExpense::class,'property_id','id');
	}
	public function isVacantOrVacantWithInstallments():bool
	{
		return $this->getStatusFormatted() == __('Vacant') || $this->getStatusFormatted() == __('Vacant / Installments');
	}
	public function isToBeDelivered():bool
	{
		return $this->getStatusFormatted() == __('To Be Delivered');
	}
	public function isOccupiedOrOccupiedWithInstallments():bool
	{
		return $this->getStatusFormatted() == __('Occupied') || $this->getStatusFormatted() == __('Occupied / Installments');
	}
	public function getTenantName():string
	{
		return $this->getActiveContract() ? $this->getActiveContract()->getTenantName() : '';
	}
}
