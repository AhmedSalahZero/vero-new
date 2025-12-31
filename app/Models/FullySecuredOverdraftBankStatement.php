<?php

namespace App\Models;

use App\Helpers\HDate;
use App\Traits\IsBankStatement;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class FullySecuredOverdraftBankStatement extends Model
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
		
		DB::table('fully_secured_overdrafts')->where('id',$model->fully_secured_overdraft_id)->update([
			'oldest_date'=>$minDate,
			// 'origin_update_row_is_debit'=>$model->is_debit  
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
		->where('fully_secured_overdraft_id',$model->fully_secured_overdraft_id)
		->each(function($fullySecuredOverdraftBankStatement) use($tableName){
			DB::table($tableName)->where('id',$fullySecuredOverdraftBankStatement->id)->update([
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
				$time  = now()->format('H:i:s');
				$fullDateTime = date('Y-m-d H:i:s', strtotime("$date $time")) ;
				
				
				$row = DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','fully_secured_overdraft_bank_statements')->first();
				
				if($row){
					$model->id = $row->deleted_id;
					DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','fully_secured_overdraft_bank_statements')->delete();
				}
				
				/**
				 * * دي علشان لو ليهم نفس التاريخ والوقت بالظبط يزود ثانيه علي التاريخ القديم
				 */
				$fullDateTime = HDate::generateUniqueDateTimeForModel(self::class,'full_date',$fullDateTime,[
					[
						'fully_secured_overdraft_id','=',$model->fully_secured_overdraft_id ,
					]
				]) ;
				$model->full_date = $fullDateTime;
			});
			
			static::created(function(self $model){
				self::updateNextRows($model);
			});
			
			static::updated(function (self $model) {
				
				$minDate = self::updateNextRows($model);
				
				
				$isChanged = $model->isDirty('fully_secured_overdraft_id') ;
				/**
				 * * دي علشان لو غيرت ال
				 * * fully_secured_overdraft_id
				 * * بمعني انه نقل السحبة مثلا من حساب الي حساب اخر .. يبقي هنحتاج نشغل الترجرز علشان الحساب القديم علشان يوزع تاني
				 */
				if($isChanged){
					$oldFullySecuredOverdraftId=$model->getRawOriginal('fully_secured_overdraft_id');
					$oldBankStatementId=$model->getRawOriginal('id');
					// لو ما لقناش اول واحد فوقه هندور علي اول واحد بعدة					
					$firstBankStatementForOldFullySecuredOverdraft = self::where('fully_secured_overdraft_id',$oldFullySecuredOverdraftId)->where('id','!=',$oldBankStatementId)->orderBy('id')->first()  ;
					// لو كانت القديمة دي قبل ما تتغير هي الاستيتم الوحيده بعد كدا انت غيرتها بالتالي الحساب القديم دا معتش ليه لزمة فا هنحذف كل السحبات و التسديدات بتاعته
					if(!$firstBankStatementForOldFullySecuredOverdraft){
						FullySecuredOverdraftWithdrawal::where('fully_secured_overdraft_id',$oldFullySecuredOverdraftId)->delete();
						// وتلقائي هيحذف السحوبات settlements
					}else{
						DB::table((new self)->getTable())
						->where('full_date','>=',$minDate)
						->orderByRaw('full_date asc , priority asc , id asc')
						->where('fully_secured_overdraft_id',$model->fully_secured_overdraft_id)->update([
							'updated_at'=>now()
						]);
						
					}
					
				}
				
			});
			
			static::deleting(function(self $fullySecuredOverdraftBankStatement){
				$oldDate = null ;
				if($fullySecuredOverdraftBankStatement->is_debit && Request('receiving_date')||$fullySecuredOverdraftBankStatement->is_credit && Request('delivery_date')){
						$oldDate = Carbon::make(Request('receiving_date',Request('delivery_date')))->format('Y-m-d');
						// $time  = now()->format('H:i:s');
						// $oldDate = date('Y-m-d H:i:s', strtotime("$oldDate $time")) ;
						$currentDate = $fullySecuredOverdraftBankStatement->date ;
						$fullySecuredOverdraftBankStatement->date = min($oldDate,$currentDate);
				}
				DB::table('fully_secured_overdrafts')->where('id',$fullySecuredOverdraftBankStatement->fully_secured_overdraft_id)->update([
					'oldest_date'=>$fullySecuredOverdraftBankStatement->date,
					// 'origin_update_row_is_debit'=>$fullySecuredOverdraftBankStatement->is_debit
				]);
				
				$fullySecuredOverdraftBankStatement->debit = 0;
				$fullySecuredOverdraftBankStatement->credit = 0;
				$fullySecuredOverdraftBankStatement->save();
				
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
		return $this->hasMany(FullySecuredOverdraftWithdrawal::class,'fully_secured_overdraft_bank_statement_id','id');
	}
	public function fullySecuredOverdraft()
	{
		return $this->belongsTo(FullySecuredOverdraft::class,'fully_secured_overdraft_id','id');
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
	public function getForeignKeyNamesThatUsedInFilter():array 
	{
		return [
			'fully_secured_overdraft_id'
		];
	}		
	
}
