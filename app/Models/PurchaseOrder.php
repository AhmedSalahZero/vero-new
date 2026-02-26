<?php

namespace App\Models;

use App\Traits\Models\IsOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
	protected $guarded = ['id'];
	
	use IsOrder ;
	
	public function getNumber()
	{
		return $this->po_number;
	}
	public function getName()
	{
		return $this->name ;
	}
	public function letterOfGuaranteeIssuances()
	{
		return $this->hasMany(LetterOfGuaranteeIssuance::class , 'purchase_order_id','id');
	}
	public function scopeOnlyForCompany(Builder $builder , int $companyId)
	{
		return $builder->where('company_id',$companyId);
	}
	public function getOrderColumnName()
	{
		return 'po_number';
	}	
	public function allocations()
	{
		return $this->hasMany(PoAllocation::class,'purchase_order_id','id');
	}
	// public function storeNewAllocation(array $allocations)
	// {
	// 	foreach($allocations as $invoiceId => $allocationsArr){
	// 		foreach($allocationsArr as $index => $allocationArr){
	// 			$partnerId = $allocationArr['partner_id'] ?? 0 ;
	// 			$purchaseOrderId = $allocationArr['purchase_order_id'] ?? 0 ;
	// 			$allocationAmount = number_unformat($allocationArr['allocation_amount'] ?? 0) ;
	// 			$allocationPercentage = number_unformat($allocationArr['allocation_percentage'] ?? 0) ;
	// 			if($allocationAmount>0){
	// 				$this->allocations()->create([
	// 					'allocation_amount'=>$allocationAmount,
	// 					'allocation_percentage'=>$allocationPercentage,
	// 					'purchase_order_id'=>$purchaseOrderId,
	// 					'partner_id'=>$partnerId ,
	// 					'invoice_id'=>$invoiceId
	// 				]);
	// 			}
	// 		}
	// 	}
	// }
	
	
}
