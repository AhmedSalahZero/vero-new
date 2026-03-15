<?php

namespace App\Models;

use App\Models\Traits\Accessors\PositionAccessor;
use App\Models\Traits\Relations\PositionRelation;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string $name
 * @property string|null $position_type
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $directManpowerExpenseAble
 * @property-read int|null $direct_manpower_expense_able_count
 * @property-read bool|null $direct_manpower_expense_able_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $freelancerExpenseAble
 * @property-read int|null $freelancer_expense_able_count
 * @property-read bool|null $freelancer_expense_able_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position wherePositionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Position whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Position extends Model
{
	protected $guarded  = [
		'id'
	];
    use  PositionRelation , PositionAccessor,HasCompany;
}
