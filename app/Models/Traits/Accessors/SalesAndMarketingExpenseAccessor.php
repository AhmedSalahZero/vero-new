<?php
namespace App\Models\Traits\Accessors ;
trait SalesAndMarketingExpenseAccessor
{
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
