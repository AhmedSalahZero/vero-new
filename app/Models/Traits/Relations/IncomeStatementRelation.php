<?php

namespace App\Models\Traits\Relations;

use App\Models\Traits\Relations\Commons\CommonRelations;

trait IncomeStatementRelation
{
	use CommonRelations, FinancialStatementAbleRelation;
}
