<?php

namespace App\Models;

use App\Models\Traits\Accessors\FreelancerExpenseAccessor;
use App\Models\Traits\Relations\FreelancerExpenseRelation;
use Illuminate\Database\Eloquent\Model;

class FreelancerExpense extends Model
{
    use   FreelancerExpenseRelation , FreelancerExpenseAccessor;
    
}
