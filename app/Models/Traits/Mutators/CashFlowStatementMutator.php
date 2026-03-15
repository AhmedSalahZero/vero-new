<?php

namespace App\Models\Traits\Mutators;


use App\Models\CashFlowStatementItem;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\ReadyFunctions\CollectionPolicyService;
use Illuminate\Http\Request;

trait CashFlowStatementMutator
{

	use FinancialStatementAbleMutator;

	
}
