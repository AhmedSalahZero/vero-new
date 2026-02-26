<?php
namespace App\Models\Trading;

use App\Models\Trading\Study;
use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class  AreaProperty extends Model
{
	use HasBasicStoreRequest,CompanyScope ;
	protected $connection=TRADING_CONNECTION_NAME;
	protected $guarded = ['id'];
	
	public function study():BelongsTo
	{
		return $this->belongsTo(Study::class,'study_id','id');
	}
		
}
