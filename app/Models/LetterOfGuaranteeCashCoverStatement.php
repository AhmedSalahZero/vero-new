<?php

namespace App\Models;

use App\Helpers\HDate;
use App\Traits\IsBankStatement;
use App\Traits\Models\HasDeleteButTriggerChangeOnLastElement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LetterOfGuaranteeCashCoverStatement extends Model
{
	use HasDeleteButTriggerChangeOnLastElement,IsBankStatement;
    protected $guarded = [
        'id'
    ];

	public static function updateNextRows(LetterOfGuaranteeCashCoverStatement $model):string 
	{
		$minDate  = $model->date ;
	
		
		/**
		 * * ليه بنستخدم ال 
		 * * min date
		 * * علشان لو عدلنا العنصر الحالي وخلينا التاريخ بتاعه اكبر من التاريخ القديم وقتها العناصر اللي ما بين التاريخ مش هيتعدلوا
		 * * مع انهم كان مفروض يتعدلوا بس انت قولتله عدلي العناصر اللي التاريخ بتاعها اكبر من او يساوي التاريخ الجديد
		 * * ودا غلط مفروض التاريخ الاقل ما بين التاريخ الجديد و القديم للعنصر بحيث دايما يبدا يحدث من عنده
		 */

		 DB::table('letter_of_guarantee_cash_cover_statements')
		->where('date','>=',$minDate)
		->orderByRaw('date asc , id asc')
		->where('financial_institution_id',$model->financial_institution_id)
		->where('source',$model->source)
		->where('lg_facility_id',$model->lg_facility_id)
		->where('lg_type',$model->lg_type)
		->each(function($letterOfGuaranteeCashCoverStatement){
			DB::table('letter_of_guarantee_cash_cover_statements')->where('id',$letterOfGuaranteeCashCoverStatement->id)->update([
				'updated_at'=>now()
			]);
		});
		
		return $minDate;

	}
		protected static function booted(): void
		{
			static::creating(function(LetterOfGuaranteeCashCoverStatement $model){
				$model->created_at = now();
				$date = $model->date ;
				$time  = now()->format('H:i:s');
				
				$row = DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','letter_of_guarantee_cash_cover_statements')->first();
				
				if($row){
					$model->id = $row->deleted_id;
					DB::table('temp_deleted_statements')->where('company_id',$model->company_id)->where('table_name','letter_of_guarantee_cash_cover_statements')->delete();
				}
				
				
				$fullDateTime = date('Y-m-d H:i:s', strtotime("$date $time")) ;
				/**
				 * * دي علشان لو ليهم نفس التاريخ والوقت بالظبط يزود ثانيه علي التاريخ القديم
				 */
				$fullDateTime = HDate::generateUniqueDateTimeForModel(self::class,'full_date',$fullDateTime,[
					[
						'company_id','=',$model->company_id ,
					]
				]) ;
				$model->full_date = $fullDateTime;
				
			});
			
			static::created(function(LetterOfGuaranteeCashCoverStatement $model){
				self::updateNextRows($model);
			});
			
			static::updated(function (LetterOfGuaranteeCashCoverStatement $model) {
				
				$minDate = self::updateNextRows($model);
				
				
				$lgFacilityIsChanged = $model->isDirty('lg_facility_id') ;
				$financialInstitutionIsChanged = $model->isDirty('financial_institution_id') ;
				$sourceIsChanged = $model->isDirty('source') ;
				$lgTypeIsChange = $model->isDirty('lg_type') ;
		
				/**
				 * * دي علشان لو غيرت ال
				 * * lg_facility_id
				 * * بمعني انه نقل السحبة مثلا من حساب الي حساب اخر .. يبقي هنحتاج نشغل الترجرز علشان الحساب القديم علشان يوزع تاني
				 */
				if($lgFacilityIsChanged ||$lgTypeIsChange || $financialInstitutionIsChanged || $sourceIsChanged ){
					$oldLgFacilityId=$model->getRawOriginal('lg_facility_id');
					$oldSource=$model->getRawOriginal('source');
					$financialInstitutionId=$model->getRawOriginal('financial_institution_id');
					$oldLgType=$model->getRawOriginal('lg_type');
					$oldStatementId =$model->getRawOriginal('id');
					// لو ما لقناش اول واحد فوقه هندور علي اول واحد بعدة					
					$firstBankStatementForOld = LetterOfGuaranteeCashCoverStatement::
					where('financial_institution_id',$financialInstitutionId)->
					where('lg_facility_id',$oldLgFacilityId)->
					where('source',$oldSource)->
					where('lg_type',$oldLgType)
					->where('id','!=',$oldStatementId )->orderBy('id')->first()  ;
					// لو كانت القديمة دي قبل ما تتغير هي الاستيتم الوحيده بعد كدا انت غيرتها بالتالي الحساب القديم دا معتش ليه لزمة فا هنحذف كل السحبات و التسديدات بتاعته
					if(!$firstBankStatementForOld){
						// وتلقائي هيحذف السحوبات settlements
					}else{
						DB::table('letter_of_guarantee_cash_cover_statements')
						->where('date','>=',$minDate)
						->orderByRaw('date asc , id asc')
						->where('lg_facility_id',$model->lg_facility_id)
						->where('lg_type',$model->lg_type)
						->where('financial_institution_id',$model->financial_institution_id)
						->where('source',$model->source)
						
						->update([
							'updated_at'=>now()
						]);
						
					}
					
				}
				
			});
			
			static::deleting(function(LetterOfGuaranteeCashCoverStatement $letterOfGuaranteeCashCoverStatement){
				$oldDate = null ;
				if($letterOfGuaranteeCashCoverStatement->is_debit && Request('cancellation_date')||$letterOfGuaranteeCashCoverStatement->is_credit && Request('issuance_date')){
						$oldDate = Carbon::make(Request('cancellation_date',Request('issuance_date')))->format('Y-m-d');
						// $time  = now()->format('H:i:s');
						// $oldDate = date('Y-m-d H:i:s', strtotime("$oldDate $time")) ;
						$currentDate = $letterOfGuaranteeCashCoverStatement->date ;
						$letterOfGuaranteeCashCoverStatement->date = min($oldDate,$currentDate);
				}
				$letterOfGuaranteeCashCoverStatement->debit = 0;
				$letterOfGuaranteeCashCoverStatement->credit = 0;
				$letterOfGuaranteeCashCoverStatement->save();
				
			});
		}

    public function moneyReceived()
    {
        return $this->belongsTo(MoneyReceived::class, 'money_received_id', 'id');
    }
	public function moneyPayment()
    {
        return $this->belongsTo(MoneyPayment::class, 'money_payment_id', 'id');
    }
	public function cashExpense()
    {
        return $this->belongsTo(CashExpense::class, 'cash_expense_id', 'id');
    }

    public function getId()
    {
        return $this->id ;
    }
	
	public function getEndBalance()
	{
		return $this->end_balance ?: 0 ;
	}
	public function getEndBalanceFormatted()
	{
		return number_format($this->getEndBalance()) ;
	}

    public function setDateAttribute($value)
    {
        $date = explode('/', $value);
        if (count($date) != 3) {
            $this->attributes['date'] = $value ;

            return ;
        }
        $month = $date[0];
        $day = $date[1];
        $year = $date[2];

        $this->attributes['date'] = $year . '-' . $month . '-' . $day;
    }
	public function getDebitAmount()
	{
		return $this->debit ?: 0 ;
	}
	public function getCurrency()
	{
		return $this->currency ; 
	}
	public function getLetterOfGuaranteeIssuance()
	{
		return $this->belongsTo(LetterOfGuaranteeIssuance::class,'letter_of_guarantee_issuance_id','id');
	} 
	public function getLetterOfGuaranteeFacility()
	{
		return $this->belongsTo(LetterOfGuaranteeIssuance::class,'lg_facility_id','id');
	} 
	
	public function getForeignKeyNamesThatUsedInFilter():array 
	{
		return [
			'lg_facility_id',
			'financial_institution_id',
			'source',
			'lg_type'
		];
	}	
}
