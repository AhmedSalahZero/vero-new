<?php

namespace App\Observers;

use App\Models\BalanceSheet;

class BalanceSheetObserver
{
	public function deleting(BalanceSheet $balanceSheet)
	{
		$balanceSheet->mainRows()->detach();
	}
}
