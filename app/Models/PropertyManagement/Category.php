<?php
namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
