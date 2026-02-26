<?php

namespace App\Models;

use App\Helpers\HDate;
use App\Traits\IsBankStatement;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class CleanOverdraftBankStatement extends Model
{
	use IsBankStatement,HasDeleteButTriggerChangeOnLastElement;
	protected $guarded =[
		'id'
	];
	const MONEY_TRANSFER  = 'money-transfer';
	public $oldFullDate = null;
	public static function updateNextRows(self $model):string 
	{
		$minDate  = $model->date ;
		DB::table('clean_overdrafts')->where('id',$model->clean_overdraft_id)->update([
			'oldest_date'=>$minDate,
		]);
		
		/**
		 * * ليه بنستخدم ال 
		 * * min date
		 * * علشان لو عدلنا العنصر الحالي وخلينا التاريخ بتاعه اكبر من التاريخ القديم وقتها العناصر اللي ما بين التاريخ مش هيتعدلوا
		 * * مع انهم كان مفروض يتعدلوا بس انت قولتله عدلي العناصر اللي التاريخ بتاعها اكبر من او يساوي التاريخ الجديد
		 * * ودا غلط مفروض التاريخ الاقل ما بين التاريخ الجديد و القديم للعنصر بحيث دايما يبدا يحدث من عنده
		 */
		$tableName = (new self)->getTable();
		 DB::table($tableName)
		->where('date','>=',$minDate)
		->orderByRaw('date asc , priority asc , id asc')
		->where('clean_overdraft_id',$model->clean_overdraft_id)
		->each(function($cleanOverdraftBankStatement) use($tableName){
			DB::table($tableName)->where('id',$cleanOverdraftBankStatement->id)->update([
				'updated_at'=>now()
			]);
		});
	
		return $minDate;

	}
		protected static function booted(): void
		{
			static::creating(function(self $model){
				$model->created_at = now();
				$date = $model->date ;
				
				
				$row = DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','clean_overdraft_bank_statements')->first();
				
				if($row){
					$model->id = $row->deleted_id;
					DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','clean_overdraft_bank_statements')->delete();
				}
				
				$time  = now()->format('H:i:s');
				$fullDateTime = date('Y-m-d H:i:s', strtotime("$date $time")) ;
				/**
				 * * دي علشان لو ليهم نفس التاريخ والوقت بالظبط يزود ثانيه علي التاريخ القديم
				 */
				$fullDateTime = HDate::generateUniqueDateTimeForModel(self::class,'full_date',$fullDateTime,[
					[
						'clean_overdraft_id','=',$model->clean_overdraft_id ,
					]
				]) ;
				
				
				
				
				$model->full_date = $fullDateTime;
			});
			
			static::created(function(self $model){
				self::updateNextRows($model);
			});
			
			static::updated(function (self $model) {
				$tableName = (new self)->getTable();
				$minDate = self::updateNextRows($model);
				
				
				$isChanged = $model->isDirty('clean_overdraft_id') ;
				/**
				 * * دي علشان لو غيرت ال
				 * * clean_overdraft_id
				 * * بمعني انه نقل السحبة مثلا من حساب الي حساب اخر .. يبقي هنحتاج نشغل الترجرز علشان الحساب القديم علشان يوزع تاني
				 */
				if($isChanged){
					$oldCleanOverdraftId=$model->getRawOriginal('clean_overdraft_id');
					$oldBankStatementId=$model->getRawOriginal('id');
					// لو ما لقناش اول واحد فوقه هندور علي اول واحد بعدة					
					$firstBankStatementForOldCleanOverdraft = self::where('clean_overdraft_id',$oldCleanOverdraftId)->where('id','!=',$oldBankStatementId)->orderBy('id')->first()  ;
					// لو كانت القديمة دي قبل ما تتغير هي الاستيتم الوحيده بعد كدا انت غيرتها بالتالي الحساب القديم دا معتش ليه لزمة فا هنحذف كل السحبات و التسديدات بتاعته
					if(!$firstBankStatementForOldCleanOverdraft){
						CleanOverdraftWithdrawal::where('clean_overdraft_id',$oldCleanOverdraftId)->delete();
						// وتلقائي هيحذف السحوبات settlements
					}else{
						DB::table($tableName)
						->where('date','>=',$minDate)
						->orderByRaw('date asc , priority asc , id asc')
						->where('clean_overdraft_id',$model->clean_overdraft_id)->update([
							'updated_at'=>now()
						]);
						
					}
					
				}
				
			});
			
			static::deleting(function(self $cleanOverdraftBankStatement){
				$oldDate = null ;
				if($cleanOverdraftBankStatement->is_debit && Request('receiving_date')||$cleanOverdraftBankStatement->is_credit && Request('delivery_date')){
						$oldDate = Carbon::make(Request('receiving_date',Request('delivery_date')))->format('Y-m-d');
						// $time  = now()->format('H:i:s');
						// $oldDate = date('Y-m-d H:i:s', strtotime("$oldDate $time")) ;
						$currentDate = $cleanOverdraftBankStatement->date ;
						$cleanOverdraftBankStatement->date = min($oldDate,$currentDate);
				}
				DB::table('clean_overdrafts')->where('id',$cleanOverdraftBankStatement->clean_overdraft_id)->update([
					'oldest_date'=>$cleanOverdraftBankStatement->date,
					// 'origin_update_row_is_debit'=>$cleanOverdraftBankStatement->is_debit
				]);
				
				$cleanOverdraftBankStatement->debit = 0;
				$cleanOverdraftBankStatement->credit = 0;
				$cleanOverdraftBankStatement->save();
				
			});
		}
		
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class,'money_received_id','id');
	}
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id','id');
	}
	public function cashExpense()
	{
		return $this->belongsTo(CashExpense::class,'cash_expense_id','id');
	}
	public function withdrawals()
	{
		return $this->hasMany(CleanOverdraftWithdrawal::class,'clean_overdraft_bank_statement_id','id');
	}
	public function cleanOverdraft()
	{
		return $this->belongsTo(CleanOverdraft::class,'clean_overdraft_id','id');
	}
	public function getId()
	{
		return $this->id ;
	}
	public function setDateAttribute($value)
	{
		$date = explode('/',$value);
		if(count($date) != 3){
			$this->attributes['date'] =  $value ;
			return ;
		}
		$month = $date[0];
		$day = $date[1];
		$year = $date[2];
		
		$this->attributes['date'] = $year.'-'.$month.'-'.$day;
	}
	
	public function internalMoneyTransfer()
	{
		return $this->belongsTo(InternalMoneyTransfer::class,'internal_money_transfer_id','id');
	}
	/**
	 * * دول اسماء العواميد اللي بنفرق باستخدامهم بين كل رو والتاني في الحسابات في التريجر
	 * * يعني مثلا لما اجي اجيب العنصر اللي قبلي هجيبه بناء علي انهي شروط 
	 */
	public function getForeignKeyNamesThatUsedInFilter():array 
	{
		return [
			'clean_overdraft_id'
		];
	}
}
