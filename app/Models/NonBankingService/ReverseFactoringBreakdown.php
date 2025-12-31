<?php
namespace App\Models\NonBankingService;

use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  ReverseFactoringBreakdown extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'percentage_payload'=>'array',
		'loan_amounts'=>'array',
	];
	public function getPercentageAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->percentage_payload[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getLoanAmountPayloadAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	public function getCategory()
	{
		return $this->category;
	}
	public function getMarginRate()
	{
		return $this->margin_rate?:0;
	}
	public function getSensitivityMarginRate():float
	{
		return $this->sensitivity_margin_rate;
	}
	public function getTenor()
	{
		return $this->tenor?:0;
	}
	public function getLoanType()
	{
		return 'normal';
	}
		
	public function getReviewForTable()
	{
		/**
		 * ! Need To Be Fixed
		 */
		if(is_numeric($this->category)){
			return '-';
		}
		$category = $this->category ;
		return HArr::getTitleFromValueArray(reverseFactoringSelector(),$category);
	}
		public function getForeignKeyName():string
	{
		return 'reverse_breakdown_id';
	}	
	public function getCategoryColumnName():?string 
	{
		return 'category';
	}
	public function getRevenueType():string 
	{
		return Study::REVERSE_FACTORING;
	}
	
}
