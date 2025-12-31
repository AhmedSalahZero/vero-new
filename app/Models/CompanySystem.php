<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CompanySystem extends Model
{
	// protected $table = 'company_system';
	protected $guarded = [
		'id'
	];
	
	public static function getAllSystemNames()
	{
		return [
			VERO ,
			CASH_VERO ,
			NON_BANKING_SERVICE ,
			EXPORT_ANALYSIS,
			EXPENSE_ANALYSIS,
			PRICING_CALCULATOR,
			SALES_FORECAST,
			INCOME_STATEMENT_PLANNING,
			LABELING
		];
	}
	public function company()
	{
		return $this->belongsTo(Company::class,'company_id','id');
	}
	public function getName():string 
	{
		return $this->system_name ;
	}
}
