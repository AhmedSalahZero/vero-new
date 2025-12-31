<?php
namespace App\Models\NonBankingService;

use Illuminate\Database\Eloquent\Model;

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
