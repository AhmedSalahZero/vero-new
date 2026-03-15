<?php

namespace App\Models\SalesGathering;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Traits\Models\IsSalesGatheringModel;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $is_existing is new product in financial planning study
 * @property int $is_new is new product in financial planning study
 * @property int $company_id
 * @property int|null $study_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\FinancialPlanning\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereIsExisting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering\Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
	
	use IsSalesGatheringModel,BelongsToStudy,BelongsToCompany;
	protected $table ='sales_gathering_products';
	protected $connection ='mysql';
 	protected $guarded = ['id'];
}
