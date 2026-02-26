<?php

namespace App\Models\Traits\Accessors;

use App\Models\IncomeStatementItem;

trait IncomeStatementAccessor
{
	use FinancialStatementAbleAccessor;

	public function getMainItemTableClassName(): string
	{
		return get_class(new IncomeStatementItem);
	}
}
