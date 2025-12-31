<?php

namespace App\Models;

use App\Http\Controllers\CleanOverdraftController;
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
class CleanOverdraft extends Model implements IHaveStatement
{
    protected $guarded = ['id'];
	
	use HasOutstandingBreakdown , IsOverdraft  , HasBankStatement, HasLastStatementAmount  ;
	
	public function cleanOverdraftBankStatements()
	{
		return $this->hasMany(CleanOverdraftBankStatement::class,'clean_overdraft_id','id');
	}
	public function bankStatements()
	{
		return $this->hasMany(CleanOverdraftBankStatement::class , 'clean_overdraft_id','id');
	}	
	
	public static function generateForeignKeyFormModelName():string 
	{
		return 'clean_overdraft_id';
	}	
	public static function getBankStatementTableName():string 
	{
		return 'clean_overdraft_bank_statements';
	}
	public static function getWithdrawalTableName():string 
	{
		return 'clean_overdraft_withdrawals';
	}
	public static function getBankStatementIdName():string 
	{
		return 'clean_overdraft_bank_statement_id';
	}
	public static function getTableNameFormatted()
	{
		return __('Clean Overdraft');
	}
	public static function getStatementTableName():string
	 {
		return 'clean_overdraft_bank_statements';	
	}
	public  static function getForeignKeyInStatementTable()
	{
		 return 'clean_overdraft_id';
	}
	
	
	public static function getCommonQueryForCashDashboard(Company $company , string $currencyName , string $date )
	{
		return DB::table('clean_overdrafts')
			->where('currency', '=', $currencyName)
			->where('company_id', $company->id)
			->where('contract_start_date', '<=', $date)
			->orderBy('clean_overdrafts.id');
	}
	public static function hasAnyRecord(Company $company,string $currency)
	{
		return DB::table('clean_overdrafts')->where('company_id',$company->id)->where('currency',$currency)->exists();
	}
	public static function getCashDashboardDataForFinancialInstitution(array &$totalRoomForEachCleanOverdraftId,Company $company , array $cleanOverdraftIds , string $currencyName , string $date , int $financialInstitutionBankId , &$totalCleanOverdraftRoom  ):array 
	{
			
				foreach($cleanOverdraftIds as $cleanOverdraftId){
					$cleanOverdraftStatement = DB::table('clean_overdraft_bank_statements')
						->where('clean_overdraft_bank_statements.company_id', $company->id)
						->where('date', '<=', $date)
						->join('clean_overdrafts', 'clean_overdraft_bank_statements.clean_overdraft_id', '=', 'clean_overdrafts.id')
						->where('clean_overdrafts.currency', '=', $currencyName)
						->where('clean_overdraft_id',$cleanOverdraftId)
						->where('financial_institution_id',$financialInstitutionBankId)
						->orderByRaw('date desc , clean_overdraft_bank_statements.id desc')
						->first();
						
						$cleanOverdraftRoom = $cleanOverdraftStatement ? $cleanOverdraftStatement->room : 0 ;
						$totalCleanOverdraftRoom += $cleanOverdraftRoom ;
						$cleanOverdraft = CleanOverdraft::find($cleanOverdraftId);
						$financialInstitution = FinancialInstitution::find($financialInstitutionBankId);
						$financialInstitutionName = $financialInstitution->getName();
						if($cleanOverdraft->financial_institution_id ==$financialInstitution->id ){
							$totalRoomForEachCleanOverdraftId[$currencyName][]  = [
								'item'=>$financialInstitutionName ,
								'available_room'=>$cleanOverdraftRoom,
								'limit'=>$cleanOverdraftStatement  ? $cleanOverdraftStatement->limit : 0 ,
								'end_balance'=>$cleanOverdraftStatement ?  $cleanOverdraftStatement->end_balance : 0 
							] ;
						}
				}
				
				return $totalRoomForEachCleanOverdraftId ;
				
	}
	
	public static function getCashDashboardDataForYear(array &$cleanOverdraftCardData,Builder $cleanOverdraftCardCommonQuery , Company $company , array $cleanOverdraftIds , string $currencyName , string $date , int $year ):array 
	{
				$outstanding = 0 ;
				$room = 0 ;
				$interestAmount = 0 ;
				foreach($cleanOverdraftIds as $cleanOverdraftId){
						$totalRoomForCleanOverdraftId = DB::table('clean_overdraft_bank_statements')
						->where('clean_overdraft_bank_statements.company_id', $company->id)
						->where('date', '<=', $date)
						->join('clean_overdrafts', 'clean_overdraft_bank_statements.clean_overdraft_id', '=', 'clean_overdrafts.id')
						->where('clean_overdrafts.currency', '=', $currencyName)
						->where('clean_overdraft_id',$cleanOverdraftId)
						->orderByRaw('date desc , clean_overdraft_bank_statements.id desc')
						->first();
						$outstanding = $totalRoomForCleanOverdraftId ? $outstanding + $totalRoomForCleanOverdraftId->end_balance : $outstanding ;
						$room = $totalRoomForCleanOverdraftId ? $room + $totalRoomForCleanOverdraftId->room : $room ;
						$interestAmount = $interestAmount +  DB::table('clean_overdraft_bank_statements')
						->where('clean_overdraft_bank_statements.company_id', $company->id)
						->whereRaw('year(date) = '.$year)
						->join('clean_overdrafts', 'clean_overdraft_bank_statements.clean_overdraft_id', '=', 'clean_overdrafts.id')
						->where('clean_overdrafts.currency', '=', $currencyName)
						->where('clean_overdraft_id',$cleanOverdraftId)
						->orderByRaw('date desc , clean_overdraft_bank_statements.id desc')
						->sum('interest_amount');
				}
				$cleanOverdraftCardData[$currencyName] = [
					'limit' =>  $cleanOverdraftCardCommonQuery->sum('limit'),
					'outstanding' => $outstanding,
					'room' => $room ,
					'interest_amount'=>$interestAmount
				];
				return $cleanOverdraftCardData;
	}
	public function getType()
	{
		return __('Clean Overdraft');
	}
	public function getCurrencyFormatted()
	{
		return Str::upper($this->getCurrency());
	}
	
	/**
	 * * for rates
	 */
	
	public function rates()
	{
		return $this->hasMany(CleanOverdraftRate::class,'clean_overdraft_id','id');
	}
	public static function getBankStatementTableClassName():string 
	{
		return CleanOverdraftBankStatement::class ;
	}
	public static function rateFullClassName():string 
	{
		return CleanOverdraftRate::class ;
	}	
	public static function boot()
	{
		parent::boot();
		static::created(function(self $model){
			$model->storeRate(
				Request()->get('balance_date'),
				Request()->get('min_interest_rate'),
				Request()->get('margin_rate'),
				Request()->get('borrowing_rate'),
				Request()->get('interest_rate'),
				$model->company_id
			);
		});
		static::deleting(function(self $model){
			$model->rates()->delete();
			CleanOverdraftBankStatement::deleteButTriggerChangeOnLastElement($model->bankStatements);
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
		$row = $this->cleanOverdraftBankStatements()->where('type','active-limit')->first();
		if($row){
			$row->update($data);
		}else{
			$this->cleanOverdraftBankStatements()->create($data);
		}
		
	}
	
	
}
