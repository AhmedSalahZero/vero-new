<?php

namespace App\Models;

use App\Models\Traits\Accessors\ProfitabilityAccessor;
use App\Models\Traits\Relations\GeneralExpenseRelation;
use Illuminate\Database\Eloquent\Model;

class Profitability extends Model
{
    use  GeneralExpenseRelation , ProfitabilityAccessor;
    
    protected $guarded = ['id'];
}
