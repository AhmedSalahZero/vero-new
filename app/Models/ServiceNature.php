<?php

namespace App\Models;

use App\Models\Traits\Accessors\ServiceNatureAccessor;
use App\Models\Traits\Relations\ServiceNatureRelation;
use App\Models\Traits\Scopes\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationPricingCalculator> $QuotationPricingCalculators
 * @property-read int|null $quotation_pricing_calculators_count
 * @property-read bool|null $quotation_pricing_calculators_exists
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculator
 * @property-read int|null $quick_pricing_calculator_count
 * @property-read bool|null $quick_pricing_calculator_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceNature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceNature extends Model
{
    use  ServiceNatureRelation ,ServiceNatureAccessor,BelongsToCompany ;
	
}
