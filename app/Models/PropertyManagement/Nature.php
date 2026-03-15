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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Nature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Nature extends Model
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
                'name'=>__('Unit')
            ],
            [
                'name'=>__('Building')
            ],
            [
                'name'=>__('Land')
            ]
        ];
    }
	
	
    public static function createAllForCompany(int $companyId)
    {
        foreach (self::getDefaultForSelects() as $arr) {
            DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('natures')->insert(array_merge(
				$arr,
				['company_id'=>$companyId]
			));
        }
    }
    

}
