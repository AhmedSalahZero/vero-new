<?php
namespace App\Models\Trading;

use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\Tradings\BelongsToStudy;
use App\Traits\HasBasicStoreRequest;
use Illuminate\Database\Eloquent\Model;

class  LoanSchedulePayment extends Model
{
	use HasBasicStoreRequest,CompanyScope , BelongsToStudy ;
	protected $connection=TRADING_CONNECTION_NAME;
	protected $guarded = ['id'];
	protected $casts =[
	
	];
	
	
}
