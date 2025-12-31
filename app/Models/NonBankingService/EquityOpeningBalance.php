<?php
namespace App\Models\NonBankingService;


use App\Helpers\HArr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquityOpeningBalance extends Model
{
    protected $guarded = ['id'];
	protected $connection= 'non_banking_service';
	public static function booted()
	{
			parent::boot();
			static::saving(function(self $model){
					$studyMonthsForViews = array_flip($model->study->getOperationDatesAsDateAndDateAsIndexToStudyEndDate());
     			   	$sumKeys = array_keys($studyMonthsForViews);
					$model->paid_up_capital_extended = HArr::repeatThrough($model->paid_up_capital_amount?:0,$sumKeys);
					$model->legal_reserve_extended = HArr::repeatThrough($model->legal_reserve?:0,$sumKeys);
			});
	}
	
	protected $casts = [
		'payload'=>'array',
		'statement'=>'array',
		'paid_up_capital_extended'=>'array',
		'legal_reserve_extended'=>'array',
	];
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	
    public function getPaidUpCapitalAmount():float 
    {
        return $this->paid_up_capital_amount ;
    } 
	public function getExtendedPaidUpCapitalAmount():array 
    {
        return $this->paid_up_capital_extended?:[] ;
    } 
	public function getLegalReserveAmount():float 
    {
        return $this->legal_reserve ;
    }
	public function getExtendedLegalReserveAmount():array 
    {
        return $this->legal_reserve_extended?:[] ;
    } 
	public function getRetainedEarningAmount():float 
    {
        return $this->retained_earnings ;
    }
	public function getTotalShareholdersEquity():float
	{
		return $this->getRetainedEarningAmount() + $this->getLegalReserveAmount() + $this->getPaidUpCapitalAmount();
	}
}
