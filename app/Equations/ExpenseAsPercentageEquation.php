<?php
namespace App\Equations;

use App\Helpers\HArr;
use App\Models\NonBankingService\Expense;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ExpenseAsPercentageEquation
{
    public function calculate(int $studyId, string $percentageOf, array $revenueStreamType, array $categoryIds, int $startDateAsIndex, int $endDateAsIndex, float $monthlyRate, string $paymentTermType, float $vatRate, bool $isDeductible, float $withholdTaxRate, bool $isSensitivity = false):array
    {
        $loanSchedulePaymentTableName = $isSensitivity ? 'sensitivity_loan_schedule_payments':'loan_schedule_payments';
        $hasLeasing = in_array('has_leasing', $revenueStreamType) ;
        $hasIjara = in_array('has_ijara_mortgage', $revenueStreamType) ;
        $hasReverseFactoring = in_array('has_reverse_factoring', $revenueStreamType) ;
        $hasPortfolioMortgage = in_array('has_portfolio_mortgage', $revenueStreamType) ;
        $hasDirectFactoring = in_array('has_direct_factoring', $revenueStreamType) ;
        $hasMicrofinance = in_array('has_micro_finance', $revenueStreamType) ;
        $hasConsumerfinance = in_array('has_consumer_finance', $revenueStreamType) ;


        $dates = range($startDateAsIndex, $endDateAsIndex);
        $resultArrs = [];
        $result = [];
                
        $expensePerContract = Expense::getExpensePerContract($revenueStreamType, $categoryIds, $studyId, 'monthly_loan_amounts');
        $selectedRevenueStreamTypes = $expensePerContract['selectedRevenueStreamTypes'];
        
        $vats = [];
        $withholds = [];
        if ($percentageOf == 'contract') {
            $resultArrs = $expensePerContract['result'];
        } else {
            if ($hasLeasing || $hasIjara || $hasReverseFactoring || $hasPortfolioMortgage || $hasMicrofinance || $hasConsumerfinance) {
                $calculationColumn = [
                    'revenue'=>'interestAmount',
                    'outstanding'=>'endBalance',
                    'collection'=>'schedulePayment'
                ][$percentageOf];
            
			
                $resultArrs = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($loanSchedulePaymentTableName)
                ->whereIn('revenue_stream_type', $selectedRevenueStreamTypes)
                ->where('study_id', $studyId)
                ->where('portfolio_loan_type', 'portfolio')
                ->when(count($categoryIds), function (Builder $builder) use ($categoryIds) {
                    $builder->whereIn('revenue_stream_category_id', $categoryIds);
                })
				->pluck($calculationColumn)->map(function ($item) {
                    return (array)json_decode($item);
                })->toArray();

            }
            if ($hasDirectFactoring) {
                $calculationColumn = [
                    'revenue'=>'interest_revenue',
                    'outstanding'=>'statement_end_balance',
                    'collection'=>'direct_factoring_settlements',
                ][$percentageOf];
        
                $directFactoringAmounts = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('direct_factoring_breakdowns')
                ->where('study_id', $studyId)
                ->when(count($categoryIds), function (Builder $builder) use ($categoryIds) {
                    $builder->whereIn('category', $categoryIds);
                })->pluck($calculationColumn)->map(function ($item) {
                    return (array)json_decode($item);
                })->toArray();

                foreach ($directFactoringAmounts as $directFactoringAmount) {
                    foreach ($directFactoringAmount as $monthIndex => $val) {
                        $valBeforeRate = $monthlyRate / 100 * $val ;
                        $valueAfterVat =  0;
                        if (!$isDeductible) {
                            $valueAfterVat = $valBeforeRate * (1+($vatRate/100));
                        }
                        $result[$monthIndex] = isset($result[$monthIndex]) ? $result[$monthIndex] + $valBeforeRate : $valBeforeRate ;
                        $onlyVatValue = $valueAfterVat -$valBeforeRate ;
                        $withholdValue = $withholdTaxRate / 100 * $valBeforeRate ;
                        $vats[$monthIndex] = isset($vats[$monthIndex]) ? $vats[$monthIndex] + $onlyVatValue : $onlyVatValue;
                        $withholds[$monthIndex] = isset($withholds[$monthIndex]) ? $withholds[$monthIndex] + $withholdValue : $withholdValue;
                    }
                }
    
            }
        }
        
        foreach ($resultArrs as $resultArrItem) {
            foreach ($resultArrItem as $monthIndex => $val) {
                $valBeforeRate = $monthlyRate / 100 * $val ;
                $valueAfterVat =  0;
                if (!$isDeductible) {
                    $valueAfterVat = $valBeforeRate * (1+($vatRate/100));
                }
                $result[$monthIndex] = isset($result[$monthIndex]) ? $result[$monthIndex] + $valBeforeRate : $valBeforeRate ;
                $onlyVatValue = $valueAfterVat -$valBeforeRate ;
                $withholdValue = $withholdTaxRate / 100 * $valBeforeRate ;
                $vats[$monthIndex] = isset($vats[$monthIndex]) ? $vats[$monthIndex] + $onlyVatValue : $onlyVatValue;
                $withholds[$monthIndex] = isset($withholds[$monthIndex]) ? $withholds[$monthIndex] + $withholdValue : $withholdValue;
                    
            }
        }
        $totalWithoutVat = [];
        foreach ($result as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalWithoutVat[$monthIndex] = isset($totalWithoutVat[$monthIndex]) ? $totalWithoutVat[$monthIndex] + $value : $value;
            }
        }
        $totalVat = [];
        foreach ($vats as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalVat[$monthIndex] = isset($totalVat[$monthIndex]) ? $totalVat[$monthIndex] + $value : $value;
            }
        }
        
        $totalWithhold = [];
        foreach ($withholds as $monthIndex=>$value) {
            if ($monthIndex>= $startDateAsIndex && $monthIndex <= $endDateAsIndex) {
                $totalWithhold[$monthIndex] = isset($totalWithhold[$monthIndex]) ? $totalWithhold[$monthIndex] + $value : $value;
            }
        }
        return [
            'total_withhold'=>$totalWithhold ,
            'total_before_vat'=>$totalWithoutVat ,
            'total_vat'=>$totalVat,
            'total_after_vat'=>HArr::sumAtDates([$totalWithoutVat,$totalVat],$dates)
        ];
    }
}
