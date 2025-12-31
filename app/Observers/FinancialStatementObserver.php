<?php

namespace App\Observers;

use App\Models\FinancialStatement;

class FinancialStatementObserver
{
	public function deleting(FinancialStatement $FinancialStatement)
	{

		$incomeStatement = $FinancialStatement->incomeStatement;
		if ($incomeStatement) {
			$incomeStatement->delete();
		}
		$balanceSheet = $FinancialStatement->balanceSheet;
		if ($balanceSheet) {
			$balanceSheet->delete();
		}
		$cashFlowStatement = $FinancialStatement->cashFlowStatement;
		if ($cashFlowStatement) {
			$cashFlowStatement->delete();
		}
	}

	public function updated(financialStatement $financialStatement)
	{
		$incomeStatement = $financialStatement->incomeStatement;
		$balanceSheet = $financialStatement->balanceSheet;
		$cashFlowStatement = $financialStatement->cashFlowStatement;
		if ($incomeStatement) {
			$incomeStatement->update([
				'name' => generateNameForFinancialStatementRelations($financialStatement->name, $incomeStatement),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}
		if ($balanceSheet) {
			$balanceSheet->update([
				'name' => generateNameForFinancialStatementRelations($financialStatement->name, $balanceSheet),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}

		if ($cashFlowStatement) {

			$cashFlowStatement->update([
				'name' => generateNameForFinancialStatementRelations($financialStatement->name, $cashFlowStatement),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}
	}
}
