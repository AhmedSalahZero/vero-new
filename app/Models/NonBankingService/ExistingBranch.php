<?php
namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $title
 * @property int $is_active
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\ExistingBranch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExistingBranch extends Model
{
	const LEASING_CATEGORY_FORM_ID = 'leasing-category-form';
	use HasBasicStoreRequest,CompanyScope ;
	protected $connection= 'non_banking_service';
	protected $guarded = ['id'];
	
	public function getTitle():string 
	{
		return $this->title;
	}
	public function getName():string
	{
		return $this->getTitle();
	}
		
	public function isActive():bool 
	{
		return (bool)$this->is_active; 
	}
	
		
}
