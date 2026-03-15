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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\MicrofinanceProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MicrofinanceProduct extends Model
{
	// const LEASING_CATEGORY_FORM_ID = 'leasing-category-form';
	use HasBasicStoreRequest,CompanyScope ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	public static function getMainLeasingTypes():array 
	{
		return [
			// 'real-estate'=>'Real Estate',
			// 'machine-equipment'=>'Machines & Equipment',
			// 'commercial-trucks'=>'Commercial Trucks',
			// 'production-lines'=>'Production Lines',
			// 'private-cars'=>'Private Cars',
			// 'office-equipment'=>'Office Equipment',
			// 'intangible-assets'=>'Intangible Assets',
			// 'heavy-equipment'=>'Heavy Equipment',
			// 'vessels'=>'Vessels',
			// 'others'=>'Others',
			// 'airplanes'=>'Airplanes',
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
			DB::connection('non_banking_service')->table('microfinance_products')->insert([
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
