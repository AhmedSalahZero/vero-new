<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterestRevenueAccount extends Model
{
	// use HasOdooPaymentMethod;
	protected $guarded = ['id'];
	public function getOdooId():int 
	{
		if(is_null($this->odoo_id)){
			throw new \Exception('Odoo Code For Branch ' . $this->getName() . ' Not Found');
		}
		return $this->odoo_id;
	}
	public function financialInstitution()
	{
		return $this->belongsTo(FinancialInstitution::class,'financial_institution_id','id');
	}
	public function getFinancialInstitutionId()
	{
		return $this->financial_institution_id;
	}
	public function getOdooCode()
	{
		return $this->odoo_code ;
	}
	// public function getJournalId():int 
	// {
	// 	return $this->journal_id ;
	// }
	
}
