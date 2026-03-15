<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestCashFlowStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestCashFlowStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestCashFlowStatement query()
 * @mixin \Eloquent
 */
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
