<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $loan_officer_count
 * @property array<array-key, mixed> $counts
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereCounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereLoanOfficerCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranchesLoanCaseProjection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExistingBranchesLoanCaseProjection extends Model
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
