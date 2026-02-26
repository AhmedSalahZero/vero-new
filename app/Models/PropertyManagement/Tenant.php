<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

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
