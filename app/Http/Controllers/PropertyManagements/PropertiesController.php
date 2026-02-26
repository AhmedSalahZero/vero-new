<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Country;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyDueInstallment;
use App\Services\PropertyService;
use App\Traits\PropertyManagement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PropertiesController extends Controller
{
	use PropertyManagement;
	
	protected $propertyService;
	
	public function __construct(PropertyService $propertyService)
	{
		$this->propertyService = $propertyService;
	}
	
	
	public function index(Company $company, Request $request)
	{
		$properties = Property::where('company_id', $company->id)
		//	->where('parent_property_id',null)
			->with([ 'country', 'governorate'])
			->orderBy('name', 'asc')
			->get()
			->map(function(Property $property) {
					$categoryName = $property->category ? $property->category->getName() : '';
					$typeName = $property->getTypeName();
					$typeName = $property->getTypeName();
				$contract=$property->getActiveContract();
				$dates = $contract ? array_keys($contract->rent_revenues?:[]):[];
				if(!count($dates) && $property->dueInstallment){
						$dates = array_keys($property->dueInstallment->total_due_installments?:[]);
				}
				$datesFormatted = [];
					foreach($dates as $dateAsIndex => $dateAsString){
						$datesFormatted[$dateAsIndex] = Carbon::make($dateAsString)->format('M Y');
						$dates[$dateAsIndex] = Carbon::make($dateAsString)->format('Y-m-01');
					}
				
					return [
						'nature_id' => $property->nature_id,
						'parent_property_id' => $property->parent_property_id,
						'parent'=>$property->parentProperty,
						'id' => $property->id,
						'name' => $property->name,
						'code' => $property->code,
						'country' => $property->country ? $property->country->getName():'-',
						'governorate' => $property->governorate ? $property->governorate->getName():'-',
						'city' => $property->city ? $property->city->getName():'-',
						'categoryName' => $categoryName,
						'typeName' => $typeName,
						'city_id' => $property->city_id,
						'area' => $property->area,
						'location' => $property->location,
						'unit_of_measurement' => $property->unit_of_measurement,
						'acquisition_cost' => $property->acquisition_cost,
						'current_book_value' => $property->current_book_value,
						'latest_market_value' => $property->latest_market_value,
						'market_values' => $property->market_values,
						'units' => $property->units,
						'dueInstallment' => $property->getDueInstallmentsFormatted(),
						'due_installment'=>$property->dueInstallment? $property->dueInstallment->getTotalDueInstallmentFormatted($dates) : [],
						'status'=>$property->getStatusFormatted(),
						'dates'=>$datesFormatted ,
						'contract'=>$contract?$contract->formatForView($dates):null,
					];
				
			})
			->filter(); // Remove null entries
			
		return view('property_managements.properties.index', [
			'company' => $company,
			'properties' => $properties,
			'empty_rows' => [
							'regular_installments_amounts' => PropertyDueInstallment::getDefaultRegularInstallmentAmounts(),
			],
			'title' => __('Properties'),
		]);
	}
	/**
	 * Show the form for creating a new property
	 */
	public function create(Company $company, Request $request)
	{
		return view('property_managements.properties.form', $this->getViewVars($company));
	}
	
	/**
	 * Show the form for editing a property
	 */
	public function edit(Company $company, Property $property)
	{
	
		return view('property_managements.properties.form', $this->getViewVars($company, $property));
	}
	
	/**
	 * Get old data for Vue component (for both create and edit)
	 */
	public function getOldData(Company $company, Request $request )
	{
		$propertyId = $request->get('property_id');

		$natureId = $request->get('nature_id', 'unit'); // Default to unit
		$property = $propertyId ? Property::find($propertyId) : null;

		return response()->json([
			'submitUrl' => $property 
				? route('property.management.update.properties', ['company' => $company->id, 'property' => $property->id])
				: route('property.management.store.properties', ['company' => $company->id]),
			'selects' => [
				'natures' => $company->getPropertyNaturesFormatted(),
				'categories' => $company->getCategoriesFormatted(),
			//	'types' => $company->getTypesFormatted(),
				'ownerships' => $company->getOwnershipsFormatted(),
				'unitOfMeasurements' => $company->getUnitOfMeasurements(),
				'usageStatus' => $company->getPropertyUsageStatuesFormatted(),
				'countries' => $this->getCountries(),
			],
			'model' => $this->propertyService->getModelDataFormatted($company, $property, $natureId),
			'empty_rows' => [
				'units' => Property::getPropertyFormatted($company, null),
				'tax_rates' => Property::getTaxRatesFormatted(null)[0],
				'market_values' => Property::getMarketValuesFormatted(null)[0],
			],
		]);
	}
	
	
	private function getCountries(): array
	{
		
			$countries = Country::with('governorates.cities')
				->get();
			
			$result = [];
		
			foreach ($countries as $country) {
				$governorates = [];
				foreach ($country->governorates as $governorate) {
				
						$cities = [];
						foreach ($governorate->cities as $city) {
							$cities[] = [
								'id' => $city->id,
								'title' => $city->getName(),
							];
						}
						
						$governorates[] = [
							'id' => $governorate->id,
							'title' => $governorate->getName(),
							'cities' => $cities
						];
					
				}
				
		
					$result[] = [
						'id' => $country->id,
						'title' => $country->getName(),
						'governorates' => $governorates
					];
				
			}
			
			return $result;
	
	}
	
	
	/**
	 * Get view variables for form
	 */
	protected function getViewVars(Company $company, ?Property $property = null)
	{
		return [
			'company' => $company,
			'property' => $property,
			'title' => $property ? __('Edit Property') : __('Create Property'),
		];
	}
	
	/**
	 * Store a newly created property
	 */
	public function store(StorePropertyRequest $request, Company $company)
	{
	
		try {
			$property = $this->propertyService->store($request, $company);
			if ($request->get('submit_button') == 'save') {
				return response()->json([
					'status' => true,
					'message' => __('Property created successfully'),
				]);
			}
			
			return response()->json([
				'redirectTo' => route('property.management.view.properties', ['company' => $company->id]),
				'message' => __('Property created successfully'),
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => false,
				'message' => __('Error creating property: ') . $e->getMessage(),
			], 500);
		}
	}
	
	/**
	 * Update the specified property
	 */
	public function update(StorePropertyRequest $request, Company $company, Property $property)
	{
		try {
			$this->propertyService->update($request, $property, $company);
			
			if ($request->get('submit_button') == 'save') {
				return response()->json([
					'status' => true,
					'message' => __('Property updated successfully'),
				]);
			}
			return response()->json([
				'redirectTo' => route('property.management.view.properties', ['company' => $company->id]),
				'message' => __('Property updated successfully'),
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => false,
				'message' => __('Error updating property: ') . $e->getMessage(),
			], 500);
		}
	}
	
	
	public function storeDueInstallments(Request $request, Company $company, Property $property)
	{
		$dueInstallment = $request->get('dueInstallment',[]);
		$dueInstallment['company_id'] = $company->id;
		$dueInstallment['signing_payment_date'] = $dueInstallment['signing_payment_date'] ? formatDateFromMonthPicker($dueInstallment['signing_payment_date']) : null;
		$dueInstallment['reservation_payment_date'] = $dueInstallment['reservation_payment_date'] ? formatDateFromMonthPicker($dueInstallment['reservation_payment_date']) : null;
		$dueInstallment['annual_start_date'] = $dueInstallment['annual_start_date'] ? formatDateFromMonthPicker($dueInstallment['annual_start_date']) : null;
		$dueInstallment['delivery_payments_start_date'] = $dueInstallment['delivery_payments_start_date'] ? formatDateFromMonthPicker($dueInstallment['delivery_payments_start_date']) : null;
		$dueInstallment['maintenance_payments_start_date'] = $dueInstallment['maintenance_payments_start_date'] ? formatDateFromMonthPicker($dueInstallment['maintenance_payments_start_date']) : null;
		
		foreach($dueInstallment['regular_installments_amounts']??[] as $index=>$regularInstallment) {
			$dueInstallment['regular_installments_amounts'][$index]['start_date'] = $regularInstallment['start_date'] ? formatDateFromMonthPicker($regularInstallment['start_date']) : null;
			$dueInstallment['regular_installments_amounts'][$index]['end_date'] = $regularInstallment['end_date'] ? formatDateFromMonthPicker($regularInstallment['end_date']) : null;
		}
		foreach($dueInstallment['variable_installment_amounts']??[] as $index=>&$variableInstallment) {
			$dueInstallment['variable_installment_amounts'][$index]['date'] = $variableInstallment['date'] ? formatDateFromMonthPicker($variableInstallment['date']) : null;
		}
		$property->dueInstallment()->updateOrCreate([
			'property_id' => $property->id
		], $dueInstallment);

		$property->calculatePropertyDueInstallments();
		
		
		return response()->json([
			'redirectTo' => route('property.management.view.properties', ['company' => $company->id]),
			'message' => __('Property updated successfully'),
		]);
		
	}
	
	/**
	 * Remove the specified property
	 */
	public function destroy(Company $company, Property $property)
	{
		try {
			$this->propertyService->delete($property);
			
			return response()->json([
				'redirectTo' => route('property.management.view.properties', ['company' => $company->id]),
				'message' => __('Property deleted successfully'),
			]);
		} catch (\Exception $e) {
			return response()->json([
				'status' => false,
				'message' => __('Error deleting property: ') . $e->getMessage(),
			], 500);
		}
	}
}
