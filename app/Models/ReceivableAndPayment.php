<?php 
namespace App\Models ;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string $balance_amount
 * @property array<array-key, mixed>|null $payload
 * @property int $cash_flow_statement_id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CashFlowStatement|null $cashFlowStatement
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereBalanceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereCashFlowStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ReceivableAndPayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReceivableAndPayment extends Model{
	protected $guarded  = ['id'];
	
	protected $table = 'receivables_payments';
	protected $casts = [
		'payload'=>'array'
	];
	public function cashFlowStatement()
	{
		return $this->belongsTo(CashFlowStatement::class , 'cash_flow_statement_id','id');
	}
	public function getId(){
		return $this->id ;
	}
	public function getName()
	{
		return $this->name ;
	}
	public function getBalanceAmount()
	{
		return $this->balance_amount ?:0 ;
	}
	public function getReceivableValueAtDate(string $date)
	{
		return $this->payload[$date] ?? 0;
	}
	public function getType()
	{
		return $this->type ;
	}
}
