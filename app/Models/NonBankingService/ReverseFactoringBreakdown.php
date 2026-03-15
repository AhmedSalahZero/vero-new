<?php
namespace App\Models\NonBankingService;

use App\Helpers\HArr;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $category
 * @property numeric $margin_rate
 * @property numeric $sensitivity_margin_rate
 * @property float $tenor
 * @property array<array-key, mixed>|null $percentage_payload
 * @property array<array-key, mixed>|null $loan_amounts
 * @property string|null $monthly_loan_amounts
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereMonthlyLoanAmounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown wherePercentagePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereSensitivityMarginRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereTenor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ReverseFactoringBreakdown whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReverseFactoringBreakdown extends Model
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
	
	public static function  getRow(?self $directFactoringBreakdown,array $datesAsIndexes,array $categories)
	{
		return [
			'category'=>$directFactoringBreakdown? $directFactoringBreakdown->getCategory() : $categories[0]['id']  , // first one is the default one
			'tenor'=>$directFactoringBreakdown ? $directFactoringBreakdown->getTenor() : 0 ,
			'margin_rate'=>$directFactoringBreakdown ? $directFactoringBreakdown->getMarginRate()  : 0 ,
			'percentage_payload'=>$directFactoringBreakdown ? $directFactoringBreakdown->getPercentagePayload() : array_fill_keys($datesAsIndexes,0),
			'loan_amounts'=>$directFactoringBreakdown ? $directFactoringBreakdown->getLoanAmountPayload()  : array_fill_keys($datesAsIndexes,0),
		];
	}
	
}
