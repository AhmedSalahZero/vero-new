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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\UsageStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UsageStatus extends Model
{
    use HasBasicStoreRequest,CompanyScope ;
    protected $connection= 'property_management';
    protected $table='usage_status';
    protected $guarded = ['id'];

    public function getName():string
    {
        return $this->name;
    }
    public static function getDefaultForSelects():array
    {
        return [
            [
                'name'=>__('Vacant')
            ],
            [
                'name'=>__('Leased')
            ],
            [
                'name'=>__('Under Renovation')
            ],
			[
                'name'=>__('Not Ready')
            ],[
                'name'=>__('Under Construction')
            ]
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
    

}
