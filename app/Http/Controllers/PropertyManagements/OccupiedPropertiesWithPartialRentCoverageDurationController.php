<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyContractPartialRentRenewal;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class OccupiedPropertiesWithPartialRentCoverageDurationController extends Controller
{
	use PropertyManagement ;
	public function create(Company $company , Request $request,Study $study){
		return view('property_managements.occupied-properties-with-partial-rent-coverage-duration.form', $this->getViewVars($company,$study));
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
		$studyStartDate = $study->getStudyStartDate();
		;
		return [
			'submitUrl'=> route('property.management.store.occupied.properties.with.partial.rent.coverage.duration',['company'=>$company->id , 'study'=>$study->id]),
			// 'submitUrl'=>route('property.management.store.revenue.stream',['company'=>$company->id , 'study'=>$study->id]),
			'dates'=>(object)$dates,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'years'=>$yearIndexes,
			'model'=>[
				'properties'=>$study->getOccupiedPropertiesWithPartialRentCoverageDuration($studyEndDate),
				'study_id'=>$study->id
				],
		];
	}
	
	protected function getViewVars(Company $company, Study $study){
		
		return [
			'company'=>$company ,
			'study'=>$study,
			'title'=>__('Occupied Properties With Partial Rent Coverage Duration & Vacant Properties'),
			
		];
	}
	public function store(Company $company , Request $request,Study $study)
	{
		$study->propertyContractPartialRentRenewals()->delete();
		$study->removeAreaPropertiesForRevenueStreamType(Property::PARTIAL_COVERAGE);
		foreach($request->get('properties') as $propertyArr){
			$companyId = $company->id;
			$studyId = $study->id;
			$propertyContractPartialRentRenewalData = $propertyArr['propertyContractPartialRentRenewal']??[];
			$property = Property::find($propertyArr['id']);
			$propertyContractPartialRentRenewalData['property_id'] = $propertyArr['id'];
			$propertyContractPartialRentRenewalData['company_id'] = $companyId;
			$propertyContractPartialRentRenewalData['study_id'] = $studyId;
			$propertyContractPartialRentRenewalData['contract_id'] = $propertyArr['contract']['id'];
			$renewalType = $propertyContractPartialRentRenewalData['renewal_type'];
			$propertyContractPartialRentRenewalData['renovate_cost'] = $renewalType == 1 ? 0 : $propertyContractPartialRentRenewalData['renovate_cost'];
			$propertyContractPartialRentRenewalData['renovate_duration'] = $renewalType == 1 ? 0 : $propertyContractPartialRentRenewalData['renovate_duration'];
			$propertyContractPartialRentRenewal=$property->propertyContractPartialRentRenewals->where('study_id',$studyId)->first();
			$propertyContractPartialRentRenewal ? $propertyContractPartialRentRenewal->update($propertyContractPartialRentRenewalData) : $propertyContractPartialRentRenewal = PropertyContractPartialRentRenewal::create($propertyContractPartialRentRenewalData) ;
			
			/**
			 * @var PropertyContractPartialRentRenewal $propertyContractPartialRentRenewal
			 */
			$propertyContractPartialRentRenewal->calculateContractRenewal();
			
			PropertyContractPartialRentRenewal::getPartialRentCoveragesAmounts($study);
			$study->createNewAreaProperty($propertyArr['id'],Property::PARTIAL_COVERAGE,$propertyArr['area']);
			$study->updateExpensesPercentageAndCostPerUnitsOfSales();
			
			
			
			
		}
		
		
		$redirectRoute = route('property.management.create.properties.to.be.delivered', ['company'=>$study->company->id,'study'=>$study->id]);
		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
	}
}
