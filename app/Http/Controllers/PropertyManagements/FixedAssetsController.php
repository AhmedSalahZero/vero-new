<?php

namespace App\Http\Controllers\PropertyManagements;

use App\Helpers\HHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFixedAssetsForPropertyRequest;
use App\Http\Requests\StoreFixedAssetsRequest;
use App\Models\Company;
use App\Models\PropertyManagement\Expense;
use App\Models\PropertyManagement\FixedAsset;
use App\Models\PropertyManagement\FixedAssetName;
use App\Models\PropertyManagement\Position;
use App\Models\PropertyManagement\Study;
use App\Traits\PropertyManagement;
use Illuminate\Http\Request;

class FixedAssetsController extends Controller
{
    use PropertyManagement ;
    public function create(Company $company, Request $request, Study $study)
    {
        return view('property_managements.ffe-fixed-assets.form', $this->getViewVars($company, $study));
    }
    public function createFundingStructure(Company $company, Request $request, Study $study)
    {
        return view('property_managements.ffe-fixed-assets.funding-form', $this->getViewVars($company, $study));
    }
    protected function getViewVars(Company $company, Study $study)
    {
        // $studyMonthsForViews = $study->getStudyDurationPerYearFromIndexesForView();
        // $yearWithItsIndexes = $study->getStudyDurationPerYearFromIndexes();
        // $fundingStructureCounts = $study->getFixedAssetsWithCountsDates(FixedAsset::FFE);
        
        return [
            // 'fundingStructureCounts'=>$fundingStructureCounts,
            // 'type'=>'create',
            'company'=>$company ,
            'study'=>$study,
            'model'=>$study ,
            // 'expenseType'=>HHelpers::getClassNameWithoutNameSpace((new Expense())),
            'title'=>__('Fixed Assets'),
            'storeRoute'=>route('property.management.store.ffe.fixed.assets', ['company'=>$company->id , 'study'=>$study->id]),
            // 'storeFundingRoute'=>route('store.ffe.funding.structure.fixed.assets', ['company'=>$company->id , 'study'=>$study->id]),
            // 'monthsWithItsYear' => $yearWithItsIndexes,
            // 'studyMonthsForViews'=>$studyMonthsForViews,
            // 'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
            // 'fixedAssetType'=>FixedAsset::FFE
        ];
    }
    public function getOldData(Company $company, Request $request, Study $study)
    {
		$dates = $study->getStudyDurationPerYearFromIndexesForView();
        $dates =array_map(function ($date) {
            return formatDateForView($date);
        }, $dates);
	

        $yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
        $lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths);

        
        
        $departmentsFormatted = $company->departments->sortBy('name')->pluck('name', 'id')->toArray() ;
        $departments = [];
        
        foreach ($departmentsFormatted as $id=>$title) {
            $departments[] = [
                'id'=>$id,
                'title'=>$title
            ];
        }
        $positionPerDepartments = [];
        foreach ($departments as $departmentArr) {
            $departmentId = $departmentArr['id'] ;
            $positions = Position::where('department_id', $departmentId)->pluck('name', 'id')->toArray();
            foreach ($positions as $positionId => $positionName) {
                $positionPerDepartments[$departmentId][] = [
                    'id'=>$positionId,
                    'title'=>$positionName
                ];
            }
        }
        
        
        
        return [
			'has_microfinance'=>false,
			// 'has_microfinance'=>$study->hasMicroFinance(),
            'submitUrl'=>route('property.management.store.ffe.fixed.assets', ['company'=>$company->id , 'study'=>$study->id]),
            'dates'=>(object)$dates,
            'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
            'departments'=>$departments,
           'positionsPerDepartments'=>$positionPerDepartments,
            'selects'=>[
                'generalFixedAssetNames'=>FixedAssetName::getGeneralAllForSelect($company),
                'perEmployeeFixedAssetNames'=>FixedAssetName::getPerEmployeeAllForSelect($company),
                // 'newBranchFixedAssetNames'=>FixedAssetName::getNewBranchAllForSelect($company),
            ],
            'empty_rows'=>[
                'ffe'=>FixedAsset::generateFFERow(null, $dates),
                'per-employee'=>FixedAsset::generatePerEmployeeRow(null, $dates),
				
            ],
            'model'=>[
                'ffe'=>$study->getFfeFormatted($dates),
                'generalFixedAssetsFundingStructure'=>$study->getGeneralFixedAssetsFundingStructureFormatted(FixedAsset::FFE),
          //      'new-branch'=>$study->getNewBranchFormatted(),
                'per-employee'=>$study->getPerEmployeeFormatted($positionPerDepartments),
                ],
        ];
    }
    
    protected function getRepeaterRelations():array
    {
		// $x = 5;
		// $x = 5;
		// $x = 5;
		// $x = 5;
		// $x = 5;
		// $x = 5;
        return [
            'fixedAssets'
        ];
    }
    public function store(Company $company, StoreFixedAssetsForPropertyRequest $request, Study $study)
    {
        $fixedAssetTypes = [
            'ffe'=>FixedAsset::FFE,
            'per-employee'=>FixedAsset::PER_EMPLOYEE,
      //      'new-branch'=>FixedAsset::NEW_BRANCH,
        ];
        // $fixedAssetType = $request->get('fixed_asset_type') ;
        // $oldIdsFromDatabase = $study->fixedAssets->where('type',$fixedAssetType)->pluck('id')->toArray();
        // $study->storeRepeaterRelations($request, $this->getRepeaterRelations(), $company, ['type'=>$fixedAssetType],$oldIdsFromDatabase);
        $study->storeRepeaterRelations($request, $this->getRepeaterRelations(), $company);
        $study->storeRelationsWithNoRepeater($request, $company);
        // $fundingStructureCounts = $study->getFixedAssetsWithCountsDates(FixedAsset::FFE);
        //$loanStructure = $study->getLoanStructure($fixedAssetType);
        //  $isFullyFundedThroughEquity = $request->input('generalFixedAssetsFundingStructure.is_fully_funded_though_equity') ;
        // if($isFullyFundedThroughEquity && $loanStructure){
        // 	 $loanStructure->delete();
        // }
		
		  $loanStructure = $study->getLoanStructure(FixedAsset::FFE);
			   /**
				* ! Only For FFE You Need To Add Another One For Microfinance
			    */
      	   $isFullyFundedThroughEquity = $request->input('generalFixedAssetsFundingStructure.is_fully_funded_though_equity') ;
			if($isFullyFundedThroughEquity && $loanStructure){
				$loanStructure->delete();
			}
			
        foreach ($fixedAssetTypes as $fixedAssetType) {
        // foreach ($fixedAssetTypes as $fixedAssetType) {
            $study->recalculateFixedAssets($fixedAssetType);
        }
        // if ($goToFundingStructure) {
        //     return response()->json([
        //     'redirectTo'=>route('create.ffe.funding.structure.fixed.assets', ['company'=>$company->id,'study'=>$study->id])
        // ]);
        // }
     
        if ($request->get('submit_button') == 'save') {
            return response()->json([
                'status'=>true
            ]);
        }
        $redirectRoute = $study->getFixedAssetNextRoute();
        return response()->json([
            'redirectTo'=>$redirectRoute
        ]);
        
    }
    public function storeFunding(Company $company, Request $request, Study $study)
    {
        $fixedAssetType = $request->get('fixed_asset_type') ;
        $study->storeRelationsWithNoRepeater($request, $company);
        $study->recalculateFixedAssets($fixedAssetType);
        $redirectRoute = $study->getFixedAssetNextRoute();
        return response()->json([
            'redirectTo'=>$redirectRoute
        ]);
        
    }

}
