<?php

namespace App\Models;

use App\Helpers\HDate;
use App\Traits\IsBankStatement;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class OverdraftAgainstCommercialPaperBankStatement extends Model
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
		DB::table('overdraft_against_commercial_papers')->where('id',$model->overdraft_against_commercial_paper_id)->update([
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
		->where('overdraft_against_commercial_paper_id',$model->overdraft_against_commercial_paper_id)
		->each(function($overdraftAgainstCommercialPaperBankStatement) use($tableName){
			DB::table($tableName)->where('id',$overdraftAgainstCommercialPaperBankStatement->id) 
			->update([
				'updated_at'=>now(),
				// 'credit'=>0 
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
				
				$row = DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','overdraft_against_commercial_paper_bank_statements')->first();
				
				if($row){
					$model->id = $row->deleted_id;
					DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','overdraft_against_commercial_paper_bank_statements')->delete();
				}
				
				/**
				 * * دي علشان لو ليهم نفس التاريخ والوقت بالظبط يزود ثانيه علي التاريخ القديم
				 */
				$fullDateTime = HDate::generateUniqueDateTimeForModel(self::class,'full_date',$fullDateTime,[
					[
						'overdraft_against_commercial_paper_id','=',$model->overdraft_against_commercial_paper_id ,
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
				
				
				$isChanged = $model->isDirty('overdraft_against_commercial_paper_id') ;
				/**
				 * * دي علشان لو غيرت ال
				 * * overdraft_against_commercial_paper_id
				 * * بمعني انه نقل السحبة مثلا من حساب الي حساب اخر .. يبقي هنحتاج نشغل الترجرز علشان الحساب القديم علشان يوزع تاني
				 */
				if($isChanged){
					$oldOverdraftAgainstCommercialPaperId=$model->getRawOriginal('overdraft_against_commercial_paper_id');
					$oldBankStatementId=$model->getRawOriginal('id');
					// لو ما لقناش اول واحد فوقه هندور علي اول واحد بعدة					
					$firstBankStatementForOldOverdraftAgainstCommercialPaper = self::where('overdraft_against_commercial_paper_id',$oldOverdraftAgainstCommercialPaperId)->where('id','!=',$oldBankStatementId)->orderBy('id')->first()  ;
					// لو كانت القديمة دي قبل ما تتغير هي الاستيتم الوحيده بعد كدا انت غيرتها بالتالي الحساب القديم دا معتش ليه لزمة فا هنحذف كل السحبات و التسديدات بتاعته
					if(!$firstBankStatementForOldOverdraftAgainstCommercialPaper){
						OverdraftAgainstCommercialPaperWithdrawal::where('overdraft_against_commercial_paper_id',$oldOverdraftAgainstCommercialPaperId)->delete();
						// وتلقائي هيحذف السحوبات settlements
					}else{
						DB::table($tableName)
						->where('date','>=',$minDate)
						->orderByRaw('date asc , priority asc , id asc')
						->where('overdraft_against_commercial_paper_id',$model->overdraft_against_commercial_paper_id)->update([
							'updated_at'=>now()
						]);
						
					}
					
				}
				
			});
			
			static::deleting(function(self $overdraftAgainstCommercialPaperBankStatement){
				$oldDate = null ;
				if($overdraftAgainstCommercialPaperBankStatement->is_debit && Request('receiving_date')||$overdraftAgainstCommercialPaperBankStatement->is_credit && Request('delivery_date')){
						$oldDate = Carbon::make(Request('receiving_date',Request('delivery_date')))->format('Y-m-d');
						// $time  = now()->format('H:i:s');
						// $oldDate = date('Y-m-d H:i:s', strtotime("$oldDate $time")) ;
						$currentDate = $overdraftAgainstCommercialPaperBankStatement->date ;
						$overdraftAgainstCommercialPaperBankStatement->date = min($oldDate,$currentDate);
				}
				DB::table('overdraft_against_commercial_papers')->where('id',$overdraftAgainstCommercialPaperBankStatement->overdraft_against_commercial_paper_id)->update([
					'oldest_date'=>$overdraftAgainstCommercialPaperBankStatement->full_date
				]);
				
				$overdraftAgainstCommercialPaperBankStatement->debit = 0;
				$overdraftAgainstCommercialPaperBankStatement->credit = 0;
				$overdraftAgainstCommercialPaperBankStatement->save();
				
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
		return $this->hasMany(OverdraftAgainstCommercialPaperWithdrawal::class,'overdraft_against_commercial_paper_bank_statement_id','id');
	}
	public function overdraftAgainstCommercialPaper()
	{
		return $this->belongsTo(OverdraftAgainstCommercialPaper::class,'overdraft_against_commercial_paper_id','id');
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
	public function getForeignKeyNamesThatUsedInFilter():array 
	{
		return [
			'overdraft_against_commercial_paper_id',
		];
	}	
		
}
