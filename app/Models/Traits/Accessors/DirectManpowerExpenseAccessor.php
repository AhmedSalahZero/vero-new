<?php
namespace App\Models\Traits\Accessors ;
trait DirectManpowerExpenseAccessor
{
    public function getId():int
    {
        return $this->id ; 
    }

  
    public function getCreatorName():string
    {
        return $this->creator->name ?? __('N/A');
    }

    public function getPositionId():int  
    {
		return (int) data_get($this, 'pivot.position_id', 0);
    }
    public function getWorkingDays():float 
    {
		return (float) data_get($this, 'pivot.working_days', 0);
    }
     public function getCostPerDay():float 
    {
		return (float) data_get($this, 'pivot.cost_per_day', 0);
    }
    public function getTotalCost():float 
    {
		return (float) data_get($this, 'pivot.total_cost', 0);
    }

}
