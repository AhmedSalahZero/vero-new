<?php

namespace App\Models;

use App\Models\Traits\Accessors\OtherDirectOperationExpenseAccessor;
use App\Models\Traits\Relations\OtherDirectOperationExpenseRelation;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherDirectOperationExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OtherDirectOperationExpense extends Model
{
    use   OtherDirectOperationExpenseRelation  , OtherDirectOperationExpenseAccessor,HasExpense;
    
}
