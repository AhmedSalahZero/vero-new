<?php

namespace App\Models\SalesGathering;

use App\Models\Traits\Scopes\BelongsToCompany;
use App\Models\Traits\Scopes\FinancialPlanning\BelongsToStudy;
use App\Traits\Models\IsSalesGatheringModel;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	
	use IsSalesGatheringModel,BelongsToStudy,BelongsToCompany;
	protected $table ='sales_gathering_products';
	protected $connection ='mysql';
 	protected $guarded = ['id'];
}
