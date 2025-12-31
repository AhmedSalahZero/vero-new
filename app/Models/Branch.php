<?php

namespace App\Models;

use App\Traits\HasOdooPaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Branch extends Model
{
	use HasOdooPaymentMethod;
	protected $table ='branch';
	protected $guarded = ['id'];
	public function getName()
	{
		return $this->name;
	}
	public function getBranch()
	{
		return $this->belongsTo(Company::class , 'company_id','id');
	}
	public function creator()
	{
		return $this->belongsTo(User::class , 'creator_id','id');
	}
	public static function getBranchesForCurrentCompany(int $companyId){
		return Branch::where('company_id',$companyId)->pluck('name','id')->toArray();
	}
	public function cashInSafeStatements()
	{
		return $this->hasMany(CashInSafeStatement::class,'branch_id','id');
	}
	public static function storeHeadOffice(int $companyId)
	{
		self::create([
			'company_id'=>$companyId,
			'name'=>'Head Office',
			'odoo_id'=>7
		]);
	}
	public function getOdooId():int 
	{
		if(is_null($this->odoo_id)){
			throw new \Exception('Odoo Code For Branch ' . $this->getName() . ' Not Found');
		}
		return $this->odoo_id;
	}
	public function getJournalId():int 
	{
		return $this->journal_id ;
	}
	public static function getIdFromOdooCode(int $companyId , string $code)
	{
		return self::where('company_id',$companyId)->where('odoo_code',$code)->first()->id;
	}
	public static function getNameFromOdooId(int $companyId , int $odooId)
	{
		return self::where('company_id',$companyId)->where('odoo_id',$odooId)->first()->name;
	}
	public function getCurrentEndBalance(int $companyId,?string $currency,$deliveryDate = null ):float
	{
		if(is_null($currency)){
			return 0;
		}
		$cashInSafeStatement = DB::table('cash_in_safe_statements')
		->where('company_id',$companyId)
		->where('currency',$currency)
		->where('branch_id',$this->id)
		->when($deliveryDate , function($q) use($deliveryDate){
			$q->where('date','<=',$deliveryDate);
		})
		->orderByRaw('date desc , id desc')
		->first();
		if(!$cashInSafeStatement){
			return 0 ;
		}
		return $cashInSafeStatement->end_balance;
	}
	
}
