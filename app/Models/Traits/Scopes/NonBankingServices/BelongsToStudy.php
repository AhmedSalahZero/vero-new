<?php
namespace App\Models\Traits\Scopes\NonBankingServices;

use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudy
{
	public function study():BelongsTo
	{
		/**
		 * @var Model $this
		 */
		return $this->BelongsTo(Study::class,'study_id','id');
	}
} 
