<?php

namespace App\Models\Trading;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\Tradings\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class CashInOutStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =TRADING_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
                'monthly_cash_and_banks'=>'array',
                'monthly_working_capital_injection'=>'array',
                'monthly_equity_injection'=>'array',
    ];

}
