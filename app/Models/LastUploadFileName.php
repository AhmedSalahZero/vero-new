<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastUploadFileName extends Model
{
	const CURRENT ='current';
	const SUCCESS ='success';
	protected $guarded = [
		'id'
	];
	public function company()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
}
