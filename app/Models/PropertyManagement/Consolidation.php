<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;


/**
 * @mixin IdeHelperConsolidation
 */
class Consolidation extends Model
{
    use BelongsToCompany;
    protected $guarded = ['id'];
    protected $connection ='property_management';
    protected $casts = [
        'study_ids'=>'array',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
	public function getName():string 
	{
		return $this->name;
	}
}
