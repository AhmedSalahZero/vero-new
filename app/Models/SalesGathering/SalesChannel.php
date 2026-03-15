<?php

namespace App\Models\SalesGathering;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Traits\Models\IsSalesGatheringModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $is_existing is new sales_channel in financial planning study
 * @property int $is_new is new sales_channel in financial planning study
 * @property int $company_id
 * @property int|null $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\FinancialPlanning\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereIsExisting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\SalesChannel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalesChannel extends Model
{
	
	use IsSalesGatheringModel,BelongsToStudy,BelongsToCompany;
	protected $table ='sales_gathering_sales_channels';
	protected $connection ='mysql';
 	protected $guarded = ['id'];
}
