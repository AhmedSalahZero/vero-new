<?php

namespace App\Models\Traits\Relations;

use App\Models\CashFlowStatementItem;
use App\Models\FinancialStatement;
use App\Models\Traits\Relations\Commons\CommonRelations;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait CashFlowStatementRelation
{
	use CommonRelations, FinancialStatementAbleRelation;
}
