<?php
namespace App\Models\Traits\Scopes\NonBankingServices;

use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudy
{
	public function study():BelongsTo
	{
		return $this->BelongsTo(Study::class,'study_id','id');
	}
} 
