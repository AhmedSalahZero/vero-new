<?php

namespace App\Models\NonBankingService;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\IsDepartment;
use App\Models\Traits\Scopes\NonBankingServices\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type manpower for example
 * @property int $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NonBankingService\Position> $positions
 * @property-read int|null $positions_count
 * @property-read bool|null $positions_exists
 * @property-read \App\Models\NonBankingService\Study|null $study
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NonBankingService\Department whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Department extends Model
{
	use BelongsToStudy,BelongsToCompany,IsDepartment,HasBasicStoreRequest;
	protected $table ='departments';
	protected $connection =NON_BANKING_SERVICE_CONNECTION_NAME;
 	protected $guarded = ['id'];
	const GENERAL = 'general';
	const MICROFINANCE = 'microfinance';
	 public static function boot()
	 {
		 parent::boot();
		 static::deleting(function(self $department){
			$positions = Position::where('department_id',$department->id)->get();
			$positions->each(function(Position $position){
				$position->delete();
			});
		 });
	 }
	 public function positions()
	{
		return $this->hasMany(Position::class,'department_id','id');
	}

	
}
