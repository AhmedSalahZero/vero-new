<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyContractFullRentRenewal;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class OccupiedPropertiesWithFullRentCoverageDurationController extends Controller
{
	use PropertyManagement ;
	public function create(Company $company , Request $request,Study $study){
		
		return view('property_managements.occupied-properties-with-full-rent-coverage-duration.form', $this->getViewVars($company,$study));
	}
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$company->load(['studies']);

		$dates =array_map(function($date){
			return formatDateForView($date);
		},array_flip($study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate()) );
		$yearIndexes = $study->getYearlyIndexes();
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
		$studyEndDate = $study->getStudyEndDate();


		return [
			'submitUrl'=> route('property.management.store.occupied.properties.with.full.rent.coverage.duration',['company'=>$company->id , 'study'=>$study->id]),
			'dates'=>(object)$dates,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'years'=>$yearIndexes,
			'model'=>[
				'properties'=>$study->getOccupiedPropertiesWithFullRentCoverageDuration($studyEndDate),
				'study_id'=>$study->id
				],
		];
	}
	
	protected function getViewVars(Company $company, Study $study){
		return [
			'company'=>$company ,
			'study'=>$study,
			'title'=>__('Occupied Properties With Full Rent Coverage Duration'),
			
		];
	}
	public function store(Company $company , Request $request,Study $study)
	{
		
		$redirectRoute = route('property.management.create.occupied.properties.with.partial.rent.coverage.duration', ['company'=>$study->company->id,'study'=>$study->id]);
		$study->propertyContractFullRentRenewals()->delete();
		$study->removeAreaPropertiesForRevenueStreamType(Property::FULL_COVERAGE);
		foreach($request->get('properties',[]) as $propertyArr){
			$propertyId = $propertyArr['id'];
			$contractId = $propertyArr['contract']['id'];
			$data = [
				'property_id'=>$propertyId,
				'contract_id'=>$contractId,
				'study_id'=>$study->id,
				'company_id'=>$company->id
			] ;
			$propertyContractFullRentRenewal = $study->propertyContractFullRentRenewalsForProperty($propertyId)->first();
			$propertyContractFullRentRenewal ? $propertyContractFullRentRenewal->update($data) : PropertyContractFullRentRenewal::create($data) ;
			$formattedResult = [];
			PropertyContractFullRentRenewal::getFullRentCoveragesAmounts($study,$formattedResult);
			$study->createNewAreaProperty($propertyId,Property::FULL_COVERAGE,$propertyArr['area']);
			$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		}

		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
	}
}
