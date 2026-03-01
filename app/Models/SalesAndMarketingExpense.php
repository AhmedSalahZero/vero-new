<?php

namespace App\Models;

use App\Models\Traits\Accessors\SalesAndMarketingExpenseAccessor;
use App\Models\Traits\Relations\SalesAndMarketingExpenseRelation;
use App\Traits\HasExpense;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSalesAndMarketingExpense
 */
class SalesAndMarketingExpense extends Model
{
    use   SalesAndMarketingExpenseRelation , SalesAndMarketingExpenseAccessor , HasExpense ;
    
}
