<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperIncomeStatement
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
