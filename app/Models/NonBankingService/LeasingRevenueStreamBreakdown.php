<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\IsRevenueStream;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  LeasingRevenueStreamBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy , IsRevenueStream;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'loan_amounts'=>'array',
		'monthly_loan_amounts'=>'array',
	];
	// protected static function boot()
	// {
	// 	 parent::boot();
	// 	 static::saving(function($q){
	// 		dd($q);
	// 	 });
	// }
	public function category()
	{
		return $this->belongsTo(LeasingCategory::class,'category_id',) ;
	}
	
	public function getReviewForTable()
	{
		if(!$this->category){
			return '---';
		}
	
		return $this->category->getTitle().'[' . $this->getLoanNature() . ' / ' . $this->getLoanType(). ' / ' . $this->getTenor(). ' M/ ' . $this->getGracePeriod(). ' M/ ' . $this->getMarginRate(). ' %/ ' . $this->getInstallmentInterval(). ' / ' . $this->getStepRate(). ' %/ ' . $this->getStepInterval() . ' ]';
	}
	public function getLoanAmountAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getForeignKeyName():string
	{
		return 'leasing_breakdown_id';
	}	
	public function getCategoryColumnName():string 
	{
		return 'category_id';
	}
	public function getRevenueType():string 
	{
		return Study::LEASING;
	}
	public static function getRow(?self $model,Study $study)
	{
		return [
				'id'=>$model ? $model->id : 0,
				'category_id'=>$model ? $model->getCategoryId() : '',
				'loan_nature'=>$model? $model->getLoanNature() : 'fixed-at-end',
				'loan_type'=>$model? $model->getLoanType() : 'normal',
				'tenor'=>$model ? $model->getTenor():12,
				'grace_period'=>$model? $model->getGracePeriod():0,
				'margin_rate'=>$model ? $model->getMarginRate() : 0,
				'installment_interval'=>$model ? $model->getInstallmentInterval():'monthly',
				'step_rate'=>$model ? $model->getStepRate() : 0,
				'step_interval'=>$model? $model->getStepInterval():'quarterly',
				'company_id'=>$study->company->id,
				'study_id'=>$study->id
		];	
	}
}
