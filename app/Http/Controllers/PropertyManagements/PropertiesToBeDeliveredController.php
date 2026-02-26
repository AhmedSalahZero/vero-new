<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PropertyManagement\Property;
use App\Models\PropertyManagement\PropertyToBeDelivered;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class PropertiesToBeDeliveredController extends Controller
{
	use PropertyManagement ;
	public function create(Company $company , Request $request,Study $study){
		return view('property_managements.properties-to-be-delivered.form', $this->getViewVars($company,$study));
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
			'submitUrl'=> route('property.management.store.properties.to.be.delivered',['company'=>$company->id , 'study'=>$study->id]),
			// 'submitUrl'=>route('property.management.store.revenue.stream',['company'=>$company->id , 'study'=>$study->id]),
			'dates'=>(object)$dates,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'years'=>$yearIndexes,
			'model'=>[
				'properties'=>$study->getPropertiesToBeDelivered($studyStartDate,$studyEndDate),
				'study_id'=>$study->id
				],
		];
	}
	
	protected function getViewVars(Company $company, Study $study){
		// $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		// $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		// $yearIndexes = $study->getYearlyIndexes();
		// $isYearsStudy = !$study->isMonthlyStudy();
		return [
			'company'=>$company ,
			'study'=>$study,
			'title'=>__('Properties To Be Delivered'),
			
		];
	}
	public function store(Company $company , Request $request,Study $study)
	{
		$study->propertyToBeDelivered()->delete();
		$study->removeAreaPropertiesForRevenueStreamType(Property::TO_BE_DELIVERED);
		foreach($request->get('properties') as $propertyArr){
			$companyId = $company->id;
			$studyId = $study->id;
			$propertyToBeDeliveredData = $propertyArr['propertyToBeDelivered']??[];
			$property = Property::find($propertyArr['id']);
			$propertyToBeDeliveredData['property_id'] = $propertyArr['id'];
			$propertyToBeDeliveredData['company_id'] = $companyId;
			$propertyToBeDeliveredData['study_id'] = $studyId;
			// $renewalType = $propertyToBeDeliveredData['renewal_type'];
			// $propertyToBeDeliveredData['renovate_cost'] = $renewalType == 1 ? 0 : $propertyToBeDeliveredData['renovate_cost'];
			// $propertyToBeDeliveredData['renovate_duration'] = $renewalType == 1 ? 0 : $propertyToBeDeliveredData['renovate_duration'];
			$propertyToBeDelivered=$property->propertiesToBeDelivered->where('study_id',$studyId)->first();
			$propertyToBeDelivered ? $propertyToBeDelivered->update($propertyToBeDeliveredData) : $propertyToBeDelivered=PropertyToBeDelivered::create($propertyToBeDeliveredData) ;
			/**
			 * @var PropertyToBeDelivered $propertyToBeDelivered
			 */
			$propertyToBeDelivered->calculateToBeDeliveredRentRevenue();
			$study->createNewAreaProperty($propertyArr['id'],Property::TO_BE_DELIVERED,$propertyArr['area']);
		}
		PropertyToBeDelivered::getToBeDeliveredCoveragesAmounts($study);
		$study->recalculateDueInstallments();
		$study->updateExpensesPercentageAndCostPerUnitsOfSales();
		$redirectRoute = route('property.management.create.forecasted.properties', ['company'=>$study->company->id,'study'=>$study->id]);
		
		return response()->json([
			'redirectTo'=>$redirectRoute
		]);
	}
}
