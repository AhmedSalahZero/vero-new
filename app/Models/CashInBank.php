<?php

namespace App\Models;

use App\Traits\Models\IsCashInBank;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $money_received_id
 * @property int|null $receiving_bank_id
 * @property string|null $account_type
 * @property string|null $account_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $company_id
 * @property-read \App\Models\AccountType|null $accountType
 * @property-read \App\Models\MoneyReceived $moneyReceived
 * @property-read \App\Models\FinancialInstitution|null $receivingBank
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereMoneyReceivedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereReceivingBankId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashInBank whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CashInBank extends Model
{
	use IsCashInBank ;
    protected $guarded = ['id'];
	
	public function moneyReceived()
	{
		return $this->belongsTo(MoneyReceived::class,'money_received_id');
	}
	
}
