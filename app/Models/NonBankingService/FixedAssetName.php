<?php

namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property int $is_employee_asset
 * @property int $is_branch_asset
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereIsBranchAsset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereIsEmployeeAsset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\FixedAssetName whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FixedAssetName extends Model
{
	use BelongsToStudy,BelongsToCompany,HasBasicStoreRequest;
	protected $table ='fixed_asset_names';
	protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
 	protected $guarded = ['id'];
	const FIXED_ASSET = 'fixed_asset';
	public function getName():string 
	{
		return $this->name ;
	}
	
	//  public static function boot()
	//  {
	// 	 parent::boot();
	// 	 static::saving(function($row){
	// 		$row->is_branch_asset = $row->is_branch_asset[0]??0;
	// 		$row->is_employee_asset = $row->is_employee_asset[0]??0;
	// 	 });
	//  }
	// public function getExpenseType(): string
	// {
	// 	return $this->expense_type;
	// }
	public function isEmployeeAsset():bool
	{
		return (bool)$this->is_employee_asset;
	}
	public function isBranchAsset():bool
	{
		return (bool)$this->is_branch_asset;
	}
	public static function getGeneralAllForSelect2(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->get()->formattedForSelect(false,'id','name');	
	}
	/**
	 * * vuejs
	 */
	public static function getGeneralAllForSelect(Company $company):array 
	{
		return FixedAssetName::where('company_id',$company->id)->get()->map(function($item){
			return [
				'id'=>$item->id,
				'title'=>$item->name
			];
		})->toArray();	
	}
	public static function getPerEmployeeAllForSelect2(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_employee_asset',1)->get()->formattedForSelect(false,'id','name');	
	}
	public static function getPerEmployeeAllForSelect(Company $company):array 
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_employee_asset',1)->get()->map(function($item){
			return [
				'id'=>$item->id,
				'title'=>$item->name
			];
		})->toArray();	
	}
	public static function getNewBranchAllForSelect(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_branch_asset',1)->get()->map(function($item){
			return [
				'id'=>$item->id,
				'title'=>$item->name
			];
		})->toArray();	
	}
	public static function getPerBranchAllForSelect2(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_branch_asset',1)->get()->formattedForSelect(false,'id','name');	
	}
	
}
