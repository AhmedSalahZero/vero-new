<?php

namespace App\Models;

use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $date
 * @property int $company_id
 * @property string|null $category_name
 * @property string|null $sub_category_name
 * @property string|null $expense_name
 * @property string|null $quantity_measurement_unit
 * @property string|null $quantity
 * @property string|null $cost_per_unit
 * @property string|null $total_cost
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereCostPerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereExpenseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereQuantityMeasurementUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereSubCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExpenseAnalysis whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExpenseAnalysis extends Model
{
    use StaticBoot;
 

    protected $guarded = [];


  
    protected $table = 'expense_analysis';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
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
