<?php

namespace App\Models;

use App\Interfaces\Models\Interfaces\IHaveStatement;
use App\Traits\HasBankStatement;
use App\Traits\HasLastStatementAmount;
use App\Traits\HasOutstandingBreakdown;
use App\Traits\IsOverdraft;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * * هو نوع من انواع حسابات التسهيل البنكية (زي القرض يعني بس فية فرق بينهم ) وبيسمى حد جاري مدين بدون ضمان
 * * بدون ضمان يعني مش بياخدوا مقابل قصادة يعني مثلا مش بياخدوا منك شيكات مثلا او بيت .. الخ علشان كدا اسمه كلين
 * * والفرق بينه وبين القرض ان هنا انت مش ملتزم تسدد مبلغ معين في فتره معين اي لا  يوجد اقساط للدفع
 * * وبناء عليه كل اما قللت التسديد كل اما هينزل عليك فايدة اكبر الشهر الجاي
 * * وعموما في حالة انك مدان للبنك وليكن مثلا لو انت سالف من البنك عشر الالف وسحبت تسعه ونزل عليك فايدة خمس مئة جنية
 * * وقتها ال خمس مئة جنية دول بينسحبوا من حسابك علطول وبالتالي انت ما عتش فاضلك غير خمس مئة مثلا
 */
class FullySecuredOverdraft extends Model implements IHaveStatement
{
    protected $guarded = ['id'];
	use HasOutstandingBreakdown , IsOverdraft ,HasBankStatement, HasLastStatementAmount;
	
	public function fullySecuredOverdraftBankStatements()
	{
		return $this->hasMany(FullySecuredOverdraftBankStatement::class,'fully_secured_overdraft_id','id');
	}
	public function bankStatements()
	{
		return $this->hasMany(FullySecuredOverdraftBankStatement::class , 'fully_secured_overdraft_id','id');
	}	
	
	public static function generateForeignKeyFormModelName()
	{
		return 'fully_secured_overdraft_id';
	}	
	public static function getBankStatementTableName()
	{
		return 'fully_secured_overdraft_bank_statements';
	}
	public static function getWithdrawalTableName()
	{
		return 'fully_secured_overdraft_withdrawals';
	}
	public static function getBankStatementIdName():string 
	{
		return 'fully_secured_overdraft_bank_statement_id';
	}
	public static function getTableNameFormatted()
	{
		return __('Fully Secured Overdraft');
	}
	public function internalMoneyTransfer()
	{
		return $this->belongsTo(InternalMoneyTransfer::class,'internal_money_transfer_id','id');
	}	
	public function cdOrTdAccountType()
	{
		return $this->belongsTo(AccountType::class,'cd_or_td_account_type_id','id');
	}
	public function getCdOrTdAccountTypeId()
	{
		return $this->cdOrTdAccountType ? $this->cdOrTdAccountType->id : 0 ; 
	}
	
	public function getCdOrTdId()
	{
		return $this->cd_or_td_account_id;
	}
	public static function getStatementTableName():string
	 {
		return 'fully_secured_overdraft_bank_statements';	
	}
	public static function getForeignKeyInStatementTable()
	{
		 return 'fully_secured_overdraft_id';
	}
	
	public static function getCommonQueryForCashDashboard(Company $company , string $currencyName , string $date )
	{
		return DB::table('fully_secured_overdrafts')
			->where('currency', '=', $currencyName)
			->where('company_id', $company->id)
			->where('contract_start_date', '<=', $date)
			->orderBy('fully_secured_overdrafts.id');
	}
	public static function hasAnyRecord(Company $company,string $currency)
	{
		return DB::table('fully_secured_overdrafts')->where('company_id',$company->id)
		->where('currency',$currency)
		->exists();
	}
	public static function getCashDashboardDataForFinancialInstitution(array &$totalRoomForEachFullySecuredOverdraftId,Company $company , array $fullySecuredOverdraftIds , string $currencyName , string $date , int $financialInstitutionBankId , &$totalFullySecuredOverdraftRoom  ):array 
	{
			
				foreach($fullySecuredOverdraftIds as $fullySecuredOverdraftId){
					$fullySecuredOverdraftStatement = DB::table('fully_secured_overdraft_bank_statements')
						->where('fully_secured_overdraft_bank_statements.company_id', $company->id)
						->where('date', '<=', $date)
						->join('fully_secured_overdrafts', 'fully_secured_overdraft_bank_statements.fully_secured_overdraft_id', '=', 'fully_secured_overdrafts.id')
						->where('fully_secured_overdrafts.currency', '=', $currencyName)
						->where('fully_secured_overdraft_id',$fullySecuredOverdraftId)
						->where('financial_institution_id',$financialInstitutionBankId)
						->orderByRaw('date desc , fully_secured_overdraft_bank_statements.id desc')
						->first();
						
						$fullySecuredOverdraftRoom = $fullySecuredOverdraftStatement ? $fullySecuredOverdraftStatement->room : 0 ;
						$totalFullySecuredOverdraftRoom += $fullySecuredOverdraftRoom ;
						$fullySecuredOverdraft = FullySecuredOverdraft::find($fullySecuredOverdraftId);
						$financialInstitution = FinancialInstitution::find($financialInstitutionBankId);
						$financialInstitutionName = $financialInstitution->getName();
						if($fullySecuredOverdraft->financial_institution_id ==$financialInstitution->id ){
							$totalRoomForEachFullySecuredOverdraftId[$currencyName][]  = [
								'item'=>$financialInstitutionName ,
								'available_room'=>$fullySecuredOverdraftRoom,
								'limit'=>$fullySecuredOverdraftStatement  ? $fullySecuredOverdraftStatement->limit : 0 ,
								'end_balance'=>$fullySecuredOverdraftStatement ?  $fullySecuredOverdraftStatement->end_balance : 0 
							] ;
						}
				}
				
				return $totalRoomForEachFullySecuredOverdraftId ;
				
	}
	
	public static function getCashDashboardDataForYear(array &$fullySecuredOverdraftCardData,Builder $fullySecuredOverdraftCardCommonQuery , Company $company , array $fullySecuredOverdraftIds , string $currencyName , string $date , int $year ):array 
	{
				$outstanding = 0 ;
				$room = 0 ;
				$interestAmount = 0 ;
				foreach($fullySecuredOverdraftIds as $fullySecuredOverdraftId){
						$totalRoomForFullySecuredOverdraftId = DB::table('fully_secured_overdraft_bank_statements')
						->where('fully_secured_overdraft_bank_statements.company_id', $company->id)
						->where('date', '<=', $date)
						->join('fully_secured_overdrafts', 'fully_secured_overdraft_bank_statements.fully_secured_overdraft_id', '=', 'fully_secured_overdrafts.id')
						->where('fully_secured_overdrafts.currency', '=', $currencyName)
						->where('fully_secured_overdraft_id',$fullySecuredOverdraftId)
						->orderByRaw('date desc , fully_secured_overdraft_bank_statements.id desc')
						->first();
						$outstanding = $totalRoomForFullySecuredOverdraftId ? $outstanding + $totalRoomForFullySecuredOverdraftId->end_balance : $outstanding ;
						$room = $totalRoomForFullySecuredOverdraftId ? $room + $totalRoomForFullySecuredOverdraftId->room : $room ;
						$interestAmount = $interestAmount +  DB::table('fully_secured_overdraft_bank_statements')
						->where('fully_secured_overdraft_bank_statements.company_id', $company->id)
						->whereRaw('year(date) = '.$year)
						->join('fully_secured_overdrafts', 'fully_secured_overdraft_bank_statements.fully_secured_overdraft_id', '=', 'fully_secured_overdrafts.id')
						->where('fully_secured_overdrafts.currency', '=', $currencyName)
						->where('fully_secured_overdraft_id',$fullySecuredOverdraftId)
						->orderByRaw('date desc , fully_secured_overdraft_bank_statements.id desc')
						->sum('interest_amount');
				}
				$fullySecuredOverdraftCardData[$currencyName] = [
					'limit' =>  $fullySecuredOverdraftCardCommonQuery->sum('limit'),
					'outstanding' => $outstanding,
					'room' => $room ,
					'interest_amount'=>$interestAmount
				];
				return $fullySecuredOverdraftCardData;
	}
	
	public function getType()
	{
		return __('Fully Secured Overdraft');
	}	
	public function getCurrencyFormatted()
	{
		return Str::upper($this->getCurrency());
	}
	
	public function rates()
	{
		return $this->hasMany(FullySecuredOverdraftRate::class,'fully_secured_overdraft_id','id');
	}
	public static function getBankStatementTableClassName():string 
	{
		return FullySecuredOverdraftBankStatement::class ;
	}		
	public static function rateFullClassName():string 
	{
		return FullySecuredOverdraftRate::class ;
	}
	public static function boot()
	{
		parent::boot();
		static::created(function(self $model){
			$model->storeRate(
				Request()->get('balance_date'),
				Request()->get('min_interest_rate',0),
				Request()->get('margin_rate'),
				Request()->get('borrowing_rate'),
				Request()->get('interest_rate'),
				$model->company_id
			);
		});
		static::deleting(function(self $model){
			$model->rates()->delete();
			FullySecuredOverdraftBankStatement::deleteButTriggerChangeOnLastElement($model->bankStatements);
		});
	}
	public function company()
	{
		return $this->belongsTo(Company::class,'company_id');
	}
	public function updateLimitRaw()
	{
		$data = [
			'type'=>'active-limit',
			'is_debit'=>1 ,
			'is_credit'=> 0 ,
			'priority'=>3,
			'company_id'=>$this->company->id ,
			'date'=>$this->contract_start_date ,
			'limit'=>$this->limit ,
			'debit'=>0,
			'credit'=>0,
			'comment_en'=>__('Limit'),
			'comment_ar'=>__('Limit',[],'ar'),
		];
		$row = $this->fullySecuredOverdraftBankStatements()->where('type','active-limit')->first();
		if($row){
			$row->update($data);
		}else{
			$this->fullySecuredOverdraftBankStatements()->create($data);
		}
		
	}
}
