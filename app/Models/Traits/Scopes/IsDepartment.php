<?php
namespace App\Models\Traits\Scopes;



trait IsDepartment
{
	public function getName()
	{
		return $this->name ;
	}
	public function getExpenseTypeId():?string
	{
		return $this->positions->count() ? $this->positions->first()->expense_type : null;
	}
	public function getExpenseTypeName():string 
	{
		if(is_null($this->getExpenseTypeId())){
			return '';
		}
		
		return getExpenseTypes()[$this->getExpenseTypeId()];
	}
} 
