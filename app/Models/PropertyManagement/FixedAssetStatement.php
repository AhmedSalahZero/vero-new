<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;

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
	public function getMonthlyAmounts():array 
	{
		return (array)$this->monthly_amounts;
	}

	public function getMonthlyAmountAtMonthIndex(int $dateAsIndex)
	{
		return $this->getMonthlyAmounts()[$dateAsIndex] ?? 0 ;  
	}
}
