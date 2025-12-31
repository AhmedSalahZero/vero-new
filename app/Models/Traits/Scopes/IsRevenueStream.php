<?php
namespace App\Models\Traits\Scopes;



trait IsRevenueStream 
{
	public function getCategoryId():int
	{
		return $this->category_id;
	}	
	public function getLoanNature():string 
	{
		return $this->loan_nature ;
	}
	public function getLoanType():string 
	{
		return $this->loan_type;
	}
	public function getTenor():int
	{
		return $this->tenor;
	}
	public function getGracePeriod():int
	{
		return $this->grace_period;
	}
	public function getMarginRate():float
	{
		return $this->margin_rate;
	}
	public function getSensitivityMarginRate():float
	{
		return $this->sensitivity_margin_rate;
	}
	public function getInstallmentInterval():string 
	{
		return $this->installment_interval;
	}
	public function getStepRate():float 
	{
		return $this->getStepUp() ? $this->getStepUp()  : $this->getStepDown();
	}
	public function getStepUp()
	{
		return $this->step_up ?: 0 ;
	}
	public function getStepDown()
	{
		return $this->step_down ?: 0 ;
	}
	public function getStepInterval():?string 
	{
		return $this->step_interval;
	}
} 
