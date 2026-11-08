<?php 
namespace App\Http\Controllers\Helpers;

use App\Models\Company;
use App\Models\QuickPricingCalculator;
use App\Support\CompanyScopedModelResolver as Resolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HelpersController {
	
	/**
	 * Models the "add new" modals may create (components/form/select.blade.php callers).
	 */
	public const STORE_NEW_ALLOWED_MODELS = [
		'RevenueBusinessLine', 'ServiceCategory', 'ServiceItem', 'PricingExpense', 'Partner',
		'Position', 'CashExpenseCategory', 'CashExpenseCategoryName',
	];

	/**
	 * SECURITY: previously instantiated any App\Models class named in the request and
	 * wrote arbitrary request-chosen columns. The model is now allow-listed, extra
	 * columns must be real non-protected columns, parent ids must belong to the same
	 * company, and company_id always comes from the route.
	 */
	public function storeNewModal(company $company , Request $request){
		$companyId = $company->id ;
		$fullModelName = Resolver::modelClass($request->get('modalName'), self::STORE_NEW_ALLOWED_MODELS);
		$model = new $fullModelName;
		$value = $request->get('value');
		
		$typeColumn = strtolower($request->get('modalName')) . '_type';
		if($request->get('modalName') == 'PricingExpense'){
			$typeColumn = 'expense_type';
		}
		$type = $request->get('modalType');
		$previousSelectorNameInDb = $request->get('previousSelectorNameInDb');
		$previousSelectorValue = $request->get('previousSelectorValue');
		if($type && ! Resolver::hasColumn($model, $typeColumn)){
			abort(422, 'Invalid type.');
		}
		if($previousSelectorNameInDb){
			Resolver::assertWritableColumn($model, $previousSelectorNameInDb);
			$this->assertParentBelongsToCompany($previousSelectorNameInDb, $previousSelectorValue, $companyId);
		}
		$additionalColumnName = $request->get('additionalColumnName');
		if($additionalColumnName){
			Resolver::assertWritableColumn($model, $additionalColumnName);
			if(! is_scalar($request->get('additionalColumnValue')) && ! is_null($request->get('additionalColumnValue'))){
				abort(422, 'Invalid value.');
			}
		}
		$modelName = $model->where('company_id',$companyId);
		if($type){
			$modelName = $modelName->where($typeColumn,$type)	;
		}
		$modelName = $modelName->where('name',$value)->first();
		if($modelName){
			return response()->json([
				'status'=>false ,
			]);
		}
		$model->company_id = $companyId;
		$model->name = $value;
		if($type){
			$model->{$typeColumn} = $type;
		}
		if($previousSelectorNameInDb){
			
			$model->{$previousSelectorNameInDb} = $previousSelectorValue;
		}
		if($additionalColumnName){
			$model->{$additionalColumnName} = $request->get('additionalColumnValue');
		}
		$model->save();
		return response()->json([
			'status'=>true ,
			'value'=>$value ,
			'id'=>$model->id 
		]);
	}

	/**
	 * A "*_id" parent reference pointing at a company-scoped table must reference a
	 * row of the same company.
	 */
	protected function assertParentBelongsToCompany(string $column, $value, int $companyId): void
	{
		if (! str_ends_with($column, '_id') || $value === null || $value === '') {
			return;
		}
		if (! is_scalar($value)) {
			abort(422, 'Invalid parent.');
		}
		$table = \Illuminate\Support\Str::plural(substr($column, 0, -3));
		if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
			return;
		}
		if (! DB::table($table)->where('id', $value)->where('company_id', $companyId)->exists()) {
			abort(422, 'Invalid parent.');
		}
	}
	
	public function deleteMulti(Company $company , Request $request){
		$ids = $request->get('ids', []);
		QuickPricingCalculator::where('company_id',$company->id)->whereIn('quick_pricing_calculators.id', is_array($ids) ? $ids : [$ids])->delete();
		return response()->json([
			'status'=>true ,
			'link'=> route('admin.view.quick.pricing.calculator',['company'=>$company->id , 'active'=>'quick-price-calculator'])
		]);
		
	}
}
