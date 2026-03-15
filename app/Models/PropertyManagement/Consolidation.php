<?php

namespace App\Models\PropertyManagement;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property string $name
 * @property array<array-key, mixed>|null $study_ids studies that will be consolidated
 * @property string $study_type
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereStudyIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereStudyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Consolidation whereUpdatedAt($value)
 * @mixin \Eloquent
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
