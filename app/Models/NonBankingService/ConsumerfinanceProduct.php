<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $title
 * @property int $is_active
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ConsumerfinanceProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConsumerfinanceProduct extends Model
{
	// const LEASING_CATEGORY_FORM_ID = 'leasing-category-form';
	use HasBasicStoreRequest,CompanyScope ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	public static function getMainLeasingTypes():array 
	{
		return [
		
		];
	} 
	public function getTitle():string 
	{
		return $this->title;
	}
	public function getName():string
	{
		return $this->getTitle();
	}
	
	
	public static function createAllForCompany(int $companyId):void
	{
		foreach(self::getMainLeasingTypes() as $title ){
			DB::connection('non_banking_service')->table('consumerfinance_products')->insert([
				'company_id'=>$companyId ,
				'title'=>$title 
			]);
		}
		
	}
	public function isActive():bool 
	{
		return (bool)$this->is_active; 
	}	
	
		
}
