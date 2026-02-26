<?php

namespace App\Models;

use App\Models\Traits\Accessors\OtherVariableManpowerExpenseAccessor;
use App\Models\Traits\Relations\OtherVariableManpowerExpenseRelation;
use App\Traits\HasExpense;
use Illuminate\Database\Eloquent\Model;
class OtherVariableManpowerExpense extends Model
{
    
    use OtherVariableManpowerExpenseRelation , OtherVariableManpowerExpenseAccessor,HasExpense;

    protected $guarded = [];
    
    
}
