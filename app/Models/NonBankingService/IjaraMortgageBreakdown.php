<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  IjaraMortgageBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'percentage_payload'=>'array',
		'loan_amounts'=>'array',
	];
	
	public function getPercentagePayload():array 
	{
		return (array)$this->percentage_payload;
	}
	public function getPercentageAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->percentage_payload[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getLoanAmountPayload():array 
	{
		return (array)$this->loan_amounts;
	}
	public function getLoanAmountPayloadAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	
	public function getInstallmentInterval()
	{
		return $this->installment_interval;
	}
	public function getMarginRate()
	{
		return $this->margin_rate?:0;
	}
	public function getSensitivityMarginRate():float
	{
		return $this->sensitivity_margin_rate;
	}
	public function getReviewForTable()
	{
		return '-';
	}
	public function getTenor()
	{
		return $this->tenor?:0;
	}
	public function getGracePeriod()
	{
		return $this->grace_period?:0;
	}
	public function getStepUp()
	{
		return 0;
	}
	public function getStepDown()
	{
		return 0;
	}
	public function getStepInterval()
	{
		return 'annually';
	}
	public function getLoanType()
	{
		return 'normal';
	}
	
	public function getLoanNature()
	{
		return 'fixed-at-end';
	}
	
		public function getForeignKeyName():string
	{
		return 'ijara_breakdown_id';
	}	
	public function getCategoryColumnName():?string 
	{
		return 'installment_interval';
	}
	public function getCategoryId()
	{
		$idAndTitleColumnNames = Study::getRevenueStreamCategoryColumnsFor('ijaraMortgageBreakdowns');
		$id = $idAndTitleColumnNames['id'];
		return $this->{$id};
	}
	public function getRevenueType():string 
	{

		return Study::IJARA;
	}
	
	public static function  getRow(?self $ijaraMortgageBreakdown,array $datesAsIndexes)
	{
		return [
			'installment_interval'=>$ijaraMortgageBreakdown? $ijaraMortgageBreakdown->installment_interval : 'monthly'  , // first one is the default one
			'tenor'=>$ijaraMortgageBreakdown ? $ijaraMortgageBreakdown->getTenor() : 0 ,
			'grace_period'=>$ijaraMortgageBreakdown ? $ijaraMortgageBreakdown->getGracePeriod() : 0 ,
			'margin_rate'=>$ijaraMortgageBreakdown ? $ijaraMortgageBreakdown->getMarginRate()  : 0 ,
			'percentage_payload'=>$ijaraMortgageBreakdown ? $ijaraMortgageBreakdown->getPercentagePayload() : array_fill_keys($datesAsIndexes,0),
			'loan_amounts'=>$ijaraMortgageBreakdown ? $ijaraMortgageBreakdown->getLoanAmountPayload()  : array_fill_keys($datesAsIndexes,0),
		];
	}
	
}
