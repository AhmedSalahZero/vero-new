<?php

namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

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
	public static function getPerEmployeeAllForSelect2(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_employee_asset',1)->get()->formattedForSelect(false,'id','name');	
	}
	public static function getPerBranchAllForSelect2(Company $company)
	{
		return FixedAssetName::where('company_id',$company->id)->where('is_branch_asset',1)->get()->formattedForSelect(false,'id','name');	
	}
}
