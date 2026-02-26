<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashProjection extends Model
{
	protected $guarded = [];
	protected $casts = [
		'amounts'=>'array',
	];
}
