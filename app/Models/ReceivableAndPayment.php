<?php 
namespace App\Models ;

use Illuminate\Database\Eloquent\Model;

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
