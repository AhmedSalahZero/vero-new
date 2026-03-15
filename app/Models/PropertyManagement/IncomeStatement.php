<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $accumulated_retained_earnings
 * @property array<array-key, mixed>|null $monthly_corporate_taxes_statements
 * @property array<array-key, mixed>|null $monthly_net_profit
 * @property int $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $ebit
 * @property array<array-key, mixed>|null $total_depreciation
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereAccumulatedRetainedEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereEbit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereMonthlyCorporateTaxesStatements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereMonthlyNetProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereTotalDepreciation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\IncomeStatement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'monthly_corporate_taxes_statements'=>'array',
        'accumulated_retained_earnings'=>'array',
        'monthly_net_profit'=>'array',
        'ebit'=>'array',
        'total_depreciation'=>'array',
    ];

}
