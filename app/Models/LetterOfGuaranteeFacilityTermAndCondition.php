<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LetterOfGuaranteeFacilityTermAndCondition extends Model
{
    protected $guarded = ['id'];
	public function getLgType()
	{
		return $this->lg_type;
	}
	public function getLgTypeFormatted()
	{
		return camelizeWithSpace($this->getLgType());
	} 
	public function getOutstandingBalance()
	{
		return $this->outstanding_balance ?: 0 ;
	}
	
	public function getOutstandingDateFormatted()
	{
		$outStandingDate = $this->outstanding_date ;
		return $outStandingDate ? Carbon::make($outStandingDate)->format('d-m-Y'):null ;
	}
	
	public function getCashCoverRate()
	{
		return $this->cash_cover_rate ?: 0 ;
	}
	public function getMinCommissionFees()
	{
		return $this->min_commission_fees ?: 0 ;
	}
	public function getIssuanceFees()
	{
		return $this->issuance_fees ?: 0 ;
	}
	public function getCommissionRate()
	{
		return $this->commission_rate ?: 0 ;
	}
	public function getCommissionInterval()
	{
		return $this->commission_interval  ;
	}
	public function letterOfGuaranteeFacility()
	{
		return $this->belongsTo(LetterOfGuaranteeFacility::class , 'letter_of_guarantee_facility_id','id');
	}
}
