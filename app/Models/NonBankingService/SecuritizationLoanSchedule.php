<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  SecuritizationLoanSchedule extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table ='securitization_loan_schedules';
	protected $guarded = ['id'];
	protected $casts =[
		// 'bank_portfolio_loan_schedule_payment_ids'=>'array',	
		// 'portfolio_loan_schedule_payment_ids'=>'array',	
		'collection_revenue_amounts'=>'array',	
	];
	public function securitization()
	{
		return $this->belongsTo(Securitization::class,'securitization_id','id');
	}
	
	
}
