<?php

namespace App\Models\Traits\Mutators;


use App\Models\CashFlowStatementItem;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\ReadyFunctions\CollectionPolicyService;
use Illuminate\Http\Request;

trait CashFlowStatementMutator
{

	use FinancialStatementAbleMutator;

	// public function getCashFlowStatementItemIdFromIncomeStatementItemId(int $incomeStatementItemId, bool $isFinancialIncome = false)
	// {
	// 	if ($incomeStatementItemId === IncomeStatementItem::SALES_REVENUE_ID || $isFinancialIncome) {
	// 		return CashFlowStatementItem::CASH_IN_ID;
	// 	}
	// 	return CashFlowStatementItem::CASH_OUT_ID;
	// }
	// public function subItemCollectionPolicy(int $incomeStatementId, int $incomeStatementItemId, string $subItemType, string $subItemName): array
	// {
	// 	$incomeStatement = IncomeStatement::find($incomeStatementId);
	// 	$subItem =  $incomeStatement->withSubItemsFor($incomeStatementItemId, $subItemType, $subItemName)->first();
	// 	if (!$subItem) {
	// 		return [
	// 			'has_collection_policy' => 1,
	// 			'collection_policy_type' => '',
	// 			'collection_policy_value' => ''
	// 		];
	// 	}
	// 	$collection_policy_value = $subItem->pivot->collection_policy_value;
	// 	return [
	// 		'has_collection_policy' => $subItem->pivot->has_collection_policy,
	// 		'collection_policy_type' => $collection_policy_type = $subItem->pivot->collection_policy_type,
	// 		'collection_policy_value' => $collection_policy_type == 'customize' ? (array)json_decode($collection_policy_value) : $collection_policy_value
	// 	];
	// }
	// public function formatSubItems(Request $request)
	// {

	// 	$values = [];
	// 	$subItems = (array)$request->sub_items; // when in add or edit modal (mode)
	// 	if ((bool)$request->get('in_add_or_edit_modal')) {
	// 		foreach ($subItems as $index => $subItemOptions) {
	// 			if (isset($subItemOptions['name'])) {
	// 				$incomeStatementId = $request->get('financial_statement_able_id');
	// 				$cashFlowStatementId = $request->get('cash_flow_statement_id');
	// 				$incomeStatementItemId = $request->get('financial_statement_able_item_id');
	// 				$cashFlowStatementItemId = $this->getCashFlowStatementItemIdFromIncomeStatementItemId($incomeStatementItemId, isset($subItemOptions['is_financial_income']));

	// 				$subItemName = $subItemOptions['name'];
	// 				$collectionPolicyType = $subItemOptions['collection_policy']['type']['name']??null ;
					
	// 				if(is_array($request->value[$incomeStatementId][$incomeStatementItemId][$subItemName])){
	// 					$values[$cashFlowStatementId][$cashFlowStatementItemId][$subItemName] = (new CollectionPolicyService())->applyCollectionPolicy(isset($subItemOptions['collection_policy']['has_collection_policy']) && (bool)$subItemOptions['collection_policy']['has_collection_policy'], $collectionPolicyType , $subItemOptions['collection_policy']['type'][$collectionPolicyType]['value'] ?? '', $request->value[$incomeStatementId][$incomeStatementItemId][$subItemName]);
	// 				}else{
	// 					$values[$cashFlowStatementId][$cashFlowStatementItemId][$subItemName] = [];
						
	// 				}
	// 			}
	// 		}
	// 	}

	// 	if (count((array)$request->value)) // in submit form 
	// 	{

	// 		$cashFlowStatementId = $request->get('cash_flow_statement_id');
	// 		$subItemType = $request->get('sub_item_type');
	// 		foreach ((array)$request->value as $incomeStatementId => $incomeStatementItemIdAndSubItems) {
	// 			foreach ($incomeStatementItemIdAndSubItems as $incomeStatementItemId => $subItemsNamesAndValues) {
	// 				foreach ($subItemsNamesAndValues as $subItemName => $dateValue) {
	// 					$cashFlowStatementItemId = $this->getCashFlowStatementItemIdFromIncomeStatementItemId($incomeStatementItemId, $request->get('is_financial_income')[$incomeStatementId][$incomeStatementItemId][$subItemName] ?? false);
	// 					$collection_policy = $this->subItemCollectionPolicy($incomeStatementId, $incomeStatementItemId, $subItemType, $subItemName);
	// 					if(is_array($dateValue)){
	// 						$values[$cashFlowStatementId][$cashFlowStatementItemId][$subItemName] = (new CollectionPolicyService())->applyCollectionPolicy($collection_policy['has_collection_policy'], $collection_policy['collection_policy_type'], $collection_policy['collection_policy_value'], $dateValue);
	// 					}
	// 					else{
	// 						$values[$cashFlowStatementId][$cashFlowStatementItemId][$subItemName] = [];
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return $values;
	// }
	// public function formatTotalForMainItem(array $array): array
	// {
	// 	$result  = [
	// 		'main_rows' => [],
	// 		'total' => []
	// 	];
	// 	foreach ($array as $cashFlowStatementId => $items) {
	// 		foreach ($items as $cashFlowStatementItemId => $subItemsWithDateValues) {
	// 			foreach ($subItemsWithDateValues as $subItemName => $dateValues) {
	// 				foreach ($dateValues as $date => $value) {
	// 					$oldValue = $result['main_rows'][$cashFlowStatementId][$cashFlowStatementItemId][$date] ?? 0;
	// 					$result['main_rows'][$cashFlowStatementId][$cashFlowStatementItemId][$date] = $oldValue + $value;
	// 				}
	// 				$result['total'][$cashFlowStatementId][$cashFlowStatementItemId] = array_sum($result['main_rows'][$cashFlowStatementId][$cashFlowStatementItemId] ?? []);
	// 			}
	// 		}
	// 	}
	// 	return $result;
	// }
	// public function replaceIncomeStatementItemIdWithCashFlowStatementId(array $items,  $financialIncomeOrExpense): array
	// {
	// 	$dataFormatted = [];
	// 	$isFinancialIncome = false;
	// 	foreach ($items as $incomeStatementItemId => $oldNamesAndNewNames) {
	// 		foreach ($oldNamesAndNewNames as $oldName => $newName) {
	// 			if (is_array($financialIncomeOrExpense)) {
	// 				$isFinancialIncome =   $financialIncomeOrExpense[$incomeStatementItemId][$oldName] ?? false;
	// 			} else {
	// 				$isFinancialIncome = $financialIncomeOrExpense;
	// 			}
	// 			$cashFlowStatementItemId = $this->getCashFlowStatementItemIdFromIncomeStatementItemId($incomeStatementItemId, $isFinancialIncome);
	// 			$dataFormatted[$cashFlowStatementItemId][$oldName] = $newName;
	// 		}
	// 	}
	// 	return $dataFormatted;
	// }
	// public function formatDataFromIncomeStatement(Request $request): array
	// {
	// 	$cashFlowStatementId = $request->get('cash_flow_statement_id');
	// 	$incomeStatementId = $request->get('income_statement_id');
	// 	$values = $this->formatSubItems($request);

	// 	$mainRowValuesWithTotal = $this->formatTotalForMainItem($values);
	// 	$isFinancialIncome = false;
	// 	if (is_array($request->get('is_financial_income'))) {
	// 		$isFinancialIncome = $request->get('is_financial_income')[$incomeStatementId] ?? [];
	// 	} else {
	// 		$isFinancialIncome = $request->get('is_financial_income');
	// 	}
	// 	$financialStatementAbleItemName[$cashFlowStatementId] = $this->replaceIncomeStatementItemIdWithCashFlowStatementId($request->financialStatementAbleItemName[$incomeStatementId], $isFinancialIncome);

	// 	return [
	// 		'value' => $values,
	// 		'valueMainRowThatHasSubItems' => $mainRowValuesWithTotal['main_rows'],
	// 		'totals' => $mainRowValuesWithTotal['total'],
	// 		'financialStatementAbleItemName' => $financialStatementAbleItemName
	// 	];
	// }
}
