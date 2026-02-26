<?php

namespace App\Models\Traits\Accessors;

use App\Models\BalanceSheetItem;

trait BalanceSheetAccessor
{
	use FinancialStatementAbleAccessor;

	public function getMainItemTableClassName(): string
	{
		return get_class(new BalanceSheetItem);
	}
}
