<?php
namespace App\Models\Traits\Scopes\Tradings;

use App\Models\Trading\Study;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudy
{
	public function study():BelongsTo
	{
		
		return $this->BelongsTo(Study::class,'study_id','id');
	}
} 
