<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\PropertyManagement\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\FixedAssetStatement query()
 * @mixin \Eloquent
 */
class FixedAssetStatement extends Model
{
	use BelongsToStudy,BelongsToCompany;
	protected $guarded = ['id'];
	protected $connection ='property_management';
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
