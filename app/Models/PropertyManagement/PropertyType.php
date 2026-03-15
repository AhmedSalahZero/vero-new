<?php
namespace App\Models\PropertyManagement;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property int|null $category_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PropertyManagement\PropertyType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PropertyType extends Model
{
    use HasBasicStoreRequest,CompanyScope ;
    protected $connection= 'property_management';
    
    protected $guarded = ['id'];

    public function getName():string
    {
        return $this->name;
    }
    public static function getDefaultForSelects(int $companyId):array
    {
        $propertyCategories = Category::where('company_id',$companyId)->orderBy('id','asc')->get();
		$results = [];
		foreach($propertyCategories as $index=>$propertyCategory){
			$propertyTypes = [
				['Apartment','Villa','Duplex','Penthouse','Studio','Townhouse','Chalet'],
				['Shop','Showroom','Kiosk','Restaurant','Café','Supermarket','Logistics Hub','Mall Unit','Service Center','Storage Room','Warehouse'],
				['Office','Co-Working Space','Meeting Room','Training Room'],
				['Clinic','Polyclinic','Pharmacy','Laboratory','Radiology Center','Dental Clinic','Daycare Medical Center'],
				['Warehouse','Factory','Workshop','Logistics Hub','Production Facility'],
				['Residential Land','Commercial Land','Industrial Land','Agricultural Land','Administrative Land','Medical Land']
			][$index]??[];
			foreach($propertyTypes as $propertyTypeName){
				$results[] = [
					'name'=>$propertyTypeName,
					'category_id'=>$propertyCategory->id,
					'company_id'=>$companyId
				];
			}
			
		}
		return $results;
		
		
		
    }
	
	
    public static function createAllForCompany(int $companyId)
    {
        foreach (self::getDefaultForSelects($companyId) as $arr) {
            DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table((new self)->getTable())->insert($arr);
        }
		
    }
    

}
