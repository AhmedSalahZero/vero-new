<?php

namespace App\Models;

use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Model;

class ExpenseAnalysis extends Model
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
    protected $table = 'expense_analysis';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
    }
	private static function generateSubTabArr()
	{
		return [];
	}
	public static function getTabs(int $companyId)
	{
		return [
			'expenseAnalysis'=>[
				'view_name'=>__('Expense Analysis'),
				'icon'=>'fa fa-crosshairs',
				'subTabs'=>[
					[
						'first_col'=>$firstColumn ='category_name',
						// 'second_col'=>$secondColumn = 'sub_category_name',
						// 'third_col'=>$thirdColumn = 'expense_name',
						'view_name'=>__('Category Trend Analysis Report / With Revenue %'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn]),
					],
					[
						'first_col'=>$firstColumn ='sub_category_name',
						// 'second_col'=>$secondColumn = 'sub_category_name',
						// 'third_col'=>$thirdColumn = 'expense_name',
						'view_name'=>__('Sub Category Trend Analysis Report / With Revenue %'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn]),
					],
					[
						'first_col'=>$firstColumn ='expense_name',
						// 'second_col'=>$secondColumn = 'sub_category_name',
						// 'third_col'=>$thirdColumn = 'expense_name',
						'view_name'=>__('Expense Item Trend Analysis Report / With Revenue %'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn]),
					],
					[
						'first_col'=>$firstColumn ='category_name',
						'second_col'=>$secondColumn = 'sub_category_name',
						'view_name'=>__('Expense Category Against Sub Category'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					
					[
						'first_col'=>$firstColumn ='category_name',
						'second_col'=>$secondColumn = 'sub_category_name',
						'third_col'=>$thirdColumn = 'expense_name',
						'view_name'=>__('Expense Category Against Expense Item'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn,$secondColumn,$thirdColumn]),
					],
					[
						'first_col'=>$firstColumn ='sub_category_name',
						'second_col'=>$secondColumn = 'expense_name',
						'view_name'=>__('Sub Category Against Expense Item'),
						'route'=>route('view.expense.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'columnName'=>$columnName ='category_name',
						'view_name'=>__('Category Breakdown Analysis / With Revenue %'),
						'route'=>route('view.expense.breakdown.report',[$companyId,$columnName]),
					],
					[
						'columnName'=>$columnName ='sub_category_name',
						'view_name'=>__('Sub Category Breakdown Analysis / With Revenue %'),
						'route'=>route('view.expense.breakdown.report',[$companyId,$columnName]),
					],
					[
						'columnName'=>$columnName ='expense_name',
						'view_name'=>__('Expense Item Breakdown Analysis / With Revenue %'),
						'route'=>route('view.expense.breakdown.report',[$companyId,$columnName]),
					],
					[
						'first_col'=>$firstColumn ='category_name',
						'view_name'=>__('Expense Category Average Min Max Values'),
						'route'=>route('view.avg.min.max.against.report',[$companyId,$firstColumn]),
					],
				
					[
						'first_col'=>$firstColumn ='category_name',
						'second_col'=>$secondColumn ='sub_category_name',
						'view_name'=>__('Sub Category Average Min Max Values'),
						'route'=>route('view.avg.min.max.against.report',[$companyId,$firstColumn,$secondColumn]),
					],	
					[
						'first_col'=>$firstColumn ='category_name',
						'second_col'=>$secondColumn ='sub_category_name',
						'third_col'=>$thirdColumn ='expense_name',
						'view_name'=>__('Expense Item Average Min Max Values'),
						'route'=>route('view.avg.min.max.against.report',[$companyId,$firstColumn,$secondColumn,$thirdColumn]),
					],
					
					[
						'first_col'=>$firstColumn ='category_name',
						'view_name'=>__('Expense Category Interval Comparing / With Revenue %'),
						'route'=>route('view.interval.comparing.report',[$companyId,$firstColumn]),
					],
					
					[
						'first_col'=>$firstColumn ='sub_category_name',
						'view_name'=>__('Expense Sub Category Interval Comparing / With Revenue %'),
						'route'=>route('view.interval.comparing.report',[$companyId,$firstColumn]),
					],
					[
						'first_col'=>$firstColumn ='expense_name',
						'view_name'=>__('Expense Item Interval Comparing / With Revenue %'),
						'route'=>route('view.interval.comparing.report',[$companyId,$firstColumn]),
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
