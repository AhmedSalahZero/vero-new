<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\IsRevenueStream;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  RevenueContract extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy , IsRevenueStream;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	protected $casts =[
		'monthly_loan_amounts'=>'array',
		'contract_counts'=>'array',
	];
	public function category()
	{
		return $this->belongsTo(LeasingCategory::class,'category_id',) ;
	}
	public function getReviewForTable()
	{
	
		return $this->category->getTitle().'[' . $this->getLoanNature() . ' / ' . $this->getLoanType(). ' / ' . $this->getTenor(). ' M/ ' . $this->getGracePeriod(). ' M/ ' . $this->getMarginRate(). ' %/ ' . $this->getInstallmentInterval(). ' / ' . $this->getStepRate(). ' %/ ' . $this->getStepInterval() . ' ]';
	}
	public function getMonthlyLoanAmountAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->monthly_loan_amounts[$yearOrMonthIndex] ?? 0  ; 
	}
	
		
}
