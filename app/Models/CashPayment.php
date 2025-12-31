<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * * هو عباره عن الكاش اللي بدفعه للمورد
 */
class CashPayment extends Model
{

    protected $guarded = ['id'];
	
	public function moneyPayment()
	{
		return $this->belongsTo(MoneyPayment::class,'money_payment_id');
	}
	public function getBankOdooId():?int
	{
		$branch = $this->deliveryBranch;
		return $branch ? $branch->odoo_id : 0 ;
	}
	public function getBankJournalId():?int
	{
		$branch = $this->deliveryBranch;
		return $branch ? $branch->journal_id : 0 ;
	}
	public function deliveryBranch(){
		return $this->belongsTo(Branch::class,'delivery_branch_id','id');
	}
	public function getDeliveryBranchId()
	{
		$branch = $this->deliveryBranch;
		return $branch ? $branch->id : 0 ;
	}
	public function getDeliveryBranchName()
	{
		$branch = $this->deliveryBranch;
		return $branch ? $branch->getName() : 0 ;
	}
	public function getReceiptNumber()
	{
		return $this->receipt_number ;
	}
	
}
