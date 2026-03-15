<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string $nature individual, corporate
 * @property string|null $business_sector
 * @property string $related_party yes or no
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereBusinessSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereRelatedParty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Tenant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tenant extends Model
{
	use BelongsToCompany,HasBasicStoreRequest;
	protected $table ='tenants';
	protected $connection =PROPERTY_MANAGEMENT_CONNECTION_NAME;
 	protected $guarded = ['id'];
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}
	public function getName():string|null
	{
		return $this->name;
	}
	public function getNature():string|null
	{
		return $this->nature;
	}
	public function getBusinessSector():string|null
	{
		return $this->business_sector;
	}
	public function getRelatedParty():string|null
	{
		return $this->related_party;
	}
	

	

	
}
