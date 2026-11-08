<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Support\CompanyScopedModelResolver as Resolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EditTableCellsController extends Controller
{
	/**
	 * Models whose cells can be edited inline (see admin/*\/view.blade.php and
	 * admin/revenue-business-line/view-table.blade.php).
	 */
	public const ALLOWED_MODELS = [
		'QuickPricingCalculator', 'QuotationPricingCalculator', 'FinancialStatement',
		'RevenueBusinessLine', 'ServiceCategory', 'ServiceItem',
	];

	public const ALLOWED_RELATIONS = [
		'revenueBusinessLine', 'serviceCategory', 'serviceItem', 'serviceCategories', 'serviceItems',
	];

	/**
	 * SECURITY: previously the model class, record id, column and relation all came
	 * from the request with no ownership check, so any user could write any column
	 * of any row of any model (including other companies' data). Everything is now
	 * allow-listed and scoped to the {company} in the route.
	 */
	public function __invoke(Request $request)
	{
		$companyId = Resolver::routeCompanyId($request);
		abort_if($companyId === null, 404);

		$data = $request->get('data');
		$columnName = $request->get('columnName');
		$isRelation = $request->get('isRelation');
		$isCollectionRelation = $request->get('isCollectionRelation');
		$collectionItemId = $request->get('collectionItemId');
		$isJson = $request->get('isJson');

		$class = Resolver::modelClass($request->get('modelName'), self::ALLOWED_MODELS);
		$model = Resolver::findForCompany($class, $request->get('modelId'), $companyId);

		if ($isRelation) {
			$relationName = Resolver::assertRelation($model, $request->get('relationName'), self::ALLOWED_RELATIONS);
			$model = $model->{$relationName};
		}
		if ($isCollectionRelation && is_object($model) && ! $model instanceof Model) {
			$model = $model->where('id', $collectionItemId)->first();
		}
		if (! $model instanceof Model) {
			abort(404);
		}
		// A related model must belong to the same company as the parent.
		if (Resolver::hasColumn($model, 'company_id') && ! Resolver::belongsToCompany($model, $companyId)) {
			abort(404);
		}

		if ($isJson) {
			$columnNameSegments = explode('_', (string) $columnName);
			$columnName = $columnNameSegments[0];
		}
		$columnName = Resolver::assertWritableColumn($model, $columnName);

		if (Resolver::hasColumn($model, 'company_id')
			&& $model::where('company_id', $companyId)->where($columnName, $data)->exists()) {
			// this record already exist ;
			return response()->json([
				'status' => false,
				'dataTableId' => $request->get('dataTableId'),
			]);
		}

		$model->{$columnName} = $isJson ? ['en' => $data, 'ar' => $data] : $data;
		$model->updated_at = now();
		$model->save();

		return response()->json([
			'dataTableId' => $request->get('dataTableId'),
		]);
	}
}
