<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\Api\OdooService;
use Illuminate\Http\Request;


class ReadOdooInvoices extends Controller
{
	public function handle(Request $request,  Company $company)
	{
		$odoo = new OdooService($company);
		$startDate = $request->get('odoo_start_date');
		$endDate = $request->get('odoo_end_date');
		try{
			logger(['sapi' => PHP_SAPI, 'max_execution_time' => ini_get('max_execution_time'), 'memory_limit' => ini_get('memory_limit')]);

			logger('1');
			$odoo->startImportContracts($startDate,$endDate,$company->id);
			logger('2');
			$odoo->startImportInvoices($startDate,$endDate,$company->id);
					logger('4');
			}catch(\Exception $e){
				logger('5');
				session()->put('fail', $e->getMessage());
			return back();
		}
		return redirect()->back()->with('success',__('Invoices Reading Has Been Completed'));
		
	}
}
