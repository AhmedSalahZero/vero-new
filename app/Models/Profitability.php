<?php

namespace App\Models;

use App\Models\Traits\Accessors\ProfitabilityAccessor;
use App\Models\Traits\Relations\GeneralExpenseRelation;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $profitabilityAble_type
 * @property int $profitabilityAble_id
 * @property float $percentage
 * @property float $net_profit_after_taxes
 * @property float $vat
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereNetProfitAfterTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereProfitabilityAbleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereProfitabilityAbleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Profitability whereVat($value)
 * @mixin \Eloquent
 */
class Profitability extends Model
{
    use  GeneralExpenseRelation , ProfitabilityAccessor;
    
    protected $guarded = ['id'];
}
