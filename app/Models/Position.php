<?php

namespace App\Models;

use App\Models\Traits\Accessors\PositionAccessor;
use App\Models\Traits\Relations\PositionRelation;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
/**
 * @mixin IdeHelperPosition
 */
class Position extends Model
{
	protected $guarded  = [
		'id'
	];
    use  PositionRelation , PositionAccessor,HasCompany;
}
