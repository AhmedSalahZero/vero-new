<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CashExpenseCategoryName extends Model
{
	
	protected $guarded = ['id'];
	
	public function cashExpenseCategory()
	{
		return $this->belongsTo(CashExpenseCategory::class,'cash_expense_category_id','id');
	}
	public static function findByOdooChatOfAccountNumber(int $companyId , string $code)
	{
		return self::where('company_id',$companyId)->where('odoo_chart_of_account_number',$code)->first();
	}
	public function getName()
	{
		return $this->name;
	}
	public function cashExpenses()
	{
		return $this->hasMany(CashExpense::class,'cash_expense_category_name_id','id');
	}
	public static function getAllForCompany(Company $company){
		return self::where('company_id',$company->id)->get();
	} 		
	public function getOdooId()
	{
		return $this->odoo_id ;
	}
	public function getOdooChartOfAccountNumber()
	{
		return $this->odoo_chart_of_account_number;
	}
}
