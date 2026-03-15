<?php

namespace App\Models;

use App\Models\Traits\Accessors\DirectManpowerExpenseAccessor;
use App\Models\Traits\Mutators\DirectManpowerExpenseMutator;
use App\Models\Traits\Relations\DirectManpowerExpenseRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $directManpowerExpensePositions
 * @property-read int|null $direct_manpower_expense_positions_count
 * @property-read bool|null $direct_manpower_expense_positions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\DirectManpowerExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DirectManpowerExpense extends Model
{
    use   DirectManpowerExpenseRelation , DirectManpowerExpenseAccessor , DirectManpowerExpenseMutator;
    
}
