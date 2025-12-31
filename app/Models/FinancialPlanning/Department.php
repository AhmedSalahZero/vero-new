<?php

namespace App\Models\FinancialPlanning;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Models\Traits\Scopes\IsDepartment;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
	
	use BelongsToStudy,BelongsToCompany,IsDepartment;
	protected $table ='departments';
	protected $connection =FINANCIAL_PLANNING_CONNECTION_NAME;
 	protected $guarded = ['id'];
	 public static function boot()
	 {
		 parent::boot();
		 static::deleting(function(self $department){
			$department->positions->each(function(Position $position){
				$position->delete();
			});
		 });
	 }
	 public function positions()
	{
		return $this->hasMany(Position::class,'department_id','id');
	}
	
	// public function getDeleteRoute():string
	// {
	// 	return route('delete.single.department',['company'=>$this->company->id,'department'=>$this->id,'study'=>$this->study->id]);
	// }	
}
