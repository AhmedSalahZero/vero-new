<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string|null $expense_type
 * @property int $department_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\NonBankingService\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NonBankingService\Manpower> $manpowers
 * @property-read int|null $manpowers_count
 * @property-read bool|null $manpowers_exists
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereExpenseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Position whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Position extends Model
{
	
	use BelongsToStudy,BelongsToCompany;
	protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
 	protected $guarded = ['id'];
	protected $casts = [
	
	];
	public function getName()
	{
		return $this->name ;
	}
	public function manpowers():HasMany
	{
		return $this->hasMany(Manpower::class,'position_id','id');
	}
	public function department():BelongsTo
	{
		return $this->belongsTo(Department::class,'department_id','id');
	}
	public function getExpenseTypeId():?string
	{
		return $this->expense_type;
	}
	
}
