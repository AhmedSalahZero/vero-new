<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractCashProjection extends Model
{
	protected $guarded = [];
	protected $casts = [
		'amounts'=>'array',
	];
}
