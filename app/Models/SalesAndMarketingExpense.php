<?php

namespace App\Models;

use App\Models\Traits\Accessors\SalesAndMarketingExpenseAccessor;
use App\Models\Traits\Relations\SalesAndMarketingExpenseRelation;
use App\Traits\HasExpense;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $expense_id
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PricingExpense|null $expense
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesAndMarketingExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalesAndMarketingExpense extends Model
{
    use   SalesAndMarketingExpenseRelation , SalesAndMarketingExpenseAccessor , HasExpense ;
    
}
