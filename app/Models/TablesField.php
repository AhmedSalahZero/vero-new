<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TablesField extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
	
	
	protected static function booted()
    {
		#REMEMBER:Only For LabelingItem Model Uploading
		if(in_array('LabelingItem',Request()->segments())){
			static::addGlobalScope('LabelingItem', function (Builder $builder) {
				$builder->where('company_id', getCurrentCompanyId());
			});	
		}
        
    }
}
