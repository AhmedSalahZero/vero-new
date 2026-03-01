<?php

namespace App\Models;

use App\Models\Traits\Accessors\ServiceNatureAccessor;
use App\Models\Traits\Relations\ServiceNatureRelation;
use App\Models\Traits\Scopes\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperServiceNature
 */
class ServiceNature extends Model
{
    use  ServiceNatureRelation ,ServiceNatureAccessor,BelongsToCompany ;
	
}
