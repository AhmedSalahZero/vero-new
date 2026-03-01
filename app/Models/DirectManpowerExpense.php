<?php

namespace App\Models;

use App\Models\Traits\Accessors\DirectManpowerExpenseAccessor;
use App\Models\Traits\Mutators\DirectManpowerExpenseMutator;
use App\Models\Traits\Relations\DirectManpowerExpenseRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDirectManpowerExpense
 */
class DirectManpowerExpense extends Model
{
    use   DirectManpowerExpenseRelation , DirectManpowerExpenseAccessor , DirectManpowerExpenseMutator;
    
}
