<?php
namespace App\Models\Traits\Accessors ;
trait GeneralExpenseAccessor
{
    public function getId():int
    {
        return $this->id ; 
    }
     public function getCompanyId():int
    {
        return $this->company->id ?? $this->pivot->company_id ; 
    }
    public function getCompanyName():string
    {
        return $this->company->getName() ;
    }
    public function getCreatorName():string
    {
        return $this->creator->name ?? __('N/A');
    }
    
    public function getPercentageOfPrice():float 
    {
		return (float) data_get($this, 'pivot.percentage_of_price', 0);
    }
	public function getName():?string 
    {
		return $this->getExpenseName();

    }
     public function getCostPerUnit():float 
    {
		return (float) data_get($this, 'pivot.cost_per_unit', 0);
    }
     public function getUnitCost():float 
    {
		return (float) data_get($this, 'pivot.unit_cost', 0);
    }
    public function getTotalCost():float 
    {
		return (float) data_get($this, 'pivot.total_cost', 0);
    }

}
