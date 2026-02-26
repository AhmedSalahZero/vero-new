<?php
namespace App\Models\Trading;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Nature extends Model
{
    use HasBasicStoreRequest,CompanyScope ;
    protected $connection=TRADING_CONNECTION_NAME;
    
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
            DB::connection(TRADING_CONNECTION_NAME)->table('natures')->insert(array_merge(
				$arr,
				['company_id'=>$companyId]
			));
        }
    }
    

}
