<?php

namespace App\Models;

use App\Models\Traits\Accessors\FreelancerExpenseAccessor;
use App\Models\Traits\Relations\FreelancerExpenseRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $freelancerPositions
 * @property-read int|null $freelancer_positions_count
 * @property-read bool|null $freelancer_positions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FreelancerExpense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FreelancerExpense extends Model
{
    use   FreelancerExpenseRelation , FreelancerExpenseAccessor;
    
}
