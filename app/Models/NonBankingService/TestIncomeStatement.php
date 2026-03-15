<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestIncomeStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestIncomeStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\TestIncomeStatement query()
 * @mixin \Eloquent
 */
class TestIncomeStatement extends Model
{
    
    use BelongsToStudy,BelongsToCompany;
    protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
    protected $guarded = ['id'];
    protected $casts = [
        'revenues'=>'array',
        'oda_interests'=>'array',
        'corporate_taxes'=>'array',
    ];

}
