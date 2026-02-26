<?php

namespace App\Models;

use App\Traits\Models\IsCashInSafe;
use Illuminate\Database\Eloquent\Model;

class CashInSafe extends Model
{
	use IsCashInSafe;
    protected $guarded = ['id'];
	
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class,'money_received_id');
	}
	
}
