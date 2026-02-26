<?php
namespace App\Traits;

use App\Models\PricingExpense;

trait HasExpense
{
	public function expense()
	{
		return $this->belongsTo(PricingExpense::class , 'expense_id','id');
	}
	public function getExpenseName()
	{
		return $this->expense ? $this->expense->name : null ;
	}
}
