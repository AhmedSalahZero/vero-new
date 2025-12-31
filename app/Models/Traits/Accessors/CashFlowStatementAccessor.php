<?php

namespace App\Models\Traits\Accessors;

use App\Models\CashFlowStatement;
use App\Models\CashFlowStatementItem;

trait CashFlowStatementAccessor
{
	use FinancialStatementAbleAccessor;

	public function getMainItemTableClassName(): string
	{
		return get_class(new CashFlowStatementItem);
	}
}
