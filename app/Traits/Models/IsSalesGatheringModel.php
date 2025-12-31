<?php
namespace App\Traits\Models;
trait IsSalesGatheringModel
{
	public function getName():string 
	{
		return $this->name;
	}
	public function isNew():bool
	{
		return (bool)$this->is_new;
	}
	public function isExisting():bool
	{
		return (bool)$this->is_existing;
	}
	
}
