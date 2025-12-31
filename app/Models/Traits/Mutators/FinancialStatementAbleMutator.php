<?php

namespace App\Models\Traits\Mutators;

use App\Helpers\HArr;
use App\Jobs\RecalculateIncomeStatementCalculationForTypesJob;
use App\Models\CashFlowStatement;
use App\Models\CashFlowStatementItem;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\ReadyFunctions\dd;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


trait FinancialStatementAbleMutator
{
	public function storeMainSection(Request $request):self
	{
		$financialStatementAble = (new static)::create(array_merge($request->except(['_token']), ['can_view_actual_report'=>0,'type' => getLastSegmentFromString(get_class(new static))]));

		return $financialStatementAble;
	}

	public function storeMainItems(Request $request)
	{
		
		foreach (($this->getMainItemTableClassName())::get() as $financialStatementAbleItem) {
			$financialStatementAbleItemId = $financialStatementAbleItem->id;
			$this->withMainItemsFor($financialStatementAbleItemId)->attach($financialStatementAbleItemId, [
				'company_id' => getCurrentCompanyId(),
				'creator_id' => Auth()->user()->id,
				'created_at' => now()
			]);

			if ($financialStatementAbleItemId == IncomeStatementItem::CORPORATE_TAXES_ID) {
				foreach (getAllFinancialAbleTypes() as $subItemType) {
					$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, 'Corporate Taxes')->attach($financialStatementAbleItemId, $this->getFinancialStatementAbleData($subItemType, 'forecast', [
						'percentage_or_fixed' => 'percentage',
						'can_be_percentage_or_fixed' => 1,
						'name' => 'Corporate Taxes',
						'percentage_value' =>$request->get('corporate_taxes_rate',0),
						'is_percentage_of' => ['Earning Before Taxes - EBT'],
					]));
				}
			}
		}

		return $this;
	}




	public function updatePivotForAdjustedSubItems(array $actualDatesAsIndexAndBooleans , int $financialStatementAbleItemId, string $sub_item_origin_name,array $subItemData,?string $isValueQuantityPrice ): void
	{
		$pivotForForecast = $this->withSubItemsFor($financialStatementAbleItemId, 'forecast', $sub_item_origin_name)->get()->sortByDesc('pivot.id')->pluck('pivot.payload')->toArray()[0] ?? [];
	
		$pivotForActual = $this->withSubItemsFor($financialStatementAbleItemId, 'actual', $sub_item_origin_name)->get()->sortByDesc('pivot.id')->pluck('pivot.payload')->toArray()[0] ?? [];
		$pivotForForecast = is_array($pivotForForecast) ? $pivotForForecast : (array)(json_decode($pivotForForecast));
		$pivotForActual = is_array($pivotForActual) ? $pivotForActual : (array)json_decode($pivotForActual);
		$pivotForModified = combineNoneZeroValuesBasedOnComingDates($actualDatesAsIndexAndBooleans,$pivotForForecast, $pivotForActual);
		$dataArr =  [
			'payload' => json_encode($pivotForModified),
			'total'=>array_sum($pivotForModified),
		//	'actual_dates' => json_encode($actualDates),
			'sub_item_type'=>'adjusted'
		];
	
		if($isValueQuantityPrice){
			$dataArr['is_value_quantity_price'] = $isValueQuantityPrice ; 
		}
		$this->withSubItemsFor($financialStatementAbleItemId, 'adjusted', $sub_item_origin_name)->detach();
		$this->withSubItemsFor($financialStatementAbleItemId, 'adjusted', $sub_item_origin_name)->attach($financialStatementAbleItemId,array_merge($subItemData,$dataArr));
		
			
			$dataArr = [
				'payload' => json_encode($pivotForModified),
				'total'=>array_sum($pivotForModified),
			//	'actual_dates' => json_encode($actualDates),
				'sub_item_type'=>'modified'
			];
			if($isValueQuantityPrice){
				$dataArr['is_value_quantity_price'] = $isValueQuantityPrice;
			}
			
			$this->withSubItemsFor($financialStatementAbleItemId, 'modified', $sub_item_origin_name)->detach();
			$this->withSubItemsFor($financialStatementAbleItemId, 'modified', $sub_item_origin_name)->attach($financialStatementAbleItemId, array_merge($subItemData , $dataArr));
	}

	public function syncPivotFor(array $actualDatesAsIndexAndBooleans , int $financialStatementAbleItemId, string $sub_item_type, string $sub_item_origin_name,array $subItemData,?string $isValueQuantityPrice )
	{
		if ($sub_item_type == 'forecast' || $sub_item_type == 'actual') {
			// for adjusted and modified for now
			$this->updatePivotForAdjustedSubItems($actualDatesAsIndexAndBooleans,$financialStatementAbleItemId, $sub_item_origin_name,$subItemData,$isValueQuantityPrice );
		}
	}

	public function syncSubItemName($financialStatementAbleItemId, $subItemType, $oldName, $newName)
	{
		$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, $oldName)->updateExistingPivot($financialStatementAbleItemId, [
			// not this
			'sub_item_name' =>  html_entity_decode($newName),
		]);
	}

	public function syncSubItemNameForPivot(int $financialStatementAbleItemId, string $subItemType, string $oldSubItemName, string $newSubItemName)
	{
		if ($subItemType == 'forecast') {
			$this->syncSubItemName($financialStatementAbleItemId, 'actual', $oldSubItemName, $newSubItemName);
			$this->syncSubItemName($financialStatementAbleItemId, 'adjusted', $oldSubItemName, $newSubItemName);
			$this->syncSubItemName($financialStatementAbleItemId, 'modified', $oldSubItemName, $newSubItemName);
		}
		if ($subItemType == 'actual') {
			$this->syncSubItemName($financialStatementAbleItemId, 'adjusted', $oldSubItemName, $newSubItemName);
			$this->syncSubItemName($financialStatementAbleItemId, 'modified', $oldSubItemName, $newSubItemName);
		}
		if ($subItemType == 'adjusted') {
			$this->syncSubItemName($financialStatementAbleItemId, 'modified', $oldSubItemName, $newSubItemName);
		}
	}

	public function getFinancialStatementAbleData(string $subType, string $subItemType, array $options, bool $isQuantityRepeating = false): array
	{
		
		$percentageOrFixed = isset($options['percentage_or_fixed']) && $options['percentage_or_fixed'] ? $options['percentage_or_fixed'] : 'non_repeating_fixed';
		if ($subType == 'actual') {
			$percentageOrFixed = 'non_repeating_fixed';
		}
		$collectionPolicyType = $options['collection_policy']['type']['name'] ?? null;

		$collection_value = null;
		if (isset($options['collection_policy']['type'][$collectionPolicyType]['value']) && is_array($options['collection_policy']['type'][$collectionPolicyType]['value'])) {
			$collection_value = json_encode($options['collection_policy']['type'][$collectionPolicyType]['value']);
		} elseif (isset($options['collection_policy']['type'][$collectionPolicyType]['value'])) {
			$collection_value = $options['collection_policy']['type'][$collectionPolicyType]['value'];
		}
		$adjustedOrModified = $subType == 'adjusted' || $subType == 'modified' ;
		$subItemName = $isQuantityRepeating ? html_entity_decode($options['name'] . __(quantityIdentifier)) : html_entity_decode($options['name']);
		$percentageOf= $percentageOrFixed == 'percentage' ? json_encode((array)$options['is_percentage_of']) : null;
		
		$percentageOf = $adjustedOrModified  ? json_encode(getMappingFromForecastToAdjustedOrModified($percentageOf,$subType)) : $percentageOf;
		$costOfUnitOf = $percentageOrFixed == 'cost_of_unit' ? json_encode((array)$options['is_cost_of_unit_of']) : null;
		// $isAllSelection = isAll($costOfUnitOf);
		// $costOfUnitOf = $isAllSelection ?  getAllPercentageOfRevenuesIds($options['financial_statement_able_item_id'] , $subType,1) : null;
		$costOfUnitOf = $adjustedOrModified  ? json_encode(getMappingFromForecastToAdjustedOrModified($costOfUnitOf,$subType)) : $costOfUnitOf;

		return [
			'company_id' => \getCurrentCompanyId(),
			'creator_id' => Auth::id(),
			'sub_item_type' => $subType,
			'sub_item_name' => $subItemName,
			'created_from' => $subItemType,
			'is_depreciation_or_amortization' => $options['is_depreciation_or_amortization'] ?? 0,
			'has_collection_policy' => $options['collection_policy']['has_collection_policy'] ?? false,
			'collection_policy_type' => $collectionPolicyType,
			'collection_policy_value' => $collection_value,
			'is_quantity' => $isQuantityRepeating,
			'can_be_quantity' => $options['can_be_quantity'] ?? false,
			'is_value_quantity_price' => $options['is_value_quantity_price'] ?? 'value',
			'percentage_or_fixed' => $percentageOrFixed,
			'can_be_percentage_or_fixed' => $options['can_be_percentage_or_fixed'] ?? false,
			'is_percentage_of' =>$percentageOf ,
			'is_cost_of_unit_of' => $costOfUnitOf,
			'repeating_fixed_value' => $percentageOrFixed == 'repeating_fixed' ? $options['repeating_fixed_value'] : null,
			'percentage_value' => $percentageOrFixed == 'percentage' ? $options['percentage_value'] : null,
			'cost_of_unit_value' => $percentageOrFixed == 'cost_of_unit' ? $options['cost_of_unit_value'] : null,
			'is_financial_expense' => isset($options['is_financial_expense']) && $options['is_financial_expense'],
			'is_financial_income' => isset($options['is_financial_income']) && $options['is_financial_income'],
			'vat_rate' => $subType =='forecast'&& isset($options['vat_rate'])  ? $options['vat_rate'] : 0,
			'is_deductible' => $subType =='forecast' && isset($options['is_deductible'])  ? $options['is_deductible'] : 0,
			'created_at' => now()
		];
	}
	// protected function calculateVatForPayload(array $items , $vatRate){
	// 	$result=[];
	// 	foreach($items as $date => $value){
	// 		$result[$date]=(1+$vatRate/100) * $value;
	// 	}
	// 	return $result;
	// }
	public function storeReport(Request $request)
	{
		// names of new added element in add popup
		$incomeStatementId = $request->input('financial_statement_able_id');
		$incomeStatement = (new static)::find($incomeStatementId);
		$incomeStatementItemId = $request->get('financial_statement_able_item_id');
	
		
		$formSubItemType = $request->get('sub_item_type');
		$insertSubItems =  $this->getInsertToSubItemFields($formSubItemType);
		$datesHelper = $incomeStatement->financialStatement->getDatesIndexesHelper();
		$dateIndexWithDate = $datesHelper['dateIndexWithDate'];
		$actualDatesAsIndexAndBooleans = HArr::getActualDatesAsIndexAndBoolean($dateIndexWithDate);
		$rows = $request->get('sub_items',[]);
		
		foreach($rows as $subItemArr){
			/**
			 * * دي علشان الرفع الخاص بال actual 
			 * * لان وقتها بنرفع اكثر من $financialStatementAbleItemId
			 */
			$financialStatementAbleItemId = is_null($incomeStatementItemId) ? $subItemArr['financial_statement_able_item_id'] :$incomeStatementItemId ;
			$currentSubItemValues = [];
			$isSalesRevenue = $financialStatementAbleItemId == 1 ;
			foreach($insertSubItems as $currentSubItemToBeInserted ){
				
				$currentSubItemDataArr = $incomeStatement->getFinancialStatementAbleData($currentSubItemToBeInserted,$formSubItemType,$subItemArr,false);
				$percentageOfValue = $currentSubItemDataArr['percentage_value'];
				$isPercentageOf = $currentSubItemDataArr['is_percentage_of'];
				$costOfUnitValue = $currentSubItemDataArr['cost_of_unit_value'];
				$isCostOfUnitOf = $currentSubItemDataArr['is_cost_of_unit_of'];
				$vatRate = $currentSubItemDataArr['vat_rate'];
				$isFinancialExpense = $currentSubItemDataArr['is_financial_expense'];
				$isDepreciationOrAmortization = $currentSubItemDataArr['is_depreciation_or_amortization'];
				$isDeductible =$currentSubItemDataArr['is_deductible']; 
				$percentageOfFixed = $currentSubItemDataArr['percentage_or_fixed'] ;
				$isNonRepeating = $percentageOfFixed == 'non_repeating_fixed' ;
				$isFixingRepeating = $percentageOfFixed == 'repeating_fixed' ;
				$isPercentage = $percentageOfFixed == 'percentage' ;
				$isCostOfUnit = $percentageOfFixed == 'cost_of_unit' ;
				$newSubItemName = $currentSubItemDataArr['sub_item_name'];
				
				
				$salesRevenuesSubItemsArray = $incomeStatement->getSalesRevenueArr($newSubItemName);
				
				if($isSalesRevenue 
				// && isset($subItemArr['val'])
				){
					$currentSubItemValues=$subItemArr['val'] ?? [];
					
					$currentPayloadForQuantity = $subItemArr['quantity'] ?? [];
					$currentDataForQuantity = $incomeStatement->getFinancialStatementAbleData($currentSubItemToBeInserted,$formSubItemType,$subItemArr,true);
				
					/**
					 * * هنا هنضيف صف جديد للكميه
					 */
					$currentDataForQuantity['total'] = array_sum($currentPayloadForQuantity);
					$currentDataForQuantity['payload'] = json_encode($currentPayloadForQuantity);
					
					if($formSubItemType != $currentSubItemToBeInserted){ 
						$oldRowForQuantity = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$currentDataForQuantity['sub_item_name'])->first() ;
						$oldRowForValue = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$newSubItemName)->first() ;
						$oldPayloadForQuantity = $oldRowForQuantity && $oldRowForQuantity->pivot ? $oldRowForQuantity->pivot->payload : json_encode([]);
						$oldPayloadForValue = $oldRowForValue && $oldRowForValue->pivot ? $oldRowForValue->pivot->payload : json_encode([]);
						$currentDataForQuantity['payload'] = $oldPayloadForQuantity ;
						$currentDataForQuantity['total'] = array_sum((array) json_decode($oldPayloadForQuantity)) ;
						$currentSubItemValues = (array) json_decode($oldPayloadForValue);
					}
					
					$currentDataForQuantity['is_quantity'] = 1;
					$currentDataForQuantity['can_be_quantity'] = 1 ;
					$currentSubItem  = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$currentDataForQuantity['sub_item_name']);
					
					$currentSubItemExist  = $currentSubItem->count();
				
						
					if(!$currentSubItemExist){
						$currentSubItem->attach($financialStatementAbleItemId,$currentDataForQuantity);
					}else{
						$currentSubItem->updateExistingPivot($financialStatementAbleItemId,$currentDataForQuantity);
					}
					$incomeStatement->syncPivotFor($actualDatesAsIndexAndBooleans,$financialStatementAbleItemId, $formSubItemType, $currentDataForQuantity['sub_item_name'],$currentDataForQuantity,null);
				}
				elseif($isNonRepeating){
					$currentSubItemValues = $subItemArr['non_repeating_popup'];
				}
				elseif($isFixingRepeating){
					$dates = $incomeStatement->getIntervalFormatted();
					// $subItemsFromForecastTable = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, 'forecast',$newSubItemName)->first();
					// $subItemsFromForecastTable=$subItemsFromForecastTable ? json_decode($subItemsFromForecastTable->pivot->payload) : [];
					foreach($dates as $date=>$dateFormatted ){
						$currentSubItemValues[$date]= $subItemArr['repeating_fixed_value'];
					}
				}
				elseif($isPercentage){
					$dates = $incomeStatement->getIntervalFormatted();
					$currentSubItemValues = $incomeStatement->getPayloadForPercentageOf($actualDatesAsIndexAndBooleans,$salesRevenuesSubItemsArray,$newSubItemName,$vatRate,$percentageOfValue,$isPercentageOf,$isFinancialExpense,$incomeStatementId,$financialStatementAbleItemId,$currentSubItemToBeInserted,$dates,$isDeductible);	
					$currentSubItemValues = $currentSubItemValues[$financialStatementAbleItemId][$newSubItemName] ?? [];
				}
				elseif($isCostOfUnit){
					$dates = $incomeStatement->getIntervalFormatted();
					$currentSubItemValues = $this->getPayloadForCostOf($actualDatesAsIndexAndBooleans,$currentSubItemValues,$salesRevenuesSubItemsArray,$newSubItemName,$vatRate,$costOfUnitValue,$isCostOfUnitOf,$isFinancialExpense,$incomeStatementId,$financialStatementAbleItemId,$currentSubItemToBeInserted,$dates,$isDeductible);	
					$currentSubItemValues = $currentSubItemValues[$financialStatementAbleItemId][$newSubItemName] ?? [];
				}
				if($formSubItemType != $currentSubItemToBeInserted){
						$oldRow = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$newSubItemName)->first() ;
						$currentSubItemValues = $oldRow && $oldRow->pivot ? (array)json_decode($oldRow->pivot->payload) : [];
				}
				if(!$isDeductible && $vatRate > 0  && !$isDepreciationOrAmortization && !$isSalesRevenue && !$isPercentage  && !$isCostOfUnit ){
					$currentSubItemValues = $this->calculatePayloadWithVat($currentSubItemValues,$currentSubItemToBeInserted,$isDeductible,$vatRate,$financialStatementAbleItemId); 
				}
				
				$currentSubItemDataArr['total'] = array_sum($currentSubItemValues);
				$currentSubItemDataArr['payload'] = json_encode($currentSubItemValues);
				
	//			$currentSubItemDataArr['actual_dates']=json_encode($actualDates);
				$currentSubItemExist = $incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$newSubItemName)->count();
				$attachOrUpdateExistingMethod = $currentSubItemExist ? 'updateExistingPivot' : 'attach';
				$incomeStatement->withSubItemsFor($financialStatementAbleItemId, $currentSubItemToBeInserted,$newSubItemName)->$attachOrUpdateExistingMethod($financialStatementAbleItemId,$currentSubItemDataArr);
				
			}
			$incomeStatement->syncPivotFor($actualDatesAsIndexAndBooleans,$financialStatementAbleItemId, $formSubItemType, $newSubItemName,$currentSubItemDataArr,null);
			
		}
		
		
		if (get_class($this) == IncomeStatement::class || ($request->get('in_add_or_edit_modal') && $request->get('financial_statement_able_item_id') == IncomeStatementItem::SALES_REVENUE_ID)) {
			foreach ($insertSubItems as $index=>$insertSubItem) {
				if($index ==0 ) // current type
				{
					// $f = microtime(true);
					$incomeStatement->refreshCalculationFor($insertSubItem);
				}else{
					
					
					$incomeStatement['is_caching_'.$insertSubItem] = 1 ;
					$incomeStatement->save();
						$job = (new RecalculateIncomeStatementCalculationForTypesJob($incomeStatement,$insertSubItem));
						dispatch($job)	;
						
						// $incomeStatement->refreshCalculationFor($insertSubItem);
					
				}
			}
		}
	
		$incomeStatement->can_view_actual_report = 1 ;
		$incomeStatement->save();
		
		
		return $incomeStatement;
	}


	public function getInsertToSubItemFields(string $subItemType): array
	{
		if ($subItemType == 'forecast') {
			return getAllFinancialAbleTypes();
		}
		if ($subItemType == 'actual') {
			return getAllFinancialAbleTypes(['forecast']);
		}

		return [$subItemType];
	}
	
	public function calculateVat($vat){
		return (1+$vat/100) ;
	}
	protected function getPayloadForCostOf(array $actualDatesAsIndexAndBooleans , $payload,$salesRevenuesSubItemsArray,$subItemName,$vatRate,$costOfUnitValue,$isCostOfUnitOf,$isFinancialExpense,$financialStatementAbleId,$financialStatementAbleItemId,$subItemType,$dates,$isDeductible):array
	{

		$values =[];
		$costOfUnitsOf = stringArrayToArray($isCostOfUnitOf);
				$isAllSelection = isAll($costOfUnitsOf);
				$costOfUnitsOf = $isAllSelection ?  getAllPercentageOfRevenuesIds($financialStatementAbleId, $subItemType,1) :$costOfUnitsOf;
				if ($isFinancialExpense && $costOfUnitValue >0) {
					$costOfUnitValue = $costOfUnitValue * -1;
				}
				foreach ($dates as $date => $formattedDate) {
					$totalCostOfUnitValue = 0;
					foreach ($costOfUnitsOf as $costOfUnitOf) {
						$totalCostOfUnitValue += $salesRevenuesSubItemsArray[$costOfUnitOf][$date] ?? 0;
					}
				
					if (isActualDateInModifiedOrAdjusted($date, $subItemType,$actualDatesAsIndexAndBooleans)) {
						$currentPayloadValue =is_array($payload) ? ($payload[$date]??0) :  ($payload->{$date}) ;
						$values[$financialStatementAbleItemId][$subItemName][$date] = $currentPayloadValue;
					} else {
						$vatValue = 1 ;
						if( $subItemType == 'forecast' && !$isDeductible && $financialStatementAbleItemId != IncomeStatementItem::SALES_REVENUE_ID){
								$vatValue = $this->calculateVat($vatRate) ;
						}
						$values[$financialStatementAbleItemId][$subItemName][$date] = $costOfUnitValue * $totalCostOfUnitValue * $vatValue;
					}
				}
				return $values;
	}
	protected function getPayloadForPercentageOf(array $actualDatesAsIndexAndBooleans,$salesRevenuesSubItemsArray,$subItemName,$vatRate,$percentageValue,$isPercentageOf,$isFinancialExpense,$financialStatementAbleId,$financialStatementAbleItemId,$subItemType,$dates,$isDeductible)
	{
	
			$values = [];
				if ($isFinancialExpense && $percentageValue >0) {
					$percentageValue = $percentageValue * -1;
				}
				$percentageValue = $percentageValue / 100;
				$percentagesOf = stringArrayToArray($isPercentageOf);
				$isAllSelection = isAll($percentagesOf);
			
				$percentagesOf = $isAllSelection ?  getAllPercentageOfRevenuesIds($financialStatementAbleId, $subItemType,0) : $percentagesOf;

				foreach ($dates as $date => $formattedDate) {
					$totalPercentageOfValue = 0;
					foreach ($percentagesOf as $percentageOf) {
						$loopPercentageValueOfSalesRevenue = $salesRevenuesSubItemsArray[$percentageOf][$date] ?? 0;
						$totalPercentageOfValue += $loopPercentageValueOfSalesRevenue;
					}
					if (isActualDateInModifiedOrAdjusted($date, $subItemType,$actualDatesAsIndexAndBooleans)) {
						$values[$financialStatementAbleItemId][$subItemName][$date] = isset($payload->{$date}) ? $payload->{$date} : 0;
					} else {
						$percentageVal = $percentageValue * $totalPercentageOfValue ;
						if($subItemType == 'forecast' && !$isDeductible){
							$vatValue =  1 ;
							if($financialStatementAbleItemId != IncomeStatementItem::SALES_REVENUE_ID){
								$vatValue = $this->calculateVat($vatRate) ;
							}
							$percentageVal = $percentageVal* $vatValue;
						}
						$values[$financialStatementAbleItemId][$subItemName][$date] = $percentageVal ;
					}
				}
				return $values;
	}
	protected function getSalesRevenueArr($subItemType)
	{
		$salesRevenueId = IncomeStatementItem::SALES_REVENUE_ID;
		return $this->withSubItemsFor($salesRevenueId, $subItemType)->get()->keyBy(function ($salesRevenueSubItem) {
			return $salesRevenueSubItem->pivot->id;
		})->map(function ($salesRevenuePivotSubItem) {
			return (array)json_decode($salesRevenuePivotSubItem->pivot->payload);
		})->toArray();
	}
	public function updateCostOfUnitAndPercentagesOfSubItems(array $actualDatesAsIndexAndBooleans , array $salesRevenuesSubItemsArray,Collection $subItemsForCurrentIncomeStatementItem, array $dates, string $subItemType): void
	{
		foreach ($subItemsForCurrentIncomeStatementItem as $subItem) {
			$financialStatementAbleItemId = $subItem->pivot->financial_statement_able_item_id;
			$subItemName = $subItem->pivot->sub_item_name;
			$values = [];
			
			$payload = json_decode($subItem->pivot->payload);
			$subItemPivotType = $subItem->pivot->percentage_or_fixed;
			$isPercentage = $subItemPivotType == 'percentage';
			$isCostOfUnit = $subItemPivotType == 'cost_of_unit';
			$isFinancialExpense = $subItem->pivot->is_financial_expense;
			$percentageOfValue = $subItem->pivot->percentage_value ?: 0;
			$costOfUnitValue = $subItem->pivot->cost_of_unit_value ?: 0;
			$isCostOfUnitOf = $subItem->pivot->is_cost_of_unit_of ?: 0;
				$isPercentageOf = $subItem->pivot->is_percentage_of;
				$financialStatementAbleId = $subItem->pivot->financial_statement_able_id ;
				$currentSubItemType = $subItem->pivot->sub_item_type;
				$isDeductible = $subItem->pivot->is_deductible;
				$vatRate = $subItem->pivot->vat_rate;
				
			if ($isPercentage && $subItemName == 'Corporate Taxes') {
				// will update it in another place while updating main row for earning before tax
				// search for the following comment to find it
				// update sub items of corporate taxes [needs to be here]
			} elseif ($isPercentage) {
				
				$values = $this->getPayloadForPercentageOf($actualDatesAsIndexAndBooleans,$salesRevenuesSubItemsArray,$subItemName,$vatRate,$percentageOfValue,$isPercentageOf,$isFinancialExpense,$financialStatementAbleId,$financialStatementAbleItemId,$currentSubItemType,$dates,$isDeductible);	
				$total = array_sum($values[$financialStatementAbleItemId][$subItemName] ?? []);
				if($total == 0){
					$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, $subItemName)->detach();
				}else{
					$payload = $values[$financialStatementAbleItemId][$subItemName] ;
					$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, $subItemName)->updateExistingPivot($financialStatementAbleItemId, [
						'payload' => json_encode($payload),
						'total'=>array_sum($payload),
						'is_deductible'=>$subItem->pivot->is_deductible,
						'vat_rate'=>$subItem->pivot->vat_rate
					]);
				}
				
			} elseif ($isCostOfUnit) {
				$values = $this->getPayloadForCostOf($actualDatesAsIndexAndBooleans,$payload,$salesRevenuesSubItemsArray,$subItemName,$vatRate,$costOfUnitValue,$isCostOfUnitOf,$isFinancialExpense,$financialStatementAbleId,$financialStatementAbleItemId,$currentSubItemType,$dates,$isDeductible);	
				$total = array_sum($values[$financialStatementAbleItemId][$subItemName] ?? []);
				if($total == 0){
					$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, $subItemName)->detach();
				}else{
					$payload = $values[$financialStatementAbleItemId][$subItemName];
					$this->withSubItemsFor($financialStatementAbleItemId, $subItemType, $subItemName)->updateExistingPivot($financialStatementAbleItemId, [
						'total'=>array_sum($payload),
						'payload' => json_encode($payload),
						'is_deductible'=>$subItem->pivot->is_deductible,
						'vat_rate'=>$subItem->pivot->vat_rate
					]);
				}
				
			}
		}
	}

	public function refreshCalculationFor(string $subItemType):void
	{
		$dates = $this->getIntervalFormatted();
		// $mainItemIdThatTriggerEditOrCreate =  Request()->get('financial_statement_able_item_id') ; 
		// $changables = [
		// 	1 => [
		// 		1 , 2 ,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24
		// 	] , 
		// 	3=> [
		// 		3,4,5,6,13,14,15,16,19,20,21,22,23,24
		// 	],
		// 	7=> [
		// 		7,8,13,14,15,16,19,20,21,22,23,24
		// 	],
		// 	9=> [
		// 		9,10,13,14,15,16,19,20,21,22,23,24
		// 	],
		// 	11=> [
		// 		11,12,13,14,15,16,19,20,21,22,23,24
		// 	],
		// 	17=> [
		// 		17,18,19,20,21,22,23,24
		// 	],
		// ][$mainItemIdThatTriggerEditOrCreate];
		$allMainItems = $this->mainItems()
		->get();
		$totals = [];
		// $debug = false ;
		
		$salesRevenuesSubItemsArray = $this->getSalesRevenueArr($subItemType);
		$datesHelper = $this->financialStatement->getDatesIndexesHelper();
		$dateIndexWithDate = $datesHelper['dateIndexWithDate'];
		$actualDatesAsIndexAndBooleans = HArr::getActualDatesAsIndexAndBoolean($dateIndexWithDate);
		$companyId = $this->company_id;
		$creatorId = $this->creator_id;
		$mainRows = [];
		$this->removeMainRowsCalculations($subItemType);
		foreach ($allMainItems as $mainItem) {
			$incomeStatementItemId = $mainItem->id;
			$isMainWithSubItems= $mainItem->has_sub_items;
			$isPercentageOfSalesRevenue = IncomeStatementItem::isPercentageOfSalesRevenue($incomeStatementItemId);
			$isMainWithoutSubItems = !$mainItem->has_sub_items && !$isPercentageOfSalesRevenue;
			// IncomeStatementItem::isMainWithoutSubItems($allMainItems, $incomeStatementItemId, $isPercentageOfSalesRevenue)
			// $oldSubItemsForCurrentMainItem = $this->withSubItemsFor($incomeStatementItemId, $subItemType)->get();
			$oldSubItemsForCurrentMainItem = $this->withSubItemsFor($incomeStatementItemId, $subItemType)->get();
			
			$this->updateCostOfUnitAndPercentagesOfSubItems($actualDatesAsIndexAndBooleans,$salesRevenuesSubItemsArray,$oldSubItemsForCurrentMainItem, $dates, $subItemType);
			
			$subItems = $this->withSubItemsFor($incomeStatementItemId, $subItemType)->get()->keyBy(function ($subItem) {
				return $subItem->pivot->sub_item_name;
			})->map(function ($subItem) {
				// /**
				//  * ! remove this
				//  */
				// $payload = $subItem->pivot ? (array)json_decode($subItem->pivot->payload):[];
				// 	DB::table('financial_statement_able_main_item_sub_items')->where('id',$subItem->pivot->id)->update(['total'=>array_sum($payload)]);
				// 	/**
				//  * ! end remove this
				//  */
				$pivot = $subItem->pivot;
				// cache::fore
				return [
					'options' => [
						'name' => $pivot->sub_item_name,
						'sub_item_type' => $pivot->sub_item_type,
						'payload' => $pivot->payload ? (array)json_decode($pivot->payload) : [],
						'total'=>$pivot->payload ? array_sum((array)json_decode($pivot->payload)) : 0,
						'sub_item_type' => $pivot->sub_item_type,
						'has_collection_policy' => $pivot->has_collection_policy,
						'collection_policy_type' => $pivot->collection_policy_type,
						'collection_policy_value' => $pivot->collection_policy_value,
						'is_quantity' => $pivot->is_quantity,
						'can_be_quantity' => $pivot->can_be_quantity,
						'is_value_quantity_price'=>$pivot->is_value_quantity_price,
						'is_depreciation_or_amortization' => $pivot->is_depreciation_or_amortization ?: 0,
						'percentage_or_fixed' => $pivot->percentage_or_fixed,
						'can_be_percentage_or_fixed' => $pivot->can_be_percentage_or_fixed,
						'repeating_fixed_value' => $pivot->repeating_fixed_value,
						'percentage_value' => $pivot->percentage_value ?: 0,
						'cost_of_unit_value' => $pivot->cost_of_unit_value ?: 0,
						'is_financial_expense' => $pivot->is_financial_expense ?: 0,
						'is_financial_income' => $pivot->is_financial_income ?: 0,
						'is_deductible'=>$pivot->is_deductible,
						'vat_rate'=>$pivot->vat_rate,
						'parent' => [
							'name' => $subItem->name,
							'has_sub_items' => $subItem->has_sub_items,
							'has_depreciation_or_amortization' => $subItem->has_depreciation_or_amortization,
							'has_percentage_or_fixed_sub_items' => $subItem->has_percentage_or_fixed_sub_items,
							'financial_statement_able_type' => $subItem->financial_statement_able_type,
							'is_main_for_all_calculations' => $subItem->is_main_for_all_calculations,
							'is_sales_rate' => $subItem->is_sales_rate,
							'for_interval_comparing' => $subItem->for_interval_comparing,
							'depends_on' => $subItem->depends_on,
							'equation' => $subItem->equation,
							'has_auto_depreciation' => $subItem->has_auto_depreciation,
							'is_auto_depreciation_for' => $subItem->is_auto_depreciation_for,
							'is_accumulated' => $subItem->is_accumulated,
						]
					],
					'values' => $subItem->pivot ? (array)json_decode($subItem->pivot->payload) : [],
					'total' => $subItem->pivot ? $subItem->pivot->total : 0

				];
			})->toArray();
	
	
			// 1- recalculate sub items [because modified ] (percentage and cost of units)

			// 2- recalculate totals
			// $start = microtime(true);
			$totals[$incomeStatementItemId] = $this->recalculateTotalForRow($actualDatesAsIndexAndBooleans,$isMainWithSubItems,$isMainWithoutSubItems,$isPercentageOfSalesRevenue,$dates, $incomeStatementItemId, $subItems, $subItemType, $totals,$companyId,$creatorId,$mainRows);
			// $end = microtime(true)-$start;
			// $time = $time + $end ;
		}
		$this->attachMainCalculations($mainRows);
	}

	protected function recalculateTotalForRow(array $actualDatesAsIndexAndBooleans , bool $isMainWithSubItems , bool $isMainWithoutSubItems , bool $isPercentageOfSalesRevenue , array $dates, int $incomeStatementItemId, array $subItemNameWithDateValues, string $subItemType, array &$allItemsTotals,int $companyId , int $creatorId , array &$mainRows)
	{
		$currentItemTotal = [];

		$corporateTaxesID = IncomeStatementItem::CORPORATE_TAXES_ID;
		if ($isMainWithSubItems && $incomeStatementItemId != $corporateTaxesID) {
		// if (IncomeStatementItem::isMainWithSubItems($allMainItems, $incomeStatementItemId) && $incomeStatementItemId != IncomeStatementItem::CORPORATE_TAXES_ID) {
			$totalOfAllRows = 0;
			$totalAtDates = [];
			$totalDepreciationAtDates = [];
			
			if(!count($subItemNameWithDateValues)){
				// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->detach();
				$mainRows[] = $this->getMainRowCalculationsArr($incomeStatementItemId,$subItemType,0,json_encode([]),$companyId,$creatorId);
			
				// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->attach($incomeStatementItemId, [
				// 	'total' => 0,
				// 	'payload' => json_encode([]),
				// 	'company_id'=>$companyId,
				// 	'creator_id'=>$creatorId,
				// 	'sub_item_type'=>$subItemType
				// ]);	
			}
			foreach ($subItemNameWithDateValues as $subItemName => $optionsAndValues) {
				$dateValues = $optionsAndValues['values'];
				$options = $optionsAndValues['options'];
				// 1 - total of each sub item
				if ($subItemName == 'Corporate Taxes') {
					$currentItemTotal['total']['sub_items'][$subItemName] = $allItemsTotals[$corporateTaxesID]['total']['total'] ?? 0;
				
				} else {
					$currentItemTotal['total']['sub_items'][$subItemName] = array_sum($dateValues);
				}

				// 2 - parent total for each total of sub items
				if ($incomeStatementItemId == IncomeStatementItem::SALES_REVENUE_ID) {
					if (!$options['is_quantity']) {
						$totalOfAllRows += $currentItemTotal['total']['sub_items'][$subItemName];
					}
				} else {
					$totalOfAllRows += $currentItemTotal['total']['sub_items'][$subItemName];
				}
		
				// if($subItemName=='P1'){
					
				// }
				// 3-parent total for each total
	
				foreach ($dateValues as $date => $value) {
					if ($incomeStatementItemId == IncomeStatementItem::SALES_REVENUE_ID) {
						if (!$options['is_quantity']) {
							$totalAtDates[$date]  = isset($totalAtDates[$date]) ? $totalAtDates[$date] + $value : $value;
						}
					} else {
						$totalAtDates[$date]  = isset($totalAtDates[$date]) ? $totalAtDates[$date] + $value : $value;
			
						
					}

					if ($options['is_depreciation_or_amortization']) {
						$totalDepreciationAtDates[$date] = isset($totalDepreciationAtDates[$date]) ? $totalDepreciationAtDates[$date] + $value : $value;
					}
				}
				
				// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->detach();
				
				// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->attach($incomeStatementItemId, [
				// 	'total' => $totalOfAllRows,
				// 	'payload' => json_encode($totalAtDates),
				// 	'company_id'=>$companyId,
				// 	'creator_id'=>$creatorId,
				// 	'sub_item_type'=>$subItemType
				// ]);
			}

			$currentItemTotal['total']['dates'] = $totalAtDates;
			$currentItemTotal['total']['total'] = $totalOfAllRows ?: 0;
			$currentItemTotal['total']['totalDepreciationAtDates'] = $totalDepreciationAtDates;
			$mainRows[] = $this->getMainRowCalculationsArr($incomeStatementItemId,$subItemType,$totalOfAllRows,json_encode($totalAtDates),$companyId,$creatorId);
		} elseif ($isMainWithoutSubItems) {
			$currentItemTotal = $this->calculateTotalForMainRowWithoutSubItems($actualDatesAsIndexAndBooleans,$incomeStatementItemId, $allItemsTotals, $dates, $subItemType,$companyId,$mainRows);
		} elseif ($isPercentageOfSalesRevenue) {
			$currentItemTotal = $this->calculateTotalPercentageOfSalesRevenueFor($incomeStatementItemId, $allItemsTotals, $dates, $subItemType,$companyId,$mainRows);
		} elseif (IncomeStatementItem::CORPORATE_TAXES_ID == $incomeStatementItemId) {
			$corporateTaxesRow = $this->withSubItemsFor($incomeStatementItemId, $subItemType, 'Corporate Taxes')->first() ;
			
			$percentageOfCorporateTaxes = $corporateTaxesRow && $corporateTaxesRow->pivot ? $corporateTaxesRow->pivot->percentage_value : 0;
			$percentageOfCorporateTaxes = $percentageOfCorporateTaxes / 100;

			$totalOfEarningBeforeTaxes = $allItemsTotals[IncomeStatementItem::EARNING_BEFORE_TAXES_ID]['total']['total'] ?? 0;
			$currentItemTotal['total']['total'] = $totalOfEarningBeforeTaxes < 0 ? 0 : $totalOfEarningBeforeTaxes * $percentageOfCorporateTaxes;
	
			// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->detach();
			// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->attach($incomeStatementItemId, [
			// 	'total' => $currentItemTotal['total']['total'] ?? 0,
			// 	// update sub items of corporate taxes [needs to be here]
			// 	// main row will be zero allows
			// 	'payload' => json_encode([]),
			// 	'company_id'=>$companyId,
			// 	'creator_id'=>$creatorId,
			// 	'sub_item_type'=>$subItemType
			// ]);
			$mainRows[]=$this->getMainRowCalculationsArr($incomeStatementItemId,$subItemType, $currentItemTotal['total']['total'] ?? 0,json_encode([]),$companyId,$creatorId);
		}


		return $currentItemTotal;
	}

	protected function calculateTotalPercentageOfSalesRevenueFor(int $incomeStatementItemId, array &$allItemsTotals, array $dates, string $subItemType,int $companyId,array &$mainRows): array
	{
		$values = [];
		$mapParentId  = array_flip(IncomeStatementItem::salesRateMap())[$incomeStatementItemId];
		$salesRevenueId = IncomeStatementItem::SALES_REVENUE_ID;
		$totalOfSalesRevenue = $allItemsTotals[$salesRevenueId]['total']['total'] ?? 0;
		$totalOfCurrentIncomeStatementItem = $allItemsTotals[$mapParentId]['total']['total'] ?? 0;
		$values['total']['total'] = $totalOfSalesRevenue ? $totalOfCurrentIncomeStatementItem / $totalOfSalesRevenue * 100 : 0;
		foreach ($dates as $date => $formattedDate) {
			$totalOfSalesRevenueAtDate = $allItemsTotals[$salesRevenueId]['total']['dates'][$date] ?? 0;
			$totalOfCurrentIncomeStatementItemAtDate = $allItemsTotals[$mapParentId]['total']['dates'][$date] ?? 0;
			$values['total']['dates'][$date] = $totalOfSalesRevenueAtDate ? $totalOfCurrentIncomeStatementItemAtDate / $totalOfSalesRevenueAtDate * 100 : 0;
		}
		// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->detach();
		// $this->withMainRowsFor($incomeStatementItemId, $subItemType)->attach($incomeStatementItemId, [
		// 	'total' => $values['total']['total'] ?? 0,
		// 	'payload' => json_encode($values['total']['dates'] ?? []),
		// 	'company_id'=>$companyId,
		// 	'sub_item_type'=>$subItemType,
		// 	'creator_id'=>$this->creator_id 
		// ]);
		$mainRows[] = $this->getMainRowCalculationsArr($incomeStatementItemId,$subItemType,$values['total']['total'] ?? 0,json_encode($values['total']['dates'] ?? []),$companyId,$this->creator_id);
		

		return $values;
	}

	protected function calculateTotalForMainRowWithoutSubItems(array $actualDatesAsIndexAndBooleans , int $incomeStatementItemId, array $totalOfMainRows, array $dates, string $subItemType,int $companyId , array &$mainRows)
	{
		$salesRevenueId = IncomeStatementItem::SALES_REVENUE_ID;
		$salesGrowthRateId = IncomeStatementItem::SALES_GROWTH_RATE_ID;
		$costOfGodsId = IncomeStatementItem::COST_OF_GOODS_ID;
		$grossProfitId = IncomeStatementItem::GROSS_PROFIT_ID;
		$marketExpenseId = IncomeStatementItem::MARKET_EXPENSES_ID;
		$salesExpenseId = IncomeStatementItem::SALES_AND_DISTRIBUTION_EXPENSES_ID;
		$generalExpensesId = IncomeStatementItem::GENERAL_EXPENSES_ID;
		$earningBeforeTaxesId = IncomeStatementItem::EARNING_BEFORE_TAXES_ID;
		$corporateTaxesID = IncomeStatementItem::CORPORATE_TAXES_ID;
	//	$netProfitId = IncomeStatementItem::NET_PROFIT_ID;
		$values = [];
		$valuesForCorporateTaxesAtDate = [];

		$equation = IncomeStatementItem::getEquationFor($this, $incomeStatementItemId);
		$corporateTaxesPercentage = $this->withSubItemsFor($corporateTaxesID, $subItemType, 'Corporate Taxes')->first()->pivot->percentage_value ?? 0;
		$corporateTaxesPercentage = $corporateTaxesPercentage / 100;

		if ($incomeStatementItemId === $salesGrowthRateId) {
			$values['total']['total'] = 0;
		
			foreach ($dates as $date => $formattedDate) {
				$values['total']['dates'][$date] = 0;
				$previousDate  = HArr::getPreviousKey($dates, $date);
				if (!is_null($previousDate)) {
					$totalOfSalesRevenueAtDate = $totalOfMainRows[$salesRevenueId]['total']['dates'][$date] ?? 0;
					$totalOfSalesRevenueAtPreviousDate = $totalOfMainRows[$salesRevenueId]['total']['dates'][$previousDate] ?? 0;
					$growthRateDiff = $totalOfSalesRevenueAtDate - $totalOfSalesRevenueAtPreviousDate;
					$growthRate = $totalOfSalesRevenueAtPreviousDate ? $growthRateDiff / $totalOfSalesRevenueAtPreviousDate * 100 : 0;
					$values['total']['dates'][$date] = $growthRate;
				}
			}
		} elseif ($incomeStatementItemId === IncomeStatementItem::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID) {
			$values['total']['total'] = 0;
			foreach ($dates as $date => $formattedDate) {
				$totalOfGrossProfitAtDate = $totalOfMainRows[$grossProfitId]['total']['dates'][$date] ?? 0;
				$totalOfMarketExpenseAtDate = $totalOfMainRows[$marketExpenseId]['total']['dates'][$date] ?? 0;
				$totalOfSalesExpenseAtDate = $totalOfMainRows[$salesExpenseId]['total']['dates'][$date] ?? 0;
				$totalOfGeneralExpenseAtDate = $totalOfMainRows[$generalExpensesId]['total']['dates'][$date] ?? 0;
				$totalDepreciationOfCostOfGoodsAtDate = $totalOfMainRows[$costOfGodsId]['total']['totalDepreciationAtDates'][$date] ?? 0;
				$totalDepreciationOfMarketExpenseAtDate = $totalOfMainRows[$marketExpenseId]['total']['totalDepreciationAtDates'][$date] ?? 0;
				$totalDepreciationOfSalesExpenseAtDate = $totalOfMainRows[$salesExpenseId]['total']['totalDepreciationAtDates'][$date] ?? 0;
				$totalDepreciationOfGeneralExpenseAtDate = $totalOfMainRows[$generalExpensesId]['total']['totalDepreciationAtDates'][$date] ?? 0;
				$totalDepreciationsAtDate = $totalDepreciationOfCostOfGoodsAtDate + $totalDepreciationOfMarketExpenseAtDate + $totalDepreciationOfSalesExpenseAtDate + $totalDepreciationOfGeneralExpenseAtDate;
				$earningBeforeInterestTaxesAtDate = $totalOfGrossProfitAtDate - $totalOfMarketExpenseAtDate - $totalOfSalesExpenseAtDate - $totalOfGeneralExpenseAtDate;
				$earningBeforeInterestTaxesDepreciationAmortizationAtDate = $earningBeforeInterestTaxesAtDate + $totalDepreciationsAtDate;
				$values['total']['dates'][$date] = $earningBeforeInterestTaxesDepreciationAmortizationAtDate;
				$values['total']['total'] += $earningBeforeInterestTaxesDepreciationAmortizationAtDate;
			}
		} elseif ($incomeStatementItemId === IncomeStatementItem::EARNING_BEFORE_INTEREST_TAXES_ID) {
			$values['total']['total'] = 0;
			foreach ($dates as $date => $formattedDate) {
				$totalOfGrossProfitAtDate = $totalOfMainRows[$grossProfitId]['total']['dates'][$date] ?? 0;
				$totalOfMarketExpenseAtDate = $totalOfMainRows[$marketExpenseId]['total']['dates'][$date] ?? 0;
				$totalOfSalesExpenseAtDate = $totalOfMainRows[$salesExpenseId]['total']['dates'][$date] ?? 0;
				$totalOfGeneralExpenseAtDate = $totalOfMainRows[$generalExpensesId]['total']['dates'][$date] ?? 0;
				$totalDepreciationOfCostOfGoodsAtDate = $totalOfMainRows[$costOfGodsId]['totalDepreciationAtDates']['dates'][$date] ?? 0;
				$totalDepreciationOfMarketExpenseAtDate = $totalOfMainRows[$marketExpenseId]['totalDepreciationAtDates']['dates'][$date] ?? 0;
				$totalDepreciationOfSalesExpenseAtDate = $totalOfMainRows[$salesExpenseId]['totalDepreciationAtDates']['dates'][$date] ?? 0;
				$totalDepreciationOfGeneralExpenseAtDate = $totalOfMainRows[$generalExpensesId]['totalDepreciationAtDates']['dates'][$date] ?? 0;
				$totalDepreciationsAtDate = $totalDepreciationOfCostOfGoodsAtDate + $totalDepreciationOfMarketExpenseAtDate + $totalDepreciationOfSalesExpenseAtDate + $totalDepreciationOfGeneralExpenseAtDate;
				$earningBeforeInterestTaxesAtDate = $totalOfGrossProfitAtDate - $totalOfMarketExpenseAtDate - $totalOfSalesExpenseAtDate - $totalOfGeneralExpenseAtDate;
				$values['total']['dates'][$date] = $earningBeforeInterestTaxesAtDate;
				$values['total']['total'] += $earningBeforeInterestTaxesAtDate;

				// for corporate taxes sub items
				// update sub items of corporate taxes [needs to be here]
			//	$valueForCurrentCorporateTaxesSubItem = $earningBeforeInterestTaxesAtDate * $corporateTaxesPercentage;
				if (isActualDateInModifiedOrAdjusted($date, $subItemType,$actualDatesAsIndexAndBooleans) || $subItemType == 'actual') {
					$pivotForCorporateTaxes = $this->withSubItemsFor($corporateTaxesID, $subItemType, 'Corporate Taxes')->first();
					$pivotForCorporateTaxes = $pivotForCorporateTaxes ? $pivotForCorporateTaxes->pivot : null;
					if (!$pivotForCorporateTaxes) {
						$valuesForCorporateTaxesAtDate[$date] = 0;
					} else {
						$valuesForCorporateTaxesAtDate[$date] = 0;
					}
				} else {
					$valuesForCorporateTaxesAtDate[$date] = 0;
				}
			}
		}
	
		elseif ($incomeStatementItemId === IncomeStatementItem::NET_PROFIT_ID) {
			$totalOfCorporateTaxes = $totalOfMainRows[$corporateTaxesID]['total']['total'] ?? 0;
			$totalOfEarningBeforeTaxes = $totalOfMainRows[$earningBeforeTaxesId]['total']['total'] ?? 0;
			$values['total']['total'] = $totalOfEarningBeforeTaxes - $totalOfCorporateTaxes;
			foreach ($dates as $date => $formattedDate) {
				$totalOfCorporateTaxesAtDate = $totalOfMainRows[$corporateTaxesID]['total']['dates'][$date] ?? 0;
				$totalOfEarningBeforeTaxesAtDate = $totalOfMainRows[$earningBeforeTaxesId]['total']['dates'][$date] ?? 0;
				$values['total']['dates'][$date] =  $totalOfEarningBeforeTaxesAtDate - $totalOfCorporateTaxesAtDate;
			}
		} elseif ($equation) {
			$equationsIds = preg_split("/[-\+\/\*]/", $equation);
			$values['total']['total'] = 0;
			foreach ($dates as $date => $formattedDate) {
				$mainIdsWithItsValues = $this->replaceEquationIdsWithItsValues($equationsIds, $date, $totalOfMainRows);
				$formattedEquation = replaceArr($mainIdsWithItsValues, $equation);
				$values['total']['dates'][$date] = eval('return ' . $formattedEquation . ';');
				$values['total']['total'] += $values['total']['dates'][$date];
			}
		}
	
		$mainRows[] = $this->getMainRowCalculationsArr($incomeStatementItemId,$subItemType,$values['total']['total'] ?? 0,json_encode($values['total']['dates'] ?? []),$companyId,$this->creator_id);
	
		if ($incomeStatementItemId === IncomeStatementItem::EARNING_BEFORE_INTEREST_TAXES_ID) {
			// update sub items of corporate taxes [needs to be here]
			$subItemName = 'Corporate Taxes';
			$this->withSubItemsFor($corporateTaxesID, $subItemType, $subItemName)->updateExistingPivot($corporateTaxesID, [
				'payload' => json_encode($valuesForCorporateTaxesAtDate ?? []),
				'total'=>array_sum($valuesForCorporateTaxesAtDate ?? []),
				'company_id'=>$companyId
			]);
		}
	
		return $values;
	}

	protected function replaceEquationIdsWithItsValues(array $equationIds, string $date, array $totalOfMainRows): array
	{
		$values = [];
		foreach ($equationIds as  $mainItemId) {
			$totalOfMainRowAtDate = $totalOfMainRows[$mainItemId]['total']['dates'][$date] ?? 0;
			$values[$mainItemId] = $totalOfMainRowAtDate;
		}

		return $values;
	}
	public function calculatePayloadWithVat(array $payload , string $subItemType ,bool $isDeductible , float $vatRate , int $financialStatementItemAbleId):array 
	{
		
		if($subItemType != 'forecast'){
			return $payload ;
		}
		if($financialStatementItemAbleId == IncomeStatementItem::SALES_REVENUE_ID){
			return $payload ;
		}
		$newPayload = [];
		foreach($payload as $date=>$value){
			#NOTE:calculate vat for sub item not percentage or cost
			
			$vatable = $this instanceof IncomeStatement && !$isDeductible || $this instanceof CashFlowStatement  && CashFlowStatementItem::CASH_IN_ID == $financialStatementItemAbleId || $this instanceof CashFlowStatement  && $isDeductible    ;
			// $newPayload[$date] = 999;
			$newPayload[$date] = $vatable ?  $value   * $this->calculateVat($vatRate)  : $value ;
			
		}
		return $newPayload;
	}
	public function getMainRowCalculationsArr($incomeStatementItemId,$subItemType,$totalOfAllRows,$totalAtDates,$companyId,$creatorId):array 
	{
		
		return [
			'financial_statement_able_id'=>$this->id ,
			'financial_statement_able_item_id'=>$incomeStatementItemId,
			'payload'=>$totalAtDates,
			'total'=>$totalOfAllRows,
			'sub_item_type'=>$subItemType,
			'company_id'=>$companyId,
			'creator_id'=>$creatorId,
		];
		// 
		
	}
	public function removeMainRowsCalculations(string $subItemType)
	{
		DB::table('financial_statement_able_main_item_calculations')->where('financial_statement_able_id',$this->id)->where('sub_item_type',$subItemType)->delete();
	}
	public function attachMainCalculations(array $mainRows)
	{
		DB::table('financial_statement_able_main_item_calculations')
		->insert($mainRows);
		// ->insert([
		// 	'financial_statement_able_id'=>$this->id ,
		// 	'financial_statement_able_item_id'=>$incomeStatementItemId,
		// 	'payload'=>$totalAtDates,
		// 	'total'=>$totalOfAllRows,
		// 	'sub_item_type'=>$subItemType,
		// 	'company_id'=>$companyId,
		// 	'creator_id'=>$creatorId,
		// ]);
	}
}
