<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  ExistingBranchesLoanCaseProjection extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';
	protected $table ='existing_branch_loan_case_projections';
	protected $guarded = ['id'];
	protected $casts =[
		'counts'=>'array',
	];
	public function getCountsAtMonthIndex(int $monthIndex)
	{
		return $this->counts[$monthIndex] ?? 0  ; 
	}
	public function getLoanOfficerCount():int
	{
		return $this->loan_officer_count?: 0;
	}
}
