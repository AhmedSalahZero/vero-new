<?php

namespace App\Observers;

use App\Models\BalanceSheet;
use App\Models\CashFlowStatement;
use App\Models\FinancialStatement;
use App\Models\IncomeStatement;

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
				'name' => $this->generateNameForFinancialStatementRelations($financialStatement->name, $incomeStatement),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}
		if ($balanceSheet) {
			$balanceSheet->update([
				'name' => $this->generateNameForFinancialStatementRelations($financialStatement->name, $balanceSheet),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}

		if ($cashFlowStatement) {

			$cashFlowStatement->update([
				'name' => $this->generateNameForFinancialStatementRelations($financialStatement->name, $cashFlowStatement),
				'duration' => $financialStatement->duration,
				'duration_type' => $financialStatement->duration_type,
				'start_from' => $financialStatement->start_from
			]);
		}
	}
	
protected function generateNameForFinancialStatementRelations(string $financialStatementName, $relationObject)
{
    if ($relationObject instanceof IncomeStatement) {
        return $financialStatementName . ' Income Statement';
    }
    if ($relationObject instanceof CashFlowStatement) {
        return $financialStatementName . ' Cash Flow Statement';
    }
    if ($relationObject instanceof BalanceSheet) {
        return $financialStatementName . ' Balance Sheet';
    }

    throw new \Exception('Can Not Generate Name For ' . $financialStatementName . ' Only Allowed [ Income Statement , Cash Flow And Balance Sheet ] Objects');
}
}
