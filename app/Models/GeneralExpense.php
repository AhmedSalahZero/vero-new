<?php

namespace App\Models;

use App\Models\Traits\Accessors\GeneralExpenseAccessor;
use App\Models\Traits\Relations\GeneralExpenseRelation;
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\GeneralExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralExpense extends Model
{
    use  GeneralExpenseRelation , GeneralExpenseAccessor,HasExpense;
    
}
