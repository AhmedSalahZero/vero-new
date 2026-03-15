<?php
namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PropertyManagement\PropertyType> $types
 * @property-read int|null $types_count
 * @property-read bool|null $types_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    use HasBasicStoreRequest,CompanyScope ;
    protected $connection= 'property_management';
    
    protected $guarded = ['id'];

    public function getName():string
    {
        return $this->name;
    }
    public static function getDefaultForSelects():array
    {
        return [
            [
                'name'=>__('Residential')
            ],
            [
                'name'=>__('Commercial')
            ],
            [
                'name'=>__('Administrative')
            ],
			[
                'name'=>__('Medical')
            ],[
                'name'=>__('Industrial')
            ],[
                'name'=>__('Land')
            ],
			
        ];
    }
	
	
    public static function createAllForCompany(int $companyId)
    {
        foreach (self::getDefaultForSelects() as $arr) {
            DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table((new self)->getTable())->insert(array_merge(
				$arr,
				['company_id'=>$companyId]
			));
        }
    }
    
	public function types()
	{
		return $this->hasMany(PropertyType::class, 'category_id');
	}

}
