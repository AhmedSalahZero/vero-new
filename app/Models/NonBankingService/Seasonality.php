<?php
namespace App\Models\NonBankingService;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property int $study_id
 * @property string $model_name
 * @property string $type
 * @property array<array-key, mixed>|null $percentages زي ما هي في الفورم بالظبط علشان لما نيجي نجيب الاولد داتا في الفيو
 * @property array<array-key, mixed>|null $distributed_percentages بنفرد الكولوم السابق شهور يعني شهر واحد قيمته كذا وشهر اتنين قيمته كذا وهكذا لحد اخر شهر في السنه
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereDistributedPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereModelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality wherePercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereStudyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Seasonality whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Seasonality extends Model
{
	protected $table ='seasonality';
	protected $guarded =[
		'id'
	];	
    protected $connection = NON_BANKING_SERVICE_CONNECTION_NAME;
	protected $casts = [
		'percentages'=>'array',
		'distributed_percentages'=>'array'
	];
	public function getType()
	{
		return $this->type;
	}
}
