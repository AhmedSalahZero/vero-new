<?php

namespace App\Models\SalesGathering;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Traits\Models\IsSalesGatheringModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $is_existing is new principle in financial planning study
 * @property int $is_new is new principle in financial planning study
 * @property int $company_id
 * @property int|null $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\FinancialPlanning\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereIsExisting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Principle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Principle extends Model
{
	
	use IsSalesGatheringModel,BelongsToStudy,BelongsToCompany;
	protected $table ='sales_gathering_principles';
	protected $connection ='mysql';
 	protected $guarded = ['id'];
}
