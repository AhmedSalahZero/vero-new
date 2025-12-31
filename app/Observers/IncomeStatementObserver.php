<?php

namespace App\Observers;

use App\Models\IncomeStatement;

class IncomeStatementObserver
{
	public function deleting(IncomeStatement $incomeStatement)
	{
		$incomeStatement->mainRows()->detach();
	}
}
