<?php

namespace App\Models;

use App\Models\LetterOfGuaranteeFacilityTermAndCondition;
use App\Traits\Models\HasLetterOfGuaranteeCashCoverStatements;
use App\Traits\Models\HasLetterOfGuaranteeStatements;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LetterOfGuaranteeFacility extends Model
{
	use HasLetterOfGuaranteeStatements , HasLetterOfGuaranteeCashCoverStatements;
    
	protected $guarded = ['id'];
	public function getName()
	{
		return $this->name;
	}	
	public function getContractStartDate()
	{
		return $this->contract_start_date;
	}
	public function getContractStartDateFormatted()
	{
		$contractStartDate = $this->contract_start_date ;
		return $contractStartDate ? Carbon::make($contractStartDate)->format('d-m-Y'):null ;
	}
	public function getContractEndDate()
	{
		return $this->contract_end_date;
	}
	public function getContractEndDateFormatted()
	{
		$contractEndDate = $this->getContractEndDate() ;
		return $contractEndDate ? Carbon::make($contractEndDate)->format('d-m-Y'):null ;
	}

	public function getOutstandingDate()
	{
		return $this->outstanding_date;
	}
	public function getOutstandingDateFormatted()
	{
		$outstandingDate = $this->getOutstandingDate() ;
		return $outstandingDate ? Carbon::make($outstandingDate)->format('d-m-Y'):null ;
	}

	public function getLimit()
	{
		return $this->limit ?: 0 ;
	}

	public function getLimitFormatted()
	{
		return number_format($this->getLimit()) ;
	}
	public function getOutstandingAmount()
	{
		return $this->outstanding_amount ?: 0 ;
	}

	public function getOutstandingAmountFormatted()
	{
		return number_format($this->getOutstandingAmount()) ;
	}

	public function getCurrency()
	{
		return $this->currency ;
	}
	public function financialInstitution()
	{
		return $this->belongsTo(FinancialInstitution::class , 'financial_institution_id','id');
	}
	public function termAndConditions()
	{
		return $this->hasMany(LetterOfGuaranteeFacilityTermAndCondition::class , 'letter_of_guarantee_facility_id','id');
	}
    public function termAndConditionForLgType(string $lgType){
        return $this->termAndConditions->where('lg_type',$lgType)->first();
    }
	public function letterOfGuaranteeStatements()
	{
		return $this->hasMany(LetterOfGuaranteeStatement::class,'lg_facility_id','id');
	}
	public function letterOfGuaranteeCashCoverStatements()
	{
		return $this->hasMany(LetterOfGuaranteeCashCoverStatement::class,'lg_facility_id','id');
	}

}
