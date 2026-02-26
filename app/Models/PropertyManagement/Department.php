<?php

namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\IsDepartment;
use App\Models\Traits\Scopes\PropertyManagements\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
	use BelongsToStudy,BelongsToCompany,IsDepartment,HasBasicStoreRequest;
	protected $table ='departments';
	protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
 	protected $guarded = ['id'];
	const GENERAL = 'general';
	const MICROFINANCE = 'microfinance';
	 public static function boot()
	 {
		 parent::boot();
		 static::deleting(function(self $department){
			$positions = Position::where('department_id',$department->id)->get();
			$positions->each(function(Position $position){
				$position->delete();
			});
		 });
	 }
	 public function positions()
	{
		return $this->hasMany(Position::class,'department_id','id');
	}

	
}
