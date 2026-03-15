<?php

namespace App\Models;

use App\Models\Traits\Accessors\OtherVariableManpowerExpenseAccessor;
use App\Models\Traits\Relations\OtherVariableManpowerExpenseRelation;
use App\Traits\HasExpense;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string|null $expense_id
 * @property string $otherVariableManpowerExpenseAble_type
 * @property int $otherVariableManpowerExpenseAble_id
 * @property float $percentage_of_price
 * @property float $cost_per_unit
 * @property float $unit_cost
 * @property float $total_cost
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PricingExpense|null $expense
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $otherVariableManpowerExpenseAble
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereOtherVariableManpowerExpenseAbleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereOtherVariableManpowerExpenseAbleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense wherePercentageOfPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\OtherVariableManpowerExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OtherVariableManpowerExpense extends Model
{
    
    use OtherVariableManpowerExpenseRelation , OtherVariableManpowerExpenseAccessor,HasExpense;

    protected $guarded = [];
    
    
}
