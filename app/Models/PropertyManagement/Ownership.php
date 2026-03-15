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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\Ownership whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ownership extends Model
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
                'name'=>__('Fully Owned')
            ],
            [
                'name'=>__('Owned & Installments')
            ],
            [
                'name'=>__('Right to Use')
            ],
			[
                'name'=>__('Managed')
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
