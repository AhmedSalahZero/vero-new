<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCashInOutStatement
 */
class CashInOutStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'monthly_cash_and_banks'=>'array',
                'monthly_working_capital_injection'=>'array',
                'monthly_equity_injection'=>'array',
    ];

}
