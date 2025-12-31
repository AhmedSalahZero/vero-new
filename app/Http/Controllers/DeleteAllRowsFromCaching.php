<?php

namespace App\Http\Controllers;

use App\Models\CachingCompany;
use App\Models\Company;
use App\Services\Caching\CashingService;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DeleteAllRowsFromCaching extends Controller
{
	/**
	 * Handle the incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function __invoke(Request $request, Company $company,$modelName)
	{
		if($modelName == 'SalesGathering'){
			(new CashingService($company))->removeAll();
		}
		CachingCompany::where('company_id', $company->id)->where('model',$modelName)->get()->each(function ($companyCache) {
			Cache::forget($companyCache->key_name);
			$companyCache->delete();
		});
		Cache::forget(getShowCompletedTestMessageCacheKey($company->id,$modelName));
		return redirect()->back()->with('success', __('All Data Has Been Removed'));
	}
}
