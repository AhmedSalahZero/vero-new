<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperContractCashProjection
 */
class ContractCashProjection extends Model
{
	protected $guarded = [];
	protected $casts = [
		'amounts'=>'array',
	];
}
