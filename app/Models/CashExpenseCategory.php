<?php

namespace App\Models;

use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class CashExpenseCategory extends Model
{
	use HasBasicStoreRequest ;
	protected $guarded = ['id'];
	public function getId()
	{
		return $this->id ;
	}
	
	public function getName()
	{
		return $this->name ;
	}
	public function cashExpenseCategoryNames()
	{
		return $this->hasMany(CashExpenseCategoryName::class,'cash_expense_category_id','id');
	}
}
