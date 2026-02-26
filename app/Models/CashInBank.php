<?php

namespace App\Models;

use App\Traits\Models\IsCashInBank;
use Illuminate\Database\Eloquent\Model;

class CashInBank extends Model
{
	use IsCashInBank ;
    protected $guarded = ['id'];
	
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class,'money_received_id');
	}
	
}
