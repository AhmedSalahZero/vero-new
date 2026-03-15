<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $overdraft_against_commercial_paper_id
 * @property float|null $lending_rate
 * @property int|null $for_commercial_papers_due_within_days
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OverdraftAgainstCommercialPaper|null $overdraftAgainstCommercialPaper
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereForCommercialPapersDueWithinDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereLendingRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereOverdraftAgainstCommercialPaperId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\LendingInformation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LendingInformation extends Model
{
    protected $guarded = ['id'];
	public function overdraftAgainstCommercialPaper()
	{
		return $this->belongsTo(OverdraftAgainstCommercialPaper::class,'overdraft_against_commercial_paper_id','id');
	}
	public function getId(){
		return $this->id ; 
	}
	public function getCustomerId()
	{
		return $this->customer_id;
	}	
	public function getAccountNumber()
	{
		return $this->account_number ;
	}
	public function getToBeSetteledMaxWithinDays()
	{
		return $this->to_be_setteled_max_within_days?:0;
	}
	public function getMaxLendingLimitPerCustomer()
	{
		return $this->max_lending_limit_per_customer?:0;
	}
}
