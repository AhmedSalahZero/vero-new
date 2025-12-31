<?php

namespace App\Models\Traits\Mutators;

use App\Models\FinancialStatement;
use App\ReadyFunctions\Date;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait FinancialStatementMutator
{

	public function storeMainSection(Request $request)
	{
		$startDate = Carbon::make($request->get('start_from'))->format('Y-m-d');
		$day = explode('-',$startDate)[2];
		$duration = $request->get('duration');
		$dateService = new Date;
		$studyEndDate = Carbon::make($dateService->addMonths($day,$startDate,$duration))->format('Y-m-d');
		$request->merge([
			'study_start_date'=>$startDate,
			'duration_in_years'=>$duration / 12 ,
			'study_end_date'=>$studyEndDate,
			'operation_start_date'=>$startDate,
		]);
		
		
		$data = $request->except(['_token']) ;
		$inUpdateMode = $this->id ;
		$additionalData = [] ;
		if(!$inUpdateMode){
			$additionalData = ['can_view_actual_report'=>0];
		}
		$data = array_merge($data  ,$additionalData );
		return $this->id  ?  $this->update($data) : FinancialStatement::create($data);
	}
	public function storeMainItems(Request $request)
	{
	
		return $this;
	}
	// public function storeReport(Request $request)
	// {
		
	// 	foreach ((array)$request->get('value') as $financialStatementId => $financialStatementItems) {
	// 		$financialStatement = FinancialStatement::find($financialStatementId)->load('subItems');

	// 		foreach ($financialStatementItems as $financialStatementItemId => $values) {
	// 			foreach ($values as $sub_item_origin_name => $payload) {
	// 				if ($financialStatement->subItems()->wherePivot('sub_item_name', $sub_item_origin_name)->where('financial_statement_items.id', $financialStatementItemId)->exists()) {
	// 					$financialStatement->withSubItemsFor($financialStatementItemId, $sub_item_origin_name)
	// 						// ->where('financial_statement_items.id', $financialStatementItemId)
	// 						->wherePivot('sub_item_name', $sub_item_origin_name)
	// 						->updateExistingPivot($financialStatementItemId, [
	// 							'payload' => json_encode($payload)
	// 						]);
	// 				}
	// 			}
	// 		}
	// 	}

	// 	foreach ((array)$request->get('financialStatementItemName') as $financialStatementId => $financialStatementItems) {

	// 		foreach ($values as $sub_item_origin_name => $payload) {
	// 			$financialStatement = FinancialStatement::find($financialStatementId)->load('subItems');

	// 			foreach ($financialStatementItems as $financialStatementItemId => $names) {
	// 				$financialStatement->withSubItemsFor($financialStatementItemId, array_keys($names)[0])
	// 					->updateExistingPivot($financialStatementItemId, [
	// 						'sub_item_name' => html_entity_decode(array_values($names)[0])
	// 					]);
	// 			}
	// 		}
	// 	}
	// 	// store autocaulated values
	// 	foreach ((array)$request->valueMainRowThatHasSubItems as $financialStatementId => $financialStatementItems) {
	// 		$financialStatement = FinancialStatement::find($financialStatementId)->load('mainRows');
	// 		foreach ($financialStatementItems as $financialStatementItemId => $payload) {
	// 			$financialStatement->withMainRowsFor($financialStatementItemId)->detach($financialStatementItemId);
	// 			$financialStatement->withMainRowsFor($financialStatementItemId)->attach($financialStatementItemId, [
	// 				'payload' => json_encode($payload),
	// 				'company_id' => \getCurrentCompanyId(),
	// 				'creator_id' => Auth::id(),
	// 			], false);
	// 		}
	// 	}

	// 	foreach ((array)$request->valueMainRowWithoutSubItems as $financialStatementId => $financialStatementItems) {
	// 		$financialStatement = FinancialStatement::find($financialStatementId)->load('mainRows');
	// 		foreach ($financialStatementItems as $financialStatementItemId => $payload) {
	// 			$financialStatement->withMainRowsFor($financialStatementItemId)->detach($financialStatementItemId);
	// 			$financialStatement->withMainRowsFor($financialStatementItemId)->attach($financialStatementItemId, [
	// 				'payload' => json_encode($payload),
	// 				'company_id' => \getCurrentCompanyId(),
	// 				'creator_id' => Auth::id(),
	// 			], false);
	// 		}
	// 	}
	// }
	public function updateIndexedDates():FinancialStatement
	{
		$datesAsStringAndIndex = $this->getDatesAsStringAndIndex();
		$studyDates = $this->getStudyDates() ;
		$datesAndIndexesHelpers = $this->datesAndIndexesHelpers($studyDates);
		$datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex']; 
		$yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear']; 
		$dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate']; 
		$dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber']; 
		$this->updateStudyAndOperationDates($datesAsStringAndIndex,$datesIndexWithYearIndex,$yearIndexWithYear,$dateIndexWithDate,$dateWithMonthNumber);
		return $this;
	}
}
