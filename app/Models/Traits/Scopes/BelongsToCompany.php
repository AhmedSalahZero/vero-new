<?php
namespace App\Models\Traits\Scopes;

use App\Models\Company;
use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
	public function company():BelongsTo
	{
		/**
		 * @var Model $this
		 */
		return $this->BelongsTo(Company::class,'company_id','id');
	}
} 
