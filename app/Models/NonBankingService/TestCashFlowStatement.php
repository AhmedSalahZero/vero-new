<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

class TestCashFlowStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
	protected $table = 'test_cashflow_statements';
    protected $casts = [
        'cash_in'=>'array',
        'cash_out'=>'array',
        'oda_interests'=>'array',
        'corporate_taxes_payments'=>'array',
    ];

}
