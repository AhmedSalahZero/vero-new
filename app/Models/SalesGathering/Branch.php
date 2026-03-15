<?php

namespace App\Models\SalesGathering;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Traits\Models\IsSalesGatheringModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $is_existing is new branch in financial planning study
 * @property int $is_new is new branch in financial planning study
 * @property int $company_id
 * @property int|null $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\FinancialPlanning\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereIsExisting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Branch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Branch extends Model
{
	
	use IsSalesGatheringModel,BelongsToStudy,BelongsToCompany;
	protected $table ='sales_gathering_branches';
	protected $connection ='mysql';
 	protected $guarded = ['id'];
}
