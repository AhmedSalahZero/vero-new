<?php
namespace App\Models\NonBankingService;

use App\Helpers\HArr;
use App\Models\NonBankingService\Study;
use App\Traits\HasCollectionOrPaymentStatement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class FixedAssetOpeningBalance extends Model
{
	use HasCollectionOrPaymentStatement;
		protected $connection= 'non_banking_service';
    protected $guarded = ['id'];
	protected $casts = [
		'product_allocations'=>'array',
		'admin_depreciations'=>'array',
		'manufacturing_depreciations'=>'array',
		'monthly_accumulated_depreciations'=>'array',
		'statement'=>'array',
		'monthly_product_allocations'=>'array',
	];
	
	public static function getOpeningBalanceColumnName():string
	{
		return 'gross_amount';
	}
	public static function getPayloadStatementColumn():string 
	{
		return 'monthly_accumulated_depreciations';
	}
	public static function booted()
	{
			parent::boot();
			static::saving(function(self $model){
				$openingBalance = $model->{self::getOpeningBalanceColumnName()};
				$monthlyDepreciation = $model->monthly_depreciation ;
				$dates = range(0,$model->monthly_counts-1);
				$monthlyDepreciations = [];
				$accumulatedDepreciations = [];
				$currentAccumulatedDepreciation = $model->accumulated_depreciation;
				// $endBalances =[];
				$statement = [];
				foreach($dates as $dateAsIndex){
				$statement['beginning_balance']	[$dateAsIndex] = $openingBalance;
				$statement['monthly_depreciation'][$dateAsIndex] = $monthlyDepreciation;
				$monthlyDepreciations[$dateAsIndex] = $monthlyDepreciation;
				$currentAccumulated =array_sum($monthlyDepreciations)+$currentAccumulatedDepreciation;
				$statement['accumulated_depreciation'][$dateAsIndex] = $currentAccumulated;
					$accumulatedDepreciations[$dateAsIndex] = $currentAccumulated ;
					$statement['end_balance'][$dateAsIndex] = $openingBalance-$currentAccumulated;
				}
				
				$model->statement =$statement;
				
			});
			
			static::saved(function(self $model){
				$statements = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_opening_balances')->where('study_id',$model->study->id)->pluck('statement')->toArray();
		//		$dateWithDateIndex = $model->study->getDateWithDateIndex();
				$studyDates = $model->study->getStudyDates() ;
				$studyDates = array_keys($studyDates);
				$totalMonthlyDepreciations = [];
				foreach($statements as $statement){
					$statement = json_decode($statement , true );
					$monthlyDepreciations = $statement['monthly_depreciation']??[] ; 
					$totalMonthlyDepreciations = HArr::sumAtDates([$totalMonthlyDepreciations , $monthlyDepreciations],$studyDates);
				}
				DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports')->where('study_id',$model->study->id)->update([
					'opening_depreciation_expenses'=>json_encode($totalMonthlyDepreciations)
				]);

			});
			
			
	}
	
	
    public function study():BelongsTo
    {
        return $this->belongsTo(Study::class, 'study_id', 'id');
    }
	public function fixedAssetName()
	{
		return $this->belongsTo(FixedAssetName::class,'name_id');
	}
    public function getName():string 
    {
        return $this->fixedAssetName ? $this->fixedAssetName->getName()  : __('N/A') ;
    }
	public function getNameId()
	{
		return $this->name_id;
	}
	public function getMonthlyCounts():int 
	{
		return $this->monthly_counts;
	}
	
	public function getGrossAmount():float
	{
		return $this->gross_amount;
	}
	public function getAccumulatedDepreciation():float
	{
		return $this->accumulated_depreciation?:0;
	}
	public function getNetAmount():float
	{
		return $this->getGrossAmount() - $this->getAccumulatedDepreciation();
	}
    public function getMonthlyDepreciation():float 
	{
		return $this->monthly_depreciation;
	}  


}
