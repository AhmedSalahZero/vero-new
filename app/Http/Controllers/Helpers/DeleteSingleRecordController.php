<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use App\Support\CompanyScopedModelResolver as Resolver;
use Illuminate\Http\Request;

class DeleteSingleRecordController extends Controller
{
	/**
	 * SECURITY: previously deleted any record of any model named in the request.
	 * Now limited to the models the UI deletes through this endpoint and to rows
	 * of the {company} in the route.
	 */
	public function __invoke(Request $request)
	{
		$companyId = Resolver::routeCompanyId($request);
		abort_if($companyId === null, 404);

		$class = Resolver::modelClass($request->get('modelName'), EditTableCellsController::ALLOWED_MODELS);
		$record = Resolver::findForCompany($class, $request->get('recordId'), $companyId);
		$record->delete();

		return response()->json([
			'status' => true,
			'tableId' => $request->get('tableId'),
		]);
	}
}
