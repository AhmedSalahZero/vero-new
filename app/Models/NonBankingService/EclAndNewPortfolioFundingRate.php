<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  EclAndNewPortfolioFundingRate extends Model
{
	use HasBasicStoreRequest,CompanyScope,BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table = 'ecl_and_new_portfolio_funding_rates';
	protected $guarded = ['id'];
	protected $casts = [
		'admin_fees_rates'=>'array',
		'monthly_admin_fees_amounts'=>'array',
		'ecl_rates'=>'array',
		'monthly_ecl_values'=>'array',
		'accumulated_ecl_values'=>'array',
		'equity_funding_rates'=>'array',
		'equity_funding_values'=>'array',
		'new_loans_funding_rates'=>'array',
		'new_loans_funding_values'=>'array',
		'monthly_new_loans_funding_values'=>'array',
		'monthly_new_odas_funding_values'=>'array',
	];
	public function getRevenueStreamType():string 
	{
		return $this->revenue_stream_type;
	}
	public function getAdminFeesRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->admin_fees_rates[$yearOrMonthIndex]??0;
	}
	public function getMonthlyAdminFeesAmountsAtMonthIndex(int $monthIndex)
	{
		return $this->monthly_admin_fees_amounts[$monthIndex] ?? 0  ; 
	}
	public function getEclRatesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->ecl_rates[$yearOrMonthIndex]??0;
	}
	public function getEquityFundingRatesAtYearOrMonthIndex(int $yearOrMonthAsIndex , $microfinanceFundedBy= null )
	{
		if($microfinanceFundedBy){
			return $this->equity_funding_rates[$microfinanceFundedBy][$yearOrMonthAsIndex]??0;
		}
		return $this->equity_funding_rates[$yearOrMonthAsIndex]??0;
	}
	public function getEquityFundingValuesAtYearOrMonthIndex(int $yearOrMonthIndex,$microfinanceFundedBy= null)
	{
		if($microfinanceFundedBy){
			return $this->equity_funding_values[$microfinanceFundedBy][$yearOrMonthIndex]??0;
		}
		if($this->isDirectFactoring()){
			$total = $this->study->getTotalDirectFactoringNewPortfolioAmountsAtYearOrMonthIndex($yearOrMonthIndex)['sum'];
			$rate = $this->getEquityFundingRatesAtYearOrMonthIndex($yearOrMonthIndex);
			return $total * $rate/100;
		}
		return $this->equity_funding_values[$yearOrMonthIndex]??0;
	}
	public function isDirectFactoring()
	{
		return $this->revenue_stream_type==Study::DIRECT_FACTORING;
	}
	public function getNewLoansFundingRatesAtYearOrMonthIndex(int $yearOrMonthIndex,$microfinanceFundedBy= null)
	{
		return 100-$this->getEquityFundingRatesAtYearOrMonthIndex($yearOrMonthIndex,$microfinanceFundedBy);
		// if($microfinanceFundedBy){
		// 	return $this->new_loans_funding_rates[$microfinanceFundedBy][$yearOrMonthIndex]??0;
		// }
		// return $this->new_loans_funding_rates[$yearOrMonthIndex]??0;
	}
	public function getNewLoansFundingValuesAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		if($this->isDirectFactoring()){
			$total = $this->study->getTotalDirectFactoringNewPortfolioAmountsAtYearOrMonthIndex($yearOrMonthIndex)['sum'];
			$rate = $this->getNewLoansFundingRatesAtYearOrMonthIndex($yearOrMonthIndex);
			return $total * $rate/100;
		}
		return $this->new_loans_funding_values[$yearOrMonthIndex]??0;
	}
}
