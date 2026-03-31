<?php
namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property array<array-key, mixed>|null $growth_rates
 * @property array<array-key, mixed>|null $ijara_mortgage_transactions_projections
 * @property int $study_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereGrowthRates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereIjaraMortgageTransactionsProjections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IjaraMortgageRevenueProjectionByCategory extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection= 'non_banking_service';

	protected $guarded = ['id'];
	protected $casts =[
		'growth_rates'=>'array',
		'monthly_due_cheques_percentages'=>'array',
		'ijara_mortgage_transactions_projections'=>'array',
	];
	
	public function getFormName():string
	{
		return 'non_banking_services.ijara-mortgage-revenue-stream-breakdown.form';
	}
	
	public function getIjaraMortgageTransactionProjectionAtYearIndex(int $yearIndex)
	{
		return $this->getIjaraMortgageTransactionProjection()[$yearIndex] ?? 0  ; 
	}
	public function getIjaraMortgageTransactionProjection():array 
	{
		return $this->ijara_mortgage_transactions_projections;
	}
	public function getGrowthRateAtYearOrMonthIndex(int $yearOrMonthIndex)
	{
		return $this->growth_rates[$yearOrMonthIndex] ?? 0  ; 
	}	
	
	
		
}
