<?php

namespace App\Models;

use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveView;
use App\Models\Traits\Accessors\SharingLinkAccessor;
use App\Models\Traits\Mutators\SharingLinkMutator;
use App\Models\Traits\Relations\SharingLinkRelation;
use Illuminate\Database\Eloquent\Model;

class SharingLink  extends Model 
{
	protected $guarded =[
		'id'
	];
}
