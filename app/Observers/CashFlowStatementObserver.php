<?php

namespace App\Observers;

use App\Models\CashFlowStatement;

class CashFlowStatementObserver
{
	public function deleting(CashFlowStatement $cashFlowStatement)
	{
		$cashFlowStatement->mainRows()->detach();
	}
}
