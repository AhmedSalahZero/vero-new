<?php
namespace App\Models\Traits\Scopes\FinancialPlanning;

use App\Models\FinancialPlanning\Study;
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
