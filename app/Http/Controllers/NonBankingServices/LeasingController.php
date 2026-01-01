<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Http\Requests\NonBankingServices\StoreLeasingRevenueStreamRequest;
use App\Models\Company;
use App\Models\NonBankingService\LeasingCategory;
use App\Models\NonBankingService\LeasingRevenueStreamBreakdown;
use App\Models\NonBankingService\Study;
use App\Traits\NonBankingService;
use Illuminate\Http\Request;

class LeasingController extends Controller
{
    use NonBankingService ;
    public function create(Company $company, Request $request, Study $study)
    {
		// Study::sumTwoIncomeStatements();
        return view('non_banking_services.leasing-revenue-stream-breakdown.form', $this->getViewVars($company, $study));
    }
	public function getOldData(Company $company , Request $request , Study $study)
	{
		$yearsWithItsMonths =  $study->getOperationDurationPerYearFromIndexes() ;
		$lastMonthIndexInEachYear = getLastMonthIndexInEachYear($yearsWithItsMonths); 
        $yearOrMonthsIndexes = $study->getYearOrMonthIndexes();
		$hasEnteredRevenueStreamBreakdown = $study->leasingRevenueStreamBreakdown->count();
		$leasingCategoriesFormatted = $company->getLeasingCategoriesFormattedForSelect();
		$leasingRevenueCategories = $study->leasingRevenueStreamBreakdown;
		$leasingRevenueStreamBreakdown = [];
		foreach(count($leasingRevenueCategories) ? $leasingRevenueCategories : [null] as $revenueStreamItem){
			$leasingRevenueStreamBreakdown[] = LeasingRevenueStreamBreakdown::getRow($revenueStreamItem,$study);
		}
		$loanAmountsPerRevenueStreamBreakdown = [];
		$subNames = [];
		foreach($leasingRevenueCategories as $currentLeasingRevenueStreamBreakdown){
			$subNames[$currentLeasingRevenueStreamBreakdown->id] = $currentLeasingRevenueStreamBreakdown->getReviewForTable();
			foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted){
				$loanAmountsPerRevenueStreamBreakdown[$currentLeasingRevenueStreamBreakdown->id][$dateAsIndex] = $currentLeasingRevenueStreamBreakdown->getLoanAmountAtYearOrMonthIndex($dateAsIndex);
			}
		}
		$eclRates = [];
		$adminFeesRates = [];
		$equityFundingRates = [];
		$equityFundingValues = [];
		$newLoansFundingRates = [];
		$newLoanFundingValues = [];
		$eclAndNewPortfolioFundingRate = $study?  $study->getEclAndNewPortfolioFundingRatesForStreamType(Study::LEASING) : null;
		foreach($yearOrMonthsIndexes as $dateAsIndex => $dateFormatted ){
			$adminFeesRates[$dateAsIndex]=$eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getAdminFeesRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$eclRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEclRatesAtYearOrMonthIndex($dateAsIndex) : 0;
			$equityFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getEquityFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoansFundingRates[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingRatesAtYearOrMonthIndex($dateAsIndex):0;
			$newLoanFundingValues[$dateAsIndex] = $eclAndNewPortfolioFundingRate ? $eclAndNewPortfolioFundingRate->getNewLoansFundingValuesAtYearOrMonthIndex($dateAsIndex):0;
		}
		
		return [
			'submitUrl'=>route('store.leasing.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id]),
			'dates'=>$yearOrMonthsIndexes,
			'lastMonthIndexInEachYear'=>$lastMonthIndexInEachYear,
			'hasEnteredRevenueStreamBreakdown'=>$hasEnteredRevenueStreamBreakdown,
			'model'=>[
				'leasingRevenueStreamBreakdown'=>[
					'sub_items'=>$leasingRevenueStreamBreakdown,
					'empty_row'=>LeasingRevenueStreamBreakdown::getRow(null,$study)
				],
				'loan_amounts'=>[
					'names'=>$subNames,
					'sub_items'=>$loanAmountsPerRevenueStreamBreakdown,
				],
				'admin_fees'=> $adminFeesRates,
				'ecl_rates'=>$eclRates,
				'equity_funding_rates'=>$equityFundingRates,
				'equity_funding_values'=>$equityFundingValues,
				'new_loans_funding_rates'=>$newLoansFundingRates,
				'new_loans_funding_values'=>$newLoanFundingValues
				],
			'selectOptions'=>[
				'leasingCategories'=>$leasingCategoriesFormatted
			]
		];
	}
    protected function getViewVars(Company $company, Study $study)
    {
        return [
            'company'=>$company ,
            'study'=>$study,
            'model'=>$study ,
            'title'=>__('Leasing Revenue Stream Breakdown'),
        ];
    }

    public function store(Company $company, StoreLeasingRevenueStreamRequest $request, Study $study)
    {
        if ($request->get('submit_button') === 'save-categories') {
            $study->storeRepeaterRelations($request, ['leasingRevenueStreamBreakdown'], $company);
			return response()->json([
				'redirectTo'=>route('create.leasing.revenue.stream.breakdown',['company'=>$company->id,'study'=>$study->id])
			]);
        }

        $loanAmounts = $request->input('loan_amounts.sub_items', []);

        // if ($request->has('loan_amounts')) {
            $study->leasingRevenueStreamBreakdown->each(function ($model) use ($loanAmounts) {
                $model->update([
                    'loan_amounts'=>$loanAmounts[$model->id]
                ]);
            });
            $study->update([
                'leasing_growth_rates'=>$request->get('growth_rate')
            ]);
        // }
      //  $study->syncSeasonality($request->get('seasonality', []), Study::LEASING, $company->id) ;
        $study->storeFixedLoans($request,Study::LEASING, 'leasingRevenueStreamBreakdown');
        $study->updateExpensesPercentageAndCostPerUnitsOfSales();
        return response()->json([
            'redirectTo'=>$study->getRevenueRoute(Study::DIRECT_FACTORING)
        ]);
    }
}
