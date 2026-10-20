<?php

namespace App\Http\Controllers;

use App\Enums\LgSources;
use App\Enums\LgTypes;
use App\Models\Company;
use App\Models\FinancialInstitution;
use App\Models\Partner;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;


class LgByBankNameReportController
{
    use GeneralFunctions;

    public function index(Company $company,Request $request)
    {
		$selectedCurrency  = $request->get('currency_name');
		$currencies = DB::table('letter_of_guarantee_issuances')->where('company_id',$company->id)->get()->unique('lg_currency')->pluck('lg_currency','lg_currency')->toArray();
        return view('lg_by_bank_name_form', [
            'company' => $company,
			'selectedCurrency'=>$selectedCurrency,
			'currencies'=>$currencies
        ]);
    }

    public function result(Company $company, Request $request)
    {
        $startDate = $request->get('start_date');
  //      $endDate = $request->get('end_date');
      
        $currencyName = $request->get('currency_name');
		$results = [];
		$bankIds = $request->get('bank_id');
	
	//	$bankName = FinancialInstitution::find($bankId)->getName();
		$status = $request->get('status');
		$results = DB::table('letter_of_guarantee_issuances')->where('letter_of_guarantee_issuances.company_id',$company->id)->where('lg_currency',$currencyName)->whereIn('financial_institution_id',$bankIds)
		->when($status== 'running',function($q){
			$q->where('status','running');
		})
		
		// renewal_date
		->where('renewal_date','>=',$startDate)
		// ->whereBetween('issuance_date',[$startDate,$endDate])
		->join('partners','partners.id','=','letter_of_guarantee_issuances.partner_id')
		->join('financial_institutions','financial_institutions.id','=','letter_of_guarantee_issuances.financial_institution_id')
		->join('banks','banks.id','=','financial_institutions.bank_id')
		->selectRaw(
			'letter_of_guarantee_issuances.id as id , partner_id , partners.name as partner_name , lg_type , transaction_name,lg_code, source ,banks.name_en as financial_institution_name , lg_amount , DATE_FORMAT(letter_of_guarantee_issuances.renewal_date,\'%d-%m-%Y\') as renewal_date , DATE_FORMAT(COALESCE(letter_of_guarantee_issuances.cancellation_date, (SELECT MIN(letter_of_guarantee_statements.date) FROM letter_of_guarantee_statements WHERE letter_of_guarantee_statements.letter_of_guarantee_issuance_id = letter_of_guarantee_issuances.id AND letter_of_guarantee_statements.type = \'for-cancellation\')),\'%d-%m-%Y\') as cancellation_date , letter_of_guarantee_issuances.status as status , cash_cover_amount,lg_commission_rate '
		)->get();
        if (!count($results)) {
            return redirect()->back()->with('fail', __('No Data Found'));
        }
		$openRows = $results->where('status', '!=', 'cancelled');
		$totals = [
			'lgAmountWithoutCancellation' => $openRows->sum('lg_amount'),
			'cashCoverWithoutCancellation' => $openRows->sum('cash_cover_amount'),
			'lgAmountWithCancellation' => $results->sum('lg_amount'),
			'cashCoverWithCancellation' => $results->sum('cash_cover_amount'),
		];
		$results = $this->paginate($results,50);
		$lgsTypes = LgTypes::getAll();
		$lgsSources = LgSources::getAll();
        return view('lg_by_bank_name_result', [
            'results' => $results,
			'totals' => $totals,
			'lgsTypes'=>$lgsTypes,
			'lgsSources'=>$lgsSources,
            'currency' => $currencyName,
		//	'bankName'=>$bankName,
			'startDate'=>Carbon::make($startDate)->format('d-m-Y'),
			// 'endDate'=>Carbon::make($endDate)->format('d-m-Y')
        ]);
    }
	
function paginate(\Illuminate\Support\Collection $results, $pageSize)
{
	$page = Paginator::resolveCurrentPage('page');
	
	$total = $results->count();
	return $this->paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
		'path' => Paginator::resolveCurrentPath(),
		'pageName' => 'page',
	]);

}
 function paginator($items, $total, $perPage, $currentPage, $options)
{
	return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
		'items', 'total', 'perPage', 'currentPage', 'options'
	));
}
}
