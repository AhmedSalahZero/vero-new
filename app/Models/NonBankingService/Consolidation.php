<?php

namespace App\Models\NonBankingService;

use App\Models\Company;
use App\Models\Traits\Scopes\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;


class Consolidation extends Model
{
    use BelongsToCompany;
    protected $guarded = ['id'];
    protected $connection ='non_banking_service';
    protected $casts = [
        'study_ids'=>'array',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
	public function getName():string 
	{
		return $this->name;
	}
}
