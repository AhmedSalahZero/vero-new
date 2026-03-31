<?php

namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetStatement query()
 * @mixin \Eloquent
 */
class FixedAssetStatement extends Model
{
	use BelongsToStudy,BelongsToCompany;
	protected $guarded = ['id'];
	protected $connection ='non_banking_service';
	protected $casts = [
		'beginning_balance'=>'array',
		'additions'=>'array',
		'initial_total_gross'=>'array',
		'replacement_cost'=>'array',
		'final_total_gross'=>'array',
	];
		
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}



}
