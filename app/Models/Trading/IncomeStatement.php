<?php

namespace App\Models\Trading;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\Tradings\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class IncomeStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =TRADING_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'monthly_corporate_taxes_statements'=>'array',
        'accumulated_retained_earnings'=>'array',
        'monthly_net_profit'=>'array',
        'ebit'=>'array',
        'total_depreciation'=>'array',
    ];

}
