<?php

namespace App\Models;

use App\Traits\StaticBoot;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SalesGathering extends Model
{
    use StaticBoot;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];


    //  protected $connection= 'mysql2';
    // protected $table = 'sales_gathering';
    // protected $primaryKey  = 'user_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_gathering';
    public function scopeCompany($query,$request = null )
    {
        return $query->where('company_id', request()->company->id?? Request('company_id',$request? $request->get('company_id'):null) );
    }
	public static function getTrendAnalysisTabs(int $companyId)
	{
		$trendTabs = TablesField::where('is_sales_trend',1)->pluck('field_name','view_name')->toArray();

		
		return [
			$mainReportName = 'zone'=>[
				'view_name'=>__('Zones'),
				'show'=>hasExport([$mainReportName],$companyId),
				'icon'=>'fa fa-crosshairs',
				'subTabs'=>[
					[
						'first_col'=>$mainReportName,
						'show'=>true ,
						'view_name'=>__('Zones Sales Analysis'),
						'route'=>route('view.single.trend.analysis',[$companyId,$mainReportName])
					],
					[
						'first_col'=>$mainReportName,
						'second_col'=>$secondColumn = 'sales_channel',
						'view_name'=>__('Zone Against Sales Channels'),
						'route'=>route('view.against.trend.analysis',[$companyId,$mainReportName,$secondColumn])
					],
					[
						'first_col'=>$mainReportName,
						'second_col'=>$secondColumn = 'sales_channel',
						'view_name'=>__('Zone Against Sales Channels'),
						'route'=>route('view.against.trend.analysis',[$companyId,$mainReportName,$secondColumn])
					],
				]
				],
				
		];
	}
	public function getDeleteByDateColumnName()
	{
		return 'date';
	}
	
}
