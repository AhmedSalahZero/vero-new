<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\CashAndBankOpeningBalance;
use App\Models\NonBankingService\EclAndNewPortfolioFundingRate;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\Manpower;
use App\Models\NonBankingService\SecuritizationLoanSchedule;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeStatementController extends Controller
{
    public function index(Company $company, Study $study,$onlyViewVars = false )
    {
		$study->recalculateCashflowStatement();
		$dateIndexWithDate = $study->getDateIndexWithDate();
        $formattedExpenses = [];
        $formattedResult = [];
        $salesRevenuePerTypes = [];
        $yearWithItsIndexes = $study->getOperationDurationPerYearFromIndexes();
        $monthsWithItsYear = $study->getMonthsWithItsYear($yearWithItsIndexes) ;
        $tableDataFormatted = [];
		$yearIndexWithYear = $study->getYearIndexWithYear();
        $expenseMainTitlesMapping = getExpenseTypes();
        $loanSchedulePayments = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->selectRaw('portfolio_loan_type,revenue_stream_type,interestAmount,securitization_date_index,endBalance')->where('study_id', $study->id)->get()->toArray();
        $defaultNumericInputClasses = [
            'number-format-decimals'=>0,
            'is-percentage'=>false,
            'classes'=>'repeater-with-collapse-input',
            'formatted-input-classes'=>'custom-input-numeric-width ',
        ];
        $defaultPercentageInputClasses = [
            'classes'=>'',
            'formatted-input-classes'=>'',
            'is-percentage'=>true ,
            'number-format-decimals'=> 2,
        ];
        $defaultClasses = [
            $defaultNumericInputClasses,
            $defaultPercentageInputClasses
        ];
        $orderIndexPerExpenseCategory = [
            // 'sales_revenue'=>0,
            'cost-of-service'=>1 ,
            'gross-profit'=>2 ,
            'other-operation-expense'=>3,
            'marketing-expense'=>4 ,
            'sales-expense'=>5,
            'general-expense'=>6,
            'ebitda'=>7,
            'ecl'=>8,
            'ebit'=>9,
            'financial-expense'=>10,
            'ebt'=>11,
            'corporate-taxes'=>12,
            'net-profit'=>13
        ];
		$isMonthlyStudy = $study->isMonthlyStudy();
        // $financialYearsEndMonths = $study->getFinancialYearsEndMonths();
        $grossProfitOrderIndex = $orderIndexPerExpenseCategory['gross-profit'];
        $ebitdaOrderIndex = $orderIndexPerExpenseCategory['ebitda'];
        $eclAndDepreciationOrderIndex = $orderIndexPerExpenseCategory['ecl'];
        $ebitOrderIndex = $orderIndexPerExpenseCategory['ebit'];
        $ebtOrderIndex = $orderIndexPerExpenseCategory['ebt'];
        $netProfitOrderIndex = $orderIndexPerExpenseCategory['net-profit'];
        $corporateTaxesOrderIndex = $orderIndexPerExpenseCategory['corporate-taxes'];
        $financialExpenseOrderIndex = $orderIndexPerExpenseCategory['financial-expense'];
        
		$cashAndBankOpeningBalance = CashAndBankOpeningBalance::where('study_id',$study->id)->first();
		$cashAndBankInterest = $cashAndBankOpeningBalance ? $cashAndBankOpeningBalance->interests: [];
		$tableDataFormatted[0]['sub_items']['cash-and-banks-opening-interest']['options']['title'] = __('Existing Portfolio Interest');
		$tableDataFormatted[0]['sub_items']['cash-and-banks-opening-interest']['data']= $cashAndBankInterest;
		
		
        $tableDataFormatted[0]['main_items']['sales-revenue']['options'] = array_merge([
            'title'=>__('Sales Revenue')
        ], $defaultNumericInputClasses);
        if ($study->hasLeasing()) {
            $tableDataFormatted[0]['main_items']['growth-rate']['options'] = array_merge($defaultPercentageInputClasses, ['title'=>__('Growth Rate %')]);
            $tableDataFormatted[0]['sub_items']['leasing']['options'] =array_merge([
                'title'=>__('Leasing'),
            ], $defaultNumericInputClasses);
        }
        if ($study->hasDirectFactoring()) {
            $tableDataFormatted[0]['sub_items']['direct-factoring']['options'] =array_merge([
                'title'=>__('Direct Factoring'),
            ], $defaultNumericInputClasses);
        }
        
        if ($study->hasIjaraMortgage()) {
            $tableDataFormatted[0]['sub_items']['ijara']['options'] =array_merge([
                'title'=>__('Ijara Mortgage'),
            ], $defaultNumericInputClasses);
        }
        if ($study->hasPortfolioMortgage()) {
            $tableDataFormatted[0]['sub_items'][Study::PORTFOLIO_MORTGAGE]['options'] =array_merge([
                'title'=>__('Portfolio Mortgage'),
            ], $defaultNumericInputClasses);
        }
        if ($study->hasReverseFactoring()) {
            $tableDataFormatted[0]['sub_items']['reverse-factoring']['options'] =array_merge([
                'title'=>__('Reverse Factoring'),
            ], $defaultNumericInputClasses);
        }
        
        if ($study->hasMicroFinance()) {
            $tableDataFormatted[0]['sub_items'][Study::MICROFINANCE]['options'] =array_merge([
                'title'=>__('Microfinance'),
            ], $defaultNumericInputClasses);
        }
        if ($study->hasConsumerFinance()) {
            $tableDataFormatted[0]['sub_items'][Study::CONSUMER_FINANCE]['options'] =array_merge([
                'title'=>__('Consumer Finance'),
            ], $defaultNumericInputClasses);
        }
        // if ($study->hasSecuritization()) {
        //     $tableDataFormatted[0]['sub_items'][Study::SECURITIZATION]['options'] =array_merge([
        //         'title'=>__('Securitization'),
        //     ], $defaultNumericInputClasses);
        // }
		
		$yearWithItsIndexes = $study->getOperationDurationPerYearFromIndexes();
		$monthsWithItsYear = $study->getMonthsWithItsYear($yearWithItsIndexes) ;
		$monthsWithItsNumbers = $study->getMonthIndexWithMonthNumber($yearWithItsIndexes) ;
		
        $tableDataFormatted[1]['main_items']['cost-of-service']['options']['title'] = __('Cost Of Service');
        $tableDataFormatted[1]['sub_items']['Existing Portfolio New Portfolio Interest Expense']['options']['title'] = $existingPortfolioInterestExpenseTitle =  __('Existing Portfolio New Portfolio Interest Expense');
        // $tableDataFormatted[1]['sub_items']['Existing Long Term Loans Interest Expense']['options']['title'] = $existingLongTermLoanInterestTitle =  __('Existing Long Term Loans Interest Expense');
        $tableDataFormatted[1]['sub_items']['New Portfolio Interest Expense']['options']['title'] = __('New Portfolio Interest Expense');
		$incomeStatementReport = $study->incomeStatementReport;
		 $odaInterestExpenses = $incomeStatementReport ? $incomeStatementReport->oda_interests : [];
		 $odasInterestExpenseText = __('ODAs Interest Expense') ;
		 
		 if(array_sum($odaInterestExpenses)){
			 $tableDataFormatted[1]['sub_items']['ODAs Interest Expense']['options']['title'] = $odasInterestExpenseText;
		 }
        $tableDataFormatted[1]['sub_items']['Manpower Salaries']['options']['title'] = __('Manpower Salaries');
        $yearWithItsMonths=$study->getYearIndexWithItsMonths();
        
			
		$totalInterestExpense = $incomeStatementReport ? (array)$incomeStatementReport->existing_interests_expense : [];
        $tableDataFormatted[1]['sub_items'][$existingPortfolioInterestExpenseTitle]['data'] = $totalInterestExpense;
        $tableDataFormatted[1]['sub_items'][$existingPortfolioInterestExpenseTitle]['year_total'] = HArr::sumPerYearIndex($totalInterestExpense, $yearWithItsMonths);
    
		
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['options']['title'] = __('Gross Profit');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        $otherOperationExpenseOrder = $orderIndexPerExpenseCategory['other-operation-expense'];

        
        $marketingExpenseOrder = $orderIndexPerExpenseCategory['marketing-expense'];
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['marketing-expense']['options']['title'] = __('Market Expense');
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        $salesExpenseOrder = $orderIndexPerExpenseCategory['sales-expense'];
        $tableDataFormatted[$salesExpenseOrder]['main_items']['sales-expense']['options']['title'] = __('Sales Expense');
        $tableDataFormatted[$salesExpenseOrder]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        
        $generalExpenseOrder = $orderIndexPerExpenseCategory['general-expense'];
        $tableDataFormatted[$generalExpenseOrder]['main_items']['general-expense']['options']['title'] = __('General Expense');
        $tableDataFormatted[$generalExpenseOrder]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        $depreciationKey ='total-depreciation';
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['options']['title'] = __('EBITDA');
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $eclAndDepreciationKey = 'ecl-and-depreciation-expenses';
        $studyMonthsForViews = $study->getStudyDates();
        $studyMonthsForViews = array_slice($studyMonthsForViews, 0, $study->getViewStudyEndDateAsIndex()+1);

        $studyDates = array_keys($study->getStudyDates()) ;
        $sumKeys = $studyDates;
        
        
        
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['options']['title'] = __('EBIT');
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['options']['title'] = __('EBT');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['options']['title'] = __('Net Profit');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        
        // $resultPerRevenueStreamType = [
        //     'all'=>[]
        // ];
		
		

        $directFactoringBreakdown = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('direct_factoring_breakdowns')
        ->where('study_id', $study->id)
        ->selectRaw('interest_revenue,bank_interest_expense')->get()->toArray();
    
        $formattedDirectFactoring = [];
		
        foreach ($directFactoringBreakdown as $currentDirectFactoringBreakdown) {
            $interestRevenues= (array)json_decode($currentDirectFactoringBreakdown->interest_revenue);
            $bankInterestExpenses= (array)json_decode($currentDirectFactoringBreakdown->bank_interest_expense);
            foreach ($monthsWithItsYear as $currentMonthIndex => $currentYearIndex) {
            //    $currentMonthAsString = $dateIndexWithDate[$currentMonthIndex];
                $currentInterestRevenue  = $interestRevenues[$currentMonthIndex]??0;
                $currentBankInterestExpense = $bankInterestExpenses[$currentMonthIndex]??0;
					$currentYearOrMonthIndex = $isMonthlyStudy ? $currentMonthIndex : $currentYearIndex ;
					$currentYearAsString = $yearIndexWithYear[$currentYearOrMonthIndex] ?? null ;
				$currentMonthNumber = $monthsWithItsNumbers[$currentMonthIndex]??null;
				$currentYearOrMonthAsString = $isMonthlyStudy ? $currentMonthNumber : $currentYearAsString; 
                if (!is_null($currentMonthIndex)) {
                    $formattedDirectFactoring['interest_revenue'][$currentMonthIndex] = isset($formattedDirectFactoring['interest_revenue'][$currentMonthIndex]) ? $formattedDirectFactoring['interest_revenue'][$currentMonthIndex] +  $currentInterestRevenue : $currentInterestRevenue;
                    $formattedDirectFactoring['bank_interest_expense'][$currentMonthIndex] = isset($formattedDirectFactoring['bank_interest_expense'][$currentMonthIndex]) ? $formattedDirectFactoring['bank_interest_expense'][$currentMonthIndex] +  $currentBankInterestExpense : $currentBankInterestExpense;
                    $resultPerRevenueStreamType['direct-factoring'][$currentYearOrMonthAsString] = $formattedDirectFactoring['interest_revenue'][$currentMonthIndex];
                    $salesRevenuePerTypes['direct-factoring'][$currentMonthIndex] = $resultPerRevenueStreamType['direct-factoring'][$currentYearOrMonthAsString];
                    $currentDirectFactoringAtMonth = $salesRevenuePerTypes['direct-factoring'][$currentMonthIndex];
                    $tableDataFormatted[0]['sub_items']['direct-factoring']['data'][$currentMonthIndex] = $currentDirectFactoringAtMonth ;
					$resultPerRevenueStreamType['all'][$currentYearOrMonthAsString] = isset($resultPerRevenueStreamType['all'][$currentYearOrMonthAsString]) ? $resultPerRevenueStreamType['all'][$currentYearOrMonthAsString] + $currentDirectFactoringAtMonth : $currentDirectFactoringAtMonth;
                }
            }
        }
		
		$totalEndBalanceForPortfolioPerRevenueType = [];
		$studyDates = $study->getDateWithDateIndex();
        foreach ($loanSchedulePayments as $loanSchedulePaymentAsStdClass) {
            $portfolioLoanType = $loanSchedulePaymentAsStdClass->portfolio_loan_type;
            $isPortfolio = $portfolioLoanType == 'portfolio';
            $revenueStreamType = $loanSchedulePaymentAsStdClass->revenue_stream_type;
			$securitizationDateIndex = $loanSchedulePaymentAsStdClass->securitization_date_index;
            $interestAmounts = json_decode($loanSchedulePaymentAsStdClass->interestAmount,true);
            $endBalances = json_decode($loanSchedulePaymentAsStdClass->endBalance,true);
			
            foreach ($studyDates as $currentMonthIndex ) {
					$currentYearIndex = $monthsWithItsYear[$currentMonthIndex]??null;
				$currentYearOrMonthIndex = $isMonthlyStudy ? $currentMonthIndex : $currentYearIndex ;
				$interestAmount = $interestAmounts[$currentMonthIndex]??0;
				$endBalance = $endBalances[$currentMonthIndex]??0;
				if(isSecuritized($securitizationDateIndex , $currentMonthIndex)){
					$interestAmount = 0;
					$endBalance = 0 ;
				}
				$currentYearAsString = $yearIndexWithYear[$currentYearOrMonthIndex] ?? null ;
				$currentMonthNumber = $monthsWithItsNumbers[$currentMonthIndex]??null;
				$currentYearOrMonthAsString = $isMonthlyStudy ? $currentMonthNumber : $currentYearAsString; 
                    if ($isPortfolio) {
						$totalEndBalanceForPortfolioPerRevenueType[$revenueStreamType][$currentMonthIndex] = isset($totalEndBalanceForPortfolioPerRevenueType[$revenueStreamType][$currentMonthIndex]) ? $totalEndBalanceForPortfolioPerRevenueType[$revenueStreamType][$currentMonthIndex] + $endBalance : $endBalance;
                        $salesRevenuePerTypes[$revenueStreamType][$currentMonthIndex] =  isset($salesRevenuePerTypes[$revenueStreamType][$currentMonthIndex]) ? $salesRevenuePerTypes[$revenueStreamType][$currentMonthIndex] + $interestAmount : $interestAmount;
                        $salesRevenuePerTypes['total_revenue'][$currentMonthIndex] =  isset($salesRevenuePerTypes['total_revenue'][$currentMonthIndex]) ? $salesRevenuePerTypes['total_revenue'][$currentMonthIndex] + $interestAmount : $interestAmount;
                        $tableDataFormatted[0]['sub_items'][$revenueStreamType]['data'][$currentMonthIndex] = $salesRevenuePerTypes[$revenueStreamType][$currentMonthIndex];
						$resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString] = isset($resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString]) ? $resultPerRevenueStreamType[$revenueStreamType][$currentYearOrMonthAsString] + $interestAmount : $interestAmount;
						$resultPerRevenueStreamType['all'][$currentYearOrMonthAsString] = isset($resultPerRevenueStreamType['all'][$currentYearOrMonthAsString]) ? $resultPerRevenueStreamType['all'][$currentYearOrMonthAsString] + $interestAmount : $interestAmount;

                    } else {
						$formattedResult['interest_cogs'][$currentMonthIndex] = isset($formattedResult['interest_cogs'][$currentMonthIndex]) ? $formattedResult['interest_cogs'][$currentMonthIndex] + $interestAmount : $interestAmount ;
                        $formattedExpenses['cost-of-service']['New Portfolio Interest Expense'][$currentMonthIndex]  = $formattedResult['interest_cogs'][$currentMonthIndex]??0 ;
                        $tableDataFormatted[1]['sub_items']['New Portfolio Interest Expense']['data'][$currentMonthIndex] =$formattedExpenses['cost-of-service']['New Portfolio Interest Expense'][$currentMonthIndex] ;
                    }
            }
            
        }
		
			
		foreach($totalEndBalanceForPortfolioPerRevenueType as $revenueStreamType => $totalPortfolioEndBalance){
			$study->recalculateMonthlyAndAccumulatedEcl($revenueStreamType,$totalPortfolioEndBalance);
		}
		
		$interestCosts  = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('direct_factoring_breakdowns')->where('study_id',$study->id)->pluck('bank_interest_expense')->toArray();
		foreach($interestCosts as $interestCost){
			$interestCost = json_decode($interestCost,true);
			foreach($interestCost as $dateIndex => $value){
				$tableDataFormatted[1]['sub_items']['New Portfolio Interest Expense']['data'][$dateIndex] = isset($tableDataFormatted[1]['sub_items']['New Portfolio Interest Expense']['data'][$dateIndex]) ? $tableDataFormatted[1]['sub_items']['New Portfolio Interest Expense']['data'][$dateIndex] + $value : $value  ;
			}
		}

		// securitaization 
		$securitizationLoanSchedules = SecuritizationLoanSchedule::where('study_id', $study->id)->get();
		  $securitizationBankLoanSettlements = [];
        $securitizationBankEarlySettlements = [];
        $securitizationGainOrLosses = [];
		$securitizationExpenses = [];
		 $securitizationCollectionRevenues = [];
        foreach ($securitizationLoanSchedules as $securitizationLoanSchedule) {
            $bankPortfolioEndBalance = $securitizationLoanSchedule->bank_portfolio_end_balance_sum;
            $bankPortfolioEarlySettlement = $securitizationLoanSchedule->early_settlements_expense_amount;
            $securitizationGainOrLoss = $securitizationLoanSchedule->securitization_profit_or_loss;
			$collectionRevenueAmounts = $securitizationLoanSchedule->collection_revenue_amounts?:[];
            $securitizationExpense = $securitizationLoanSchedule->securitization_expense_amount;
            $securitization = $securitizationLoanSchedule->securitization;
            $securitizationDateAsIndex = $securitization->securitization_date;
            $securitizationBankLoanSettlements[$securitizationDateAsIndex] = isset($securitizationBankLoanSettlements[$securitizationDateAsIndex]) ? $securitizationBankLoanSettlements[$securitizationDateAsIndex] +  $bankPortfolioEndBalance : $bankPortfolioEndBalance;
            $securitizationBankEarlySettlements[$securitizationDateAsIndex] = isset($securitizationBankEarlySettlements[$securitizationDateAsIndex]) ? $securitizationBankEarlySettlements[$securitizationDateAsIndex] +  $bankPortfolioEarlySettlement : $bankPortfolioEarlySettlement;
            $securitizationExpenses[$securitizationDateAsIndex] = isset($securitizationExpenses[$securitizationDateAsIndex]) ? $securitizationExpenses[$securitizationDateAsIndex] +  $securitizationExpense : $securitizationExpense;
			foreach ($collectionRevenueAmounts as $dateAsIndex => $collectionRevenue) {
                $securitizationCollectionRevenues[$dateAsIndex] = isset($securitizationCollectionRevenues[$dateAsIndex]) ? $securitizationCollectionRevenues[$dateAsIndex] +  $collectionRevenue : $collectionRevenue;
            }
			// if($securitizationGainOrLoss < 0){
				$securitizationGainOrLoss = $securitizationGainOrLoss ;
				$securitizationGainOrLosses[$securitizationDateAsIndex] = isset($securitizationGainOrLosses[$securitizationDateAsIndex]) ? $securitizationGainOrLosses[$securitizationDateAsIndex] +  $securitizationGainOrLoss : $securitizationGainOrLoss;
				
				
			// }
        }
		// foreach()
		if(count($securitizationGainOrLosses)){
			$tableDataFormatted[0]['sub_items']['securitization-gain-or-loss']['options']['title'] = __('Securitization Gain / (Loss)');
			$tableDataFormatted[0]['sub_items']['securitization-gain-or-loss']['data']= $securitizationGainOrLosses;
			
			
			
		}
		// $tableDataFormatted[0]['sub_items']['securitization-gain-or-loss']['year_total']= HArr::sumPerYearIndex($securitizationGainOrLosses, $yearWithItsMonths);
		
		if(count($securitizationGainOrLosses)){
			$tableDataFormatted[0]['sub_items']['securitization-collection-revenues']['options']['title'] = __('Securitization Collection Revenues');
			$tableDataFormatted[0]['sub_items']['securitization-collection-revenues']['data']= $securitizationCollectionRevenues;
		}
		
						
        $monthlyAdminFees = EclAndNewPortfolioFundingRate::where('study_id', $study->id)->get([
            'monthly_admin_fees_amounts'])->toArray();
        $monthAdminFees = array_column($monthlyAdminFees, 'monthly_admin_fees_amounts');
        $monthAdminFees = HArr::sumAtDates($monthAdminFees, $studyDates);
        
        $tableDataFormatted[0]['sub_items']['monthly-admin-fees']['data'] = $monthAdminFees;
        $tableDataFormatted[0]['sub_items']['monthly-admin-fees']['options']['title'] = __('Monthly Admin Fees');
		
		
        $interestCashSurplusAmounts =$incomeStatementReport? $incomeStatementReport->interest_cash_surplus : [];
		
        
        $title = __('Cash Surplus Interest');
        $tableDataFormatted[0]['sub_items'][$title]['options'] =array_merge([
            'title'=>$title
        ], $defaultNumericInputClasses);
        $tableDataFormatted[0]['sub_items'][$title]['data'] = $interestCashSurplusAmounts;
        $tableDataFormatted[0]['sub_items'][$title]['year_total'] = HArr::sumPerYearIndex($interestCashSurplusAmounts, $yearWithItsMonths);
  
		
		
		
        foreach($tableDataFormatted[0]['sub_items']?? [] as $id => $subItemArr){
			$tableDataFormatted[0]['sub_items'][$id]['year_total'] =	HArr::sumPerYearIndex($subItemArr['data']??[], $yearWithItsMonths);
		}
		
        
        $totalSalesRevenues = Harr::calculateTotalFromSubItems($tableDataFormatted[0]['sub_items']??[]) ;
        
        $yearWithItsMonths=$study->getYearIndexWithItsMonths();
               
               
        $tableDataFormatted[0]['main_items']['sales-revenue']['data'] = $totalSalesRevenues;
        $tableDataFormatted[0]['main_items']['sales-revenue']['year_total'] =$totalSalesRevenuesPerYears =  HArr::sumPerYearIndex($totalSalesRevenues, $yearWithItsMonths);
        $tableDataFormatted[0]['main_items']['growth-rate']['data'] = Harr::calculateGrowthRate($totalSalesRevenues);
        $tableDataFormatted[0]['main_items']['growth-rate']['year_total'] =  HArr::calculateGrowthRate($totalSalesRevenuesPerYears);
               
   
    
       
		if(array_sum($odaInterestExpenses)){
			$tableDataFormatted[1]['sub_items'][$odasInterestExpenseText]['data'] = $odaInterestExpenses;
			$tableDataFormatted[1]['sub_items'][$odasInterestExpenseText]['year_total'] = HArr::sumPerYearIndex($odaInterestExpenses, $yearWithItsMonths);
		}
		
        $expenses = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses')->where('study_id',$study->id)->join('expense_names', 'expense_names.id', '=', 'expenses.expense_name_id')->selectRaw('expenses.expense_category,expense_names.name as name,expenses.relation_name,expenses.monthly_repeating_amounts,expenses.total_after_vat,payload')->where('expenses.model_id', $study->id)->where('expenses.model_name', 'Study')->get()->toArray();
        $columnPerTypes = Expense::getColumnMapping();
        $salaryExpensesForCategories = Manpower::getSalaryExpensesPerCategory($monthsWithItsYear, $study->id, $company->id);
        foreach ($salaryExpensesForCategories as $manpowerCategory => $salaryExpensesForCategory) {
            foreach ($salaryExpensesForCategory as $monthIndex => $value) {
                $currentOrderIndex = $orderIndexPerExpenseCategory[$manpowerCategory];
                $currentValue = $salaryExpensesForCategories[$manpowerCategory][$monthIndex] ?? 0 ;
                $formattedExpenses[$manpowerCategory]['Manpower Salaries'][$monthIndex] = isset($formattedExpenses[$manpowerCategory]['Manpower Salaries'][$monthIndex]) ? $formattedExpenses[$manpowerCategory]['Manpower Salaries'][$monthIndex] +  $currentValue : $currentValue;
                $currentMonthManpowerTotal = $formattedExpenses[$manpowerCategory]['Manpower Salaries'][$monthIndex];
                $tableDataFormatted[$currentOrderIndex]['sub_items']['Manpower Salaries']['data'][$monthIndex] =$currentMonthManpowerTotal ;
            }
        }
		
		
		if (count($securitizationBankEarlySettlements)) {
            $title = __('Securitization Bank Early Settlement Expense');
            $tableDataFormatted[1]['sub_items'][$title]['options'] =array_merge([
               'title'=>$title
            ], $defaultNumericInputClasses);
            $tableDataFormatted[1]['sub_items'][$title]['data'] = $securitizationBankEarlySettlements;
            $tableDataFormatted[1]['sub_items'][$title]['year_total'] = HArr::sumPerYearIndex($securitizationBankEarlySettlements, $yearWithItsMonths);
        }
		if (count($securitizationExpenses)) {
            $title = __('Securitization Expenses');
            $tableDataFormatted[1]['sub_items'][$title]['options'] =array_merge([
               'title'=>$title
            ], $defaultNumericInputClasses);
            $tableDataFormatted[1]['sub_items'][$title]['data'] = $securitizationExpenses;
            $tableDataFormatted[1]['sub_items'][$title]['year_total'] = HArr::sumPerYearIndex($securitizationExpenses, $yearWithItsMonths);
        }
        foreach ($expenses as $expense) {
        
            $name = $expense->name;
            $relationName = $expense->relation_name;
            $expenseCategory = $expense->expense_category;
            
            $currentOrderIndex = $orderIndexPerExpenseCategory[$expenseCategory];
            $tableDataFormatted[$currentOrderIndex]['main_items'][$expenseCategory]['options']['title'] =$expenseMainTitlesMapping[$expenseCategory] ;
            $tableDataFormatted[$currentOrderIndex]['sub_items'][$name]['options']['title'] =$name ;
            $currentColumnName = $columnPerTypes[$relationName];
          
      
            $monthlyExpenses = json_decode($expense->{$currentColumnName},true);
            foreach ($yearWithItsIndexes as $yearIndex => $monthIndexWithActive) {
                foreach ($monthIndexWithActive as $monthIndex=> $isActiveIndex) {
                    $currentMonthManpowerTotal = 0 ;
                    $monthlyExpense = $relationName == 'one_time_expense' && isset($monthlyExpenses['monthly_one_time']) ? ($monthlyExpenses['monthly_one_time']) : $monthlyExpenses;
                    $currentMonthlyExpenseValue = $monthlyExpense[$monthIndex]??0 ;
                    $tableDataFormatted[$currentOrderIndex]['sub_items'][$name]['data'][$monthIndex] = isset($tableDataFormatted[$currentOrderIndex]['sub_items'][$name]['data'][$monthIndex]) ? $tableDataFormatted[$currentOrderIndex]['sub_items'][$name]['data'][$monthIndex] + $currentMonthlyExpenseValue:$currentMonthlyExpenseValue ;
                }
                
            }
   
        }
		

        $totalCostOfService = Harr::calculateTotalFromSubItems($tableDataFormatted[1]['sub_items']??[]) ;
        $tableDataFormatted[1]['main_items']['cost-of-service']['data'] = $totalCostOfService;
        $tableDataFormatted[1]['main_items']['cost-of-service']['year_total'] =$totalCostOfServicePerYear =  HArr::sumPerYearIndex($totalCostOfService, $yearWithItsMonths);
        $tableDataFormatted[1]['main_items']['% Of Revenue']['data'] = $currentData =  HArr::calculatePercentageOf($totalSalesRevenues, $totalCostOfService);
	
        $tableDataFormatted[1]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $totalCostOfServicePerYear);
	
        
		
		

		

		
		// ddddddddddddddddddddddddddddddddddd
		
		
		foreach($tableDataFormatted[1]['sub_items']?? [] as $id => $subItemArr){
			$tableDataFormatted[1]['sub_items'][$id]['year_total'] =	$currentTotalPerYear = HArr::sumPerYearIndex($subItemArr['data']??[], $yearWithItsMonths);
		}
		
               

        $totalGrossProfit = HArr::subtractAtDates([$totalSalesRevenues,$totalCostOfService], $sumKeys) ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['data'] =  $totalGrossProfit ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['year_total'] = $grossProfitTotalPerYear = HArr::sumPerYearIndex($totalGrossProfit, $yearWithItsMonths);
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['gross-profit']['options']['title'] = __('Gross Profit');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalGrossProfit) ;
        $tableDataFormatted[$grossProfitOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $grossProfitTotalPerYear);
    

        
        
        $eclExpenses = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('ecl_and_new_portfolio_funding_rates')->where('study_id', $study->id)->pluck('monthly_ecl_values')->toArray();
        $totalEclExpenses = [];
        $title = __('ECL Expense')  ;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$title]['options'] =array_merge([
            'title'=>$title
        ], $defaultNumericInputClasses);
        foreach ($eclExpenses as $currentData) {
            $currentData = (array) json_decode($currentData);
            $totalEclExpenses  = HArr::sumAtDates([$totalEclExpenses,$currentData], $sumKeys);
        }
		$incomeStatementReport = $study->incomeStatementReport;
		$totalExistingEcl = $incomeStatementReport ? $incomeStatementReport->existing_ecl_expenses : [];
		$totalEclExpenses = HArr::sumAtDates([$totalEclExpenses,$totalExistingEcl],$sumKeys);
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$title]['data'] = $totalEclExpenses;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$title]['year_total'] = HArr::sumPerYearIndex($totalEclExpenses, $yearWithItsMonths);
        $totalDepreciationExpenses = [];
        $fixedAssetDepreciations = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets')->where('study_id', $study->id)->pluck('total_monthly_depreciations')->toArray();
        $title = __('Depreciation Expense')  ;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$depreciationKey]['options'] =array_merge([
            'title'=>$title
        ], $defaultNumericInputClasses);
        foreach ($fixedAssetDepreciations as $revenueType => $currentData) {
            $currentData = json_decode($currentData, true);
            $totalDepreciationExpenses  = HArr::sumAtDates([$totalDepreciationExpenses,$currentData], $sumKeys);
        }
		
		  $fixedAssetOpeningBalancesAdminDepreciations = [];
        $fixedAssetOpeningBalancesAdminDepreciations = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_opening_balances')->where('study_id', $study->id)->pluck('statement')->toArray();
        array_walk($fixedAssetOpeningBalancesAdminDepreciations, function (&$value) {
			 $value = json_decode($value,true)['monthly_depreciation']??[];
        });
		$fixedAssetOpeningBalancesAdminDepreciations = HArr::sumAtDates($fixedAssetOpeningBalancesAdminDepreciations,$sumKeys);
		
	
        $totalFixedAssetAdminDepreciation = HArr::sumAtDates([$fixedAssetOpeningBalancesAdminDepreciations,$totalDepreciationExpenses], $sumKeys);
		
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$depreciationKey]['data'] = $totalFixedAssetAdminDepreciation;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items'][$depreciationKey]['year_total'] = $totalDepreciationExpensesPerYears = HArr::sumPerYearIndex($totalFixedAssetAdminDepreciation, $yearWithItsMonths);
        $totalEclAndDepreciationExpenses = Harr::calculateTotalFromSubItems($tableDataFormatted[$eclAndDepreciationOrderIndex]['sub_items']??[]);
        
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['main_items'][$eclAndDepreciationKey]['data'] =  $totalEclAndDepreciationExpenses ;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['main_items'][$eclAndDepreciationKey]['year_total'] = $totalEclAndDepreciationExpensesPerYear = HArr::sumPerYearIndex($totalEclAndDepreciationExpenses, $yearWithItsMonths);
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['main_items'][$eclAndDepreciationKey]['options']['title'] = __('ECL & Depreciation Cost');
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalEclAndDepreciationExpenses) ;
        $tableDataFormatted[$eclAndDepreciationOrderIndex]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $totalEclAndDepreciationExpensesPerYear);

        // sub items total per year
        $currentSubItems = $tableDataFormatted[$otherOperationExpenseOrder]['sub_items']??[];
        foreach ($currentSubItems as $subItemName => $subItemData) {
            $tableDataFormatted[$otherOperationExpenseOrder]['sub_items'][$subItemName]['year_total'] = HArr::sumPerYearIndex($subItemData['data']??[], $yearWithItsMonths);
        }
        // $tableDataFormatted[$currentOrderIndex]['sub_items'][$name]['year_total']
        $totalOtherOperatingExpenses = HArr::calculateTotalFromSubItems($currentSubItems) ;
        $tableDataFormatted[$otherOperationExpenseOrder]['main_items']['other-operation-expense']['data'] =  $totalOtherOperatingExpenses ;
        $tableDataFormatted[$otherOperationExpenseOrder]['main_items']['other-operation-expense']['year_total'] = $otherOperatingExpensesTotalPerYear = HArr::sumPerYearIndex($totalOtherOperatingExpenses, $yearWithItsMonths);
        $tableDataFormatted[$otherOperationExpenseOrder]['main_items']['other-operation-expense']['options']['title'] = __('Other Operation Expense');
        $tableDataFormatted[$otherOperationExpenseOrder]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalOtherOperatingExpenses) ;
        $tableDataFormatted[$otherOperationExpenseOrder]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $otherOperatingExpensesTotalPerYear);
        
        
        
        $currentSubItems = $tableDataFormatted[$marketingExpenseOrder]['sub_items']??[];
        foreach ($currentSubItems as $subItemName => $subItemData) {
            $tableDataFormatted[$marketingExpenseOrder]['sub_items'][$subItemName]['year_total'] = HArr::sumPerYearIndex($subItemData['data']??[], $yearWithItsMonths);
        }
        $totalMarketingExpenses = HArr::calculateTotalFromSubItems($tableDataFormatted[$marketingExpenseOrder]['sub_items']??[]) ;
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['marketing-expense']['data'] =  $totalMarketingExpenses ;
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['marketing-expense']['year_total'] = $totalMarketingExpensesPerYear = HArr::sumPerYearIndex($totalMarketingExpenses, $yearWithItsMonths);
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['marketing-expense']['options']['title'] = __('Marketing Expenses');
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalMarketingExpenses) ;
        $tableDataFormatted[$marketingExpenseOrder]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $totalMarketingExpensesPerYear);
        
        
        
        $currentSubItems = $tableDataFormatted[$salesExpenseOrder]['sub_items']??[];
        foreach ($currentSubItems as $subItemName => $subItemData) {
            $tableDataFormatted[$salesExpenseOrder]['sub_items'][$subItemName]['year_total'] = HArr::sumPerYearIndex($subItemData['data']??[], $yearWithItsMonths);
        }
        $totalSalesExpenses = HArr::calculateTotalFromSubItems($tableDataFormatted[$salesExpenseOrder]['sub_items']??[]) ;
        $tableDataFormatted[$salesExpenseOrder]['main_items']['sales-expense']['data'] =  $totalSalesExpenses ;
        $tableDataFormatted[$salesExpenseOrder]['main_items']['sales-expense']['year_total'] = $totalSalesExpensesPerYear = HArr::sumPerYearIndex($totalSalesExpenses, $yearWithItsMonths);
        $tableDataFormatted[$salesExpenseOrder]['main_items']['sales-expense']['options']['title'] = __('Sales Expense');
        $tableDataFormatted[$salesExpenseOrder]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalSalesExpenses) ;
        $tableDataFormatted[$salesExpenseOrder]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $totalSalesExpensesPerYear);
        
            
        
        $currentSubItems = $tableDataFormatted[$generalExpenseOrder]['sub_items']??[];
	
        foreach ($currentSubItems as $subItemName => $subItemData) {
            $tableDataFormatted[$generalExpenseOrder]['sub_items'][$subItemName]['year_total'] = HArr::sumPerYearIndex($subItemData['data']??[], $yearWithItsMonths);
        }
        $totalGeneralExpenses = HArr::calculateTotalFromSubItems($tableDataFormatted[$generalExpenseOrder]['sub_items']??[]) ;
	
        $tableDataFormatted[$generalExpenseOrder]['main_items']['general-expense']['data'] =  $totalGeneralExpenses ;
        $tableDataFormatted[$generalExpenseOrder]['main_items']['general-expense']['year_total'] = $totalGeneralExpensesPerYear = HArr::sumPerYearIndex($totalGeneralExpenses, $yearWithItsMonths);
        $tableDataFormatted[$generalExpenseOrder]['main_items']['general-expense']['options']['title'] = __('General Expense');
        $tableDataFormatted[$generalExpenseOrder]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalGeneralExpenses) ;
        $tableDataFormatted[$generalExpenseOrder]['main_items']['% Of Revenue']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $totalGeneralExpensesPerYear);
        $totalSGANDA = HArr::sumAtDates([$totalGeneralExpenses,$totalSalesExpenses,$totalMarketingExpenses,$totalOtherOperatingExpenses], $sumKeys);
        
        /**
         * * Five Item
         */
          
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['options']['title'] = __('EBITDA');
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        // $fixedAssetAdminDepreciations = [];
      
        $totalDepreciation = HArr::sumAtDates([$totalFixedAssetAdminDepreciation,$totalGrossProfit], $sumKeys);
        $editda = HArr::subtractAtDates([$totalDepreciation,$totalSGANDA], $sumKeys) ;
	
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['data'] = $editda;
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['ebitda']['year_total'] =$ebitdaTotalPerYear= HArr::sumPerYearIndex($editda, $yearWithItsMonths);
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $editda);
        $tableDataFormatted[$ebitdaOrderIndex]['main_items']['% Of Revenue']['year_total'] = $editdaRevenuePercentage = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $ebitdaTotalPerYear);
        /**
         * * End Five Item
         */
        
        /**
         * * Start Sixth Item
         */
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['options']['title'] = __('EBIT');
        $ebit = HArr::subtractAtDates([$totalGrossProfit,$totalSGANDA,$totalEclAndDepreciationExpenses], $sumKeys) ;
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['data'] = $ebit ;
        $tableDataFormatted[$ebitOrderIndex]['main_items']['ebit']['year_total'] =$ebitTotalPerYear= HArr::sumPerYearIndex($ebit, $yearWithItsMonths);
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($totalSalesRevenues, $ebit);
        $tableDataFormatted[$ebitOrderIndex]['main_items']['% Of Revenue']['year_total'] = $grossProfitRevenuePercentages =$editRevenuePercentage= HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $ebitTotalPerYear);
        /**
         * * End  Sixth Item
         */
        
        
        /**
        * * Start Seven Item
        */
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['finance_exp']['options'] = array_merge([
            'title'=>__('Finance Expense')
        ], $defaultNumericInputClasses);
      
        $openingLoans = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('long_term_loan_opening_balances')->where('study_id', $study->id)->pluck('interests')->toArray();
        $openingLoansTotal=[];
        foreach ($openingLoans as $openingLoanInterest) {
            $openingLoansTotal= HArr::sumAtDates([json_decode($openingLoanInterest,true),$openingLoansTotal], $sumKeys);
        }
        if (count($openingLoansTotal)) {
            $tableDataFormatted[$financialExpenseOrderIndex]['sub_items'][__('Opening Balance Loans Interests')]['data'] = $openingLoansTotal;
            $tableDataFormatted[$financialExpenseOrderIndex]['sub_items'][__('Opening Balance Loans Interests')]['year_total'] = HArr::sumPerYearIndex($openingLoansTotal, $yearWithItsMonths);
        }
        
		$fixedAssetLoanInterestExpenses = $incomeStatementReport ? $incomeStatementReport->fixed_asset_loan_interest_expenses : [];
		  if ($fixedAssetLoanInterestExpenses && count($fixedAssetLoanInterestExpenses)) {
            $tableDataFormatted[$financialExpenseOrderIndex]['sub_items'][__('Fixed Assets Loans Interests')]['data'] = $fixedAssetLoanInterestExpenses;
            $tableDataFormatted[$financialExpenseOrderIndex]['sub_items'][__('Fixed Assets Loans Interests')]['year_total'] = HArr::sumPerYearIndex($fixedAssetLoanInterestExpenses, $yearWithItsMonths);
        }
        
        
        $totalFinanceExpense = HArr::sumAtDates(array_column($tableDataFormatted[$financialExpenseOrderIndex]['sub_items']??[], 'data'), $sumKeys);
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['finance_exp']['data'] = $totalFinanceExpense;
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['finance_exp']['year_total'] = $financeExpenseTotalPerYear = HArr::sumPerYearIndex($totalFinanceExpense, $yearWithItsMonths);
        
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['revenues-percentage']['options'] = array_merge([
            'title'=>__('%/Revenues')
        ], $defaultPercentageInputClasses);
        
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['revenues-percentage']['data'] =  HArr::calculatePercentageOf($totalSalesRevenues, $totalFinanceExpense) ;
        $tableDataFormatted[$financialExpenseOrderIndex]['main_items']['revenues-percentage']['year_total'] = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $financeExpenseTotalPerYear);
        /**
         * * End Seven Item
         */
        
        
        /**
         * * Start Eight Item
         */
        
        $ebt = HArr::subtractAtDates([$ebit,$totalFinanceExpense], $sumKeys);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['options']['title'] = __('EBT');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['data'] = $ebt;
        $tableDataFormatted[$ebtOrderIndex]['main_items']['ebt']['year_total'] =$ebtTotalPerYear = HArr::sumPerYearIndex($ebt, $yearWithItsMonths);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['data']=  HArr::calculatePercentageOf($totalSalesRevenues, $ebt);
        $tableDataFormatted[$ebtOrderIndex]['main_items']['% Of Revenue']['year_total'] = $ebtRevenuePercentagePerYear= HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $ebtTotalPerYear);
       
        
        /**
         * * End Eight Item
         */
        
        
        /**
        * * Start Nine Item
        */
		
        // $corporateTaxesRate = $study->corporate_taxes_rate/100;
		$annuallyCorporateTaxes = $incomeStatementReport ? $incomeStatementReport->corporate_taxes: [];
        // $annuallyCorporateTaxes =  HArr::MultiplyWithNumberIfPositiveAndZeroOtherValues($ebt, $corporateTaxesRate);
        // $annuallyCorporateTaxes = HArr::sumPerYearIndex($annuallyCorporateTaxes, $yearWithItsMonths);
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['options']['title'] = __('Corporate Taxes');
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['data'] = $annuallyCorporateTaxes;
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['corporate-taxes']['year_total'] = HArr::sumPerYearIndex($annuallyCorporateTaxes,$yearWithItsMonths);
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['data']=  [];
        $tableDataFormatted[$corporateTaxesOrderIndex]['main_items']['% Of Revenue']['year_total'] = $corporateTaxesRevenuePercentage=HArr::calculatePercentageOf($totalSalesExpensesPerYear, $annuallyCorporateTaxes);
		$study->incomeStatementReport->update([
			'corporate_taxes'=>$annuallyCorporateTaxes
		]);
        $totalProductsWithholdAmounts = [];
        
        $dateIndexWithDate = $study->getDateIndexWithDate();
        $calculatedCorporateTaxesPerYear = HArr::sumPerYearIndex($annuallyCorporateTaxes, $yearWithItsMonths) ;
        foreach ($calculatedCorporateTaxesPerYear as $dateIndex => &$value) {
            if ($value < 0) {
                $value =0 ;
            }
        }
		
		// min('','');
		// array_merge('');
        $corporateTaxesPayable = $study->getCorporateTaxesPayable();
        $studyStartDateAsMonthNumber = array_values($study->getDateWithMonthNumber())[0];
		$dates = $study->getStudyDates();
		
        $corporateTaxesStatement  = Study::calculateCorporateTaxesStatement($dates,$totalProductsWithholdAmounts, $calculatedCorporateTaxesPerYear, $corporateTaxesPayable, $dateIndexWithDate, $studyStartDateAsMonthNumber);

        
      
    
        /**
         * * End Nine Item
         */
        
        /**
         * * Start  Sixth Item
         */
        
        $annuallyNetProfit = HArr::subtractAtDates([$ebt,$annuallyCorporateTaxes], $sumKeys);
        $netProfit = $annuallyNetProfit;
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['options']['title'] = __('Net Profit');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['data'] = $netProfit;
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['net-profit']['year_total'] = $netProfitTotalPerYear = HArr::sumPerYearIndex($annuallyNetProfit, $yearWithItsMonths);
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['options']['title'] = __('% Of Revenue');
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['data'] = HArr::calculatePercentageOf($totalSalesRevenues, $netProfit);
        $tableDataFormatted[$netProfitOrderIndex]['main_items']['% Of Revenue']['year_total'] = $netProfitRevenuePercentage = HArr::calculatePercentageOf($totalSalesRevenuesPerYears, $netProfitTotalPerYear);
        	
        $retainedEarningOpening = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('equity_opening_balances')->where('study_id', $study->id)->first();
        $retainedEarningOpening = $retainedEarningOpening ? $retainedEarningOpening->retained_earnings : 0;
        // $retainedEarning = HArr::calculateRetainEarning($retainedEarningOpening,$ebt);
        $retainedEarning = HArr::calculateRetainEarning($retainedEarningOpening, $netProfit);
        $statementData = [
            'monthly_corporate_taxes_statements'=>$corporateTaxesStatement,
            'monthly_net_profit'=>$netProfit,
             'accumulated_retained_earnings'=>$retainedEarning,
            'study_id'=>$study->id,
			'ebit'=>$ebitTotalPerYear,
			'total_depreciation'=>$totalDepreciationExpensesPerYears,
        ];
        $study->incomeStatement ?  $study->incomeStatement->update($statementData) : $study->incomeStatement()->create($statementData);
        $studyMonthsForViews=$study->getStudyDurationPerYearFromIndexesForView();
        ksort($tableDataFormatted);
		if($onlyViewVars){
			return [
				'tableDataFormatted'=>$tableDataFormatted,
				'resultPerRevenueStreamType'=>$resultPerRevenueStreamType??[],
				'studyMonthsForViews'=>$studyMonthsForViews,
				'defaultClasses'=>$defaultClasses
			];
		}
	
		$viewVars = [
            'company'=>$company,
            'studyMonthsForViews'=>$studyMonthsForViews,
            'title'=>__('Forecasted Income Statement'),
            'tableTitle'=>__('Forecasted Income Statement'),
            'createRoute'=>route('create.financial.planning.study', ['company'=>$company->id]),
            'studyMonths'=>$study->getStudyDurationPerYearFromIndexes(),
            'study'=>$study,
            'salesRevenuePerTypes'=>$salesRevenuePerTypes,
            'tableDataFormatted'=>$tableDataFormatted,
            'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
            'monthsWithItsYear'=>$monthsWithItsYear,
            'defaultClasses'=>$defaultClasses,
			'nextButton' => [
				'link'=>route('cash.in.out.flow.result',['company'=>$company->id,'study'=>$study->id]),
				'title'=>__('Go To Cashflow')
			]
        ] ;
        return view('non_banking_services.income-statement.cash-flow',$viewVars );
    }
	public function viewPreviousTwoYearsIncomeStatement(Company $company , Study $study)
	{
		$viewVars = $this->index($company,$study,true);
		$viewVars = array_merge($viewVars , [
			'title'=>$title = __('Previous Two Years Income Statements'),
			'tableTitle'=>$title,
			'study'=>$study,
			'previous_years_income_statement'=>$study->previous_years_income_statement,
			'financialYearEndMonthNumber'=>$study->getFinancialYearEndMonthNumber(),
			'studyMonthsForViews'=>[
				2023,2024
			]
		]);
		  return view('non_banking_services.income-statement.previous_two_years_income_statements',$viewVars );
	}
	public function storePreviousTwoYearsIncomeStatement(Request $request, Company $company,Study $study )
	{
		$study->update([
			'previous_years_income_statement'=>$request->except(['_token'])
		]);
		return redirect()->back()->with('success',__('Saved'));
	}
    // protected function getViewVars(Company $company, Study $model = null):array
    // {
    //     $actionRoute=isset($model) ? route('update.study', [$company->id , $model->id]) : route('store.financial.planning.study', ['company'=>$company->id]);
    //     return [
    //         'company'=>$company,
    //         'title'=>$company->getName().' ' . __(' Financial Plan'),
    //         'model'=>$model,
    //         'actionRoute'=>$actionRoute,
    //         'navigators' => [],
    //     ];
    // }
    // public function create(Company $company, Request $request)
    // {
    //     return view('financial_planning.study.form', $this->getViewVars($company));
    // }
    // public function store(Company $company, Request $request, Study $study = null)
    // {
    //     $request->merge([
    //         'study_start_date'=>Carbon::make($request->get('study_start_date'))->format('Y-m-d'),
    //         'study_end_date'=>Carbon::make($request->get('study_end_date'))->format('Y-m-d'),
    //         'operation_start_date'=>Carbon::make($request->get('operation_start_date'))->format('Y-m-d'),

            
    //     ]);
    //     $data = $request->except(['_token']) ;
    //     $model = null ;
    //     if (is_null($study)) {
    //         $model = Study::create($data);
    //     } else {
    //         $study->update($data);
    //         $model = $study;
    //     }

    //     /**
    //      * @var Study $model
    //      */
    //     $datesAsStringAndIndex = $model->getDatesAsStringAndIndex();
    //     $studyDates = $model->getStudyDates() ;
    //     $datesAndIndexesHelpers = $model->datesAndIndexesHelpers($studyDates);
    //     $datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex'];
    //     $yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear'];
    //     $dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate'];
    //     $dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber'];
    //     $model->updateStudyAndOperationDates($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
    //     return response()->json([
    //         'redirectTo'=>route('create.general.assumption', ['company'=>$company->id,'study'=>$model->id])
    //     ]);
    // }
    // public function edit(Company $company, Request $request, Study $study)
    // {
    //     return view('financial_planning.study.form', $this->getViewVars($company, $study));
    // }
    // public function update(Request $request, Company $company, Study $study)
    // {
    //     return $this->store($company, $request, $study);
    // }
    // public function destroy(Request $request, Company $company, Study $study)
    // {
    //     $study->delete();
    //     return redirect()->back()->with('success', __('Study Has Been Deleted Successfully'));
    // }
}
