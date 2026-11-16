<?php

namespace App\ReadyFunctions;

use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Models\Loan;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;
use App\ReadyFunctions\Date;
use Arr;
use Carbon\Carbon;

class CalculateFixedLoanAtEndService
{
    
	/**
	 * * قرض بسعر متغير (الكوريدور + الهامش). $baseRatesMapping: سعر الكوريدور لكل شهر ({'2026-01-01': 22, ...}).
	 * * - القرض بيتسعّر بالسعر الساري في شهر صرفه.
	 * * - في كل شهر السعر بيتغير فيه بعد الصرف ، القسط بيتعاد حسابه من أول صف في جدول القرض في الشهر ده أو بعده ،
	 * *   على الرصيد والمدة اللي فاضلين ، من غير ما يعرف الأسعار اللي جاية. الفترة اللي بعد الصف ده بتتحسب بالسعر الجديد.
	 * * - فترة السماح وعداد الـ step بيكمّلوا من مكانهم: القرض نفس القرض ، السعر بس اللي اتغير.
	 * * مثال: قرض شهري اتصرف نوفمبر 2026 والكوريدور نزل في فبراير 2027: صف فبراير بالسعر القديم ، وقسط مارس (فايدة فبراير)
	 * *       أول قسط بالسعر الجديد. قرض ربع سنوي بيتعاد من أول صف ربع سنوي بعد التغيير.
	 * * (كان بيتعاد تسعيره كل شهر في القايمة ، من صف الدفع اللي بعد الصف اللي اتعاد منه آخر مرة ، فالقرض الربع سنوي
	 * *  كان بيتأخر 3 شهور مع كل شهر ، وفترة السماح والـ step كانوا بيبدأوا من الأول.)
	 */
	 public function __calculateBasedOnDiffBaseRates(array $baseRatesMapping, string $loanType, string $loanStartDate, float $loanAmount, float $marginRate, float $tenor, string $installmentPaymentIntervalName, int $installmentPaymentIntervalValue, float $stepUpRate = 0, ?string $stepUpIntervalName = null, float $stepDownRate = 0, ?string $stepDownIntervalName = null, float $gracePeriod  = 0, int $monthIndex = 0, array $datesAsStringAndIndex = [], array $dateWithDateIndex = [] , $daysCount = null):array
    {
        if ($loanAmount <= 0 || $tenor < 1) {
            return [] ; // a loan without a tenor has no schedule (it is skipped, as it always was)
        }
        // the row of the first installment, the way __calculate places it: the grace in whole installment periods
        // (a quarterly loan with 4 months of grace pays its first installment in month 6, not 7)
        $firstInstallmentIndex = $monthIndex + $installmentPaymentIntervalValue * intdiv((int) $gracePeriod + $installmentPaymentIntervalValue, $installmentPaymentIntervalValue);
        $schedule = [];
        $segmentNumber = 0;
        foreach (HArr::rateChangesFromMonth($baseRatesMapping, $monthIndex, $datesAsStringAndIndex) as $changeIndex => $currentBaseRate) {
            $startIndex = $monthIndex;
            $startDate = $loanStartDate;
            $amount = $loanAmount;
            $remainingTenor = $tenor;
            $remainingGrace = $gracePeriod;
            $installmentsPaid = 0;
            if ($segmentNumber > 0) {
                $startIndex = self::firstRowAtOrAfter($schedule['endBalance'] ?? [], $changeIndex);
                $startDate = is_null($startIndex) ? null : ($dateWithDateIndex[$startIndex] ?? null);
                if (is_null($startDate)) {
                    break; // the change comes after the loan's last row (or after the study's dates)
                }
                $amount = (float) $schedule['endBalance'][$startIndex];
                $remainingTenor = $tenor - ($startIndex - $monthIndex);
                if ($amount <= 0 || $remainingTenor < $installmentPaymentIntervalValue) {
                    break; // the loan is repaid, nothing is left to re-price
                }
                $remainingGrace = max(0, $firstInstallmentIndex - $startIndex - $installmentPaymentIntervalValue);
                $installmentsPaid = $startIndex < $firstInstallmentIndex ? 0 : intdiv($startIndex - $firstInstallmentIndex, $installmentPaymentIntervalValue) + 1;
            }
            $segment = $this->__calculate($schedule, $segmentNumber, $loanType, $startDate, $amount, $currentBaseRate, $marginRate, $remainingTenor, $installmentPaymentIntervalName, $stepUpRate, $stepUpIntervalName, $stepDownRate, $stepDownIntervalName, $remainingGrace, $startIndex, $daysCount, null, $installmentsPaid);
            $schedule = $segment['final_result'] ?? $schedule;
            $segmentNumber++;
        }
        unset($schedule['totals']);
        if ($installmentPaymentIntervalName != 'monthly' && $schedule !== []) {
            $schedule = $this->extendPerMonth($schedule, $installmentPaymentIntervalValue, is_null($daysCount) ? $loanStartDate : null);
        }
		
        return $schedule;
    }
		
    /** the share of each recognition month in its period's interest: the days of the month before it (its accrual month) */
    private function interestSharesOfThePeriod(array $recognitionMonths, int $loanStartIndex, ?string $loanStartDate): array
    {
        $count = count($recognitionMonths);
        if (is_null($loanStartDate) || $count === 0) {
            return array_fill(0, $count, $count ? 1 / $count : 0);
        }
        $loanMonth = Carbon::make($loanStartDate)->startOfMonth();
        $days = [];
        foreach ($recognitionMonths as $month) {
            $days[] = $loanMonth->copy()->addMonthsNoOverflow($month - 1 - $loanStartIndex)->daysInMonth;
        }
        $totalDays = array_sum($days);

        return array_map(fn ($monthDays) => $monthDays / $totalDays, $days);
    }

    /** the first row of the schedule at or after $index (null if the schedule ends before it) */
    private static function firstRowAtOrAfter(array $rows, int $index): ?int
    {
        $first = null;
        foreach (array_keys($rows) as $rowIndex) {
            if ($rowIndex >= $index && ($first === null || $rowIndex < $first)) {
                $first = (int) $rowIndex;
            }
        }
			
        return $first;
    }
	
    protected function getInstallmentDates($currentStartDateAsIndex, $startDate, $tenor, $installmentPaymentIntervalName, $gracePlusInstallmentIntervalValue, $currentDaysCount)
    {
        $datesAsIndexString=HDate::generateDatesBetweenStartDateAndDuration($currentStartDateAsIndex, $startDate, $tenor, $installmentPaymentIntervalName);
        $datesAsStringIndex = array_flip($datesAsIndexString);
        $installmentPaymentIntervalValue = $this->getInstallmentPaymentIntervalValue($installmentPaymentIntervalName);
        $datesIndexAndDaysCount =HDate::calculateDaysCountAtEnd($datesAsIndexString, $installmentPaymentIntervalValue, $currentDaysCount);
        $installmentStartDateAsIndex = $datesAsStringIndex[HDate::getDateAfterIndex($datesAsIndexString, $datesAsStringIndex, $startDate, $gracePlusInstallmentIntervalValue/$installmentPaymentIntervalValue)] ;
        return $installmentStartDateAsIndex;
                
                
    }
    
    
    /**
     * * $installmentsAlreadyPaid: لما القرض بيتعاد تسعيره بعد ما اتدفع منه أقساط ، عداد الـ step بيكمّل من عندها
     * * (مثال: step-up سنوي واتدفع 7 أقساط شهرية: الزيادة الجاية بعد 5 أقساط ، مش بعد 12).
     * * $interestByDays: قروض الـ non-banking بس — فايدة القرض الربع والنص سنوي بتتوزع على شهورها بعدد الأيام
     * * (extendPerMonth). حاسبة القروض والـ property management فاضلين بأجزاء متساوية زي ما كانوا.
     */
    public function __calculate($previousResult, int $indexOfLoop, string $loanType, string $startDate, float $loanAmount, $baseRate, float $marginRate, float $tenor, string $installmentPaymentIntervalName, float $stepUpRate = 0, ?string $stepUpIntervalName = null, float $stepDownRate = 0, ?string $stepDownIntervalName = null, float $gracePeriod  = 0, $currentStartDateAsIndex=0, ?int $currentDaysCount = null, ?array $pricingPerMonths = null, int $installmentsAlreadyPaid = 0, bool $interestByDays = false)
    {
		
        if ($loanAmount <= 0) {
            return [] ;
        }
        $loanFactors = [];
        $installmentFactors = [];
        $datesAsIndexString=HDate::generateDatesBetweenStartDateAndDuration($currentStartDateAsIndex, $startDate, $tenor, $installmentPaymentIntervalName);
        
        
        $datesAsStringIndex = array_flip($datesAsIndexString);
        $installmentPaymentIntervalValue = $this->getInstallmentPaymentIntervalValue($installmentPaymentIntervalName);
        $datesIndexAndDaysCount =HDate::calculateDaysCountAtEnd($datesAsIndexString, $installmentPaymentIntervalValue, $currentDaysCount);
        $isFixedDaysCount = !is_null($currentDaysCount); // the loop below reuses $currentDaysCount for each row's days
        $interestFactors=[];
        $currentPricing =  ($baseRate + $marginRate) /100  ;
        $stepRate = Loan::getStepRate($loanType, $stepUpRate, $stepDownRate);
        $stepRate = $stepRate / 100;
        $isWithCapitalization = Loan::isWithCapitalization($loanType);
        $appliedStepName = Loan::getAppliedStepIntervalName($loanType, $stepUpIntervalName, $stepDownIntervalName);
        $appliedStepValue = $this->getAppliedStepIntervalValue($appliedStepName);
        $gracePlusInstallmentIntervalValue = (int) ($gracePeriod+$installmentPaymentIntervalValue);
        $dateAfterIndex = HDate::getDateAfterIndex($datesAsIndexString, $datesAsStringIndex, $startDate, $gracePlusInstallmentIntervalValue/$installmentPaymentIntervalValue);
        $installmentStartDateAsIndex = $datesAsStringIndex[$dateAfterIndex] ;
        $endDateAsIndex = array_key_last($datesAsIndexString);
        $stepFactors = [];
        $currentStepFactorCounterValue = 0;
        $currentAppliedStepCounter = 0 ;
        $installmentsPerStep = $appliedStepValue / $installmentPaymentIntervalValue;
        if ($installmentsAlreadyPaid > 0 && $installmentsPerStep >= 1 && floor($installmentsPerStep) == $installmentsPerStep) {
            $currentStepFactorCounterValue = intdiv($installmentsAlreadyPaid, (int) $installmentsPerStep);
            $currentAppliedStepCounter = $installmentsAlreadyPaid % (int) $installmentsPerStep;
        }
        $currentLoanFactor = $loanAmount;
        $currentInstallmentFactor = 0 ;
        $previousPricing = 0 ;
        foreach ($datesIndexAndDaysCount as $currentDateAsIndex => $currentDaysCount) {

            $currentPricing = is_null($pricingPerMonths) ? $currentPricing : ($pricingPerMonths[$currentDateAsIndex]??$previousPricing);
            $previousPricing = $currentPricing ;
            /**
             * * calculate Interest Loan Factor
             */
                
                
            $interestFactors[$currentDateAsIndex]=($currentPricing / 360) * $currentDaysCount;
            $currentInterestFactor = $interestFactors[$currentDateAsIndex] ;
            /**
             * * Calculate Loan Factors
             */
            
            if (!$isWithCapitalization && $currentDateAsIndex < $installmentStartDateAsIndex) {
                $currentLoanFactor = $loanAmount;
            } else {
                $currentLoanFactor = $currentLoanFactor + ($currentLoanFactor * $currentInterestFactor);
            }
            
            $loanFactors[$currentDateAsIndex] = $currentLoanFactor;
                
            /**
             * * Calculate Step Factor
             */
            if ($currentDateAsIndex < $installmentStartDateAsIndex) {
                $stepFactors[$currentDateAsIndex]= 0 ;
            } else {
                $currentAppliedStepCounter++ ;
                $stepFactors[$currentDateAsIndex] = $currentStepFactorCounterValue;
                if ($currentAppliedStepCounter == $appliedStepValue/$installmentPaymentIntervalValue) {
                    $currentAppliedStepCounter = 0 ;
                    $currentStepFactorCounterValue++;
                }
            }
                  
            if ($currentDateAsIndex < $installmentStartDateAsIndex) {
                $currentInstallmentFactor =  0 ;
            } elseif ($currentDateAsIndex == $installmentStartDateAsIndex) {
                $currentInstallmentFactor = -1 ;
            } else {
                // steps counted from this schedule's first installment (that one is the installment solved below)
                $v = $stepFactors[$currentDateAsIndex] - $stepFactors[$installmentStartDateAsIndex] ;
                $currentInstallmentFactor = ($currentInstallmentFactor + ($currentInstallmentFactor * $currentInterestFactor) - (1 * pow((1 + $stepRate), ($v))));
            }
            $installmentFactors[$currentDateAsIndex] = $currentInstallmentFactor;
            /**
             * * Calculate Installment Factor
             */
        
        }
        
        $installmentAmounts = $this->calculateInstallmentAmount($installmentPaymentIntervalValue, $loanFactors, $installmentFactors, $stepRate, $installmentStartDateAsIndex, $endDateAsIndex, $tenor, $installmentPaymentIntervalValue, $appliedStepValue, $installmentsAlreadyPaid);

        $loanScheduleResult = $this->calculateLoanScheduleResult($installmentPaymentIntervalValue, $datesIndexAndDaysCount, $loanType, $loanAmount, $interestFactors, $installmentAmounts);
        $loanScheduleResult['accured_interest'] = [];
        // $loanScheduleResult = HArr::replacePreviousValues($loanScheduleResult);
        if ($indexOfLoop == -1) {
            
            if ($installmentPaymentIntervalName != 'monthly') {
                $loanScheduleResult = $this->extendPerMonth($loanScheduleResult, $installmentPaymentIntervalValue, ($interestByDays && !$isFixedDaysCount) ? $startDate : null);
            }
            return [
                'final_result'=>$loanScheduleResult,
            ];
        }
		
        $mergedResult = $indexOfLoop == 0 ? $loanScheduleResult :$previousResult ;
		
        $mergedResult = $loanScheduleResult;

        
    
        foreach ($previousResult as $key => $currentArr) {
                
            if ($key == 'totals') {
                continue;
            }

            $firstKey = array_key_first($loanScheduleResult[$key]);
		
            unset($loanScheduleResult[$key][$firstKey]);
            $mergedResult[$key]=HArr::mergeTwoAssocArr($previousResult[$key], $loanScheduleResult[$key]);
       
            
                
            
        }
	
		if(!count($mergedResult)){
			
		}
        return [
            'result'=>$loanScheduleResult ,
            'final_result'=>$mergedResult ,
        ];
    
    }

    /**
     * * القرض الربع والنص سنوي: فايدة الفترة بتتسجل في شهورها في قائمة الدخل.
     * * $startDate (تاريخ الصرف): كل شهر بياخد من الفايدة بعدد أيام الشهر اللي اتحسبت فيه ، زي ما الفايدة نفسها
     * * متحسبة بعدد الأيام ÷ 360. مثال: فترة يناير–مارس 2026 (31 + 28 + 31 = 90 يوم) فايدتها 45,000:
     * * فبراير 15,500 ، مارس 14,000 ، أبريل (شهر الدفع) 15,500. من غير تاريخ (عدد أيام ثابت): أجزاء متساوية.
     * * (كانت دايماً أجزاء متساوية ، 15,000 كل شهر.)
     */
    protected function extendPerMonth(array $loanScheduleResult, $installmentPaymentIntervalValue, ?string $startDate = null):array
    {
        $result = [];
        $startDateAsIndex = array_key_first($loanScheduleResult['endBalance']??[]);
        $endDateAsIndex = array_key_last($loanScheduleResult['endBalance']??[]);
        $previousBeginningBalance = Arr::first($loanScheduleResult['beginning']??[]) ;
        $result['beginning'] = [];
        $result['endBalance'] = [];
        // $currentInterestAmount = $loanScheduleResult['schedulePayment'][$currentDateAsIndex];
        for ($currentDateAsIndex  =$startDateAsIndex ; $currentDateAsIndex <=$endDateAsIndex ; $currentDateAsIndex++) {
            $currentInterestAmount = $loanScheduleResult['interestAmount'][$currentDateAsIndex]??0;
            if ($currentInterestAmount > 0) {
                $recognitionMonths = range($currentDateAsIndex - $installmentPaymentIntervalValue + 1, $currentDateAsIndex);
                $shares = $this->interestSharesOfThePeriod($recognitionMonths, (int) $startDateAsIndex, $startDate);
                $recognised = 0;
                foreach ($recognitionMonths as $position => $month) {
                    // the payment month takes what is left, so the months add up to the period's interest exactly
                    $result['interestAmount'][$month] = $month === $currentDateAsIndex ? $currentInterestAmount - $recognised : $currentInterestAmount * $shares[$position];
                    $recognised += $result['interestAmount'][$month];
                }
            }
            $previousEndBalanceValue = $result['endBalance'][$currentDateAsIndex-1]??0;
            // if (count($result['endBalance'])) {
                
            // }
            $currentEndBalance=$loanScheduleResult['endBalance'][$currentDateAsIndex]??null ;
            $result['schedulePayment'][$currentDateAsIndex] = $loanScheduleResult['schedulePayment'][$currentDateAsIndex]??0;
            $result['principleAmount'][$currentDateAsIndex] = $loanScheduleResult['principleAmount'][$currentDateAsIndex]??0;
            //        $result['no_securitization'][$currentDateAsIndex] = $loanScheduleResult['no_securitization'][$currentDateAsIndex]??0;
            $result['endBalance'][$currentDateAsIndex] = isset($loanScheduleResult['endBalance'][$currentDateAsIndex]) ? $currentEndBalance :$previousEndBalanceValue;
            $result['beginning'][$currentDateAsIndex] = $previousBeginningBalance ;
            $previousBeginningBalance = $result['endBalance'][$currentDateAsIndex];
        }
        $currentInterestAmountArr = $result['interestAmount']??[];
        ksort($currentInterestAmountArr);
        $dateAsIndexes = array_keys($result['beginning']);
        if (app()->bound('dateIndexWithDate')) {
            $result['accured_interest']=Loan::calculateSettlementStatement($dateAsIndexes, $loanScheduleResult['interestAmount'], $result['interestAmount']??[], 0, false, true);
        }
        $result['interestAmount'] = $currentInterestAmountArr ;
        return $result;
    }
    public function getInstallmentPaymentIntervalValue($installmentPayment):int
    {
        switch ($installmentPayment) {
            case 'monthly':
                return 1;
            case 'quarterly':
                return 3;
            case 'semi annually':
                return 6;
			default:
				return 1;
        }
    }

    protected function getAppliedStepIntervalValue($appliedStepIntervalName):int
    {
    
        switch ($appliedStepIntervalName) {
            case 'quarterly':
                return 3;
            case 'semi annually':
                return 6;
            case 'annually':
                return 12;
            default:
                return 12;
        }
    }


    protected function defaultDateFormat():string
    {
        return 'd-m-Y';
    }

    protected function addMonths(string $loanStartDateDay, string $date, int $duration):Carbon
    {
        return Carbon::make((new Date())->addMonths($loanStartDateDay, $date, $duration, 0, 1, 2));
    }

    protected function getDateFormatted(Carbon $date):string
    {
        return $date->format($this->defaultDateFormat());
    }
    

    protected function calculateInstallmentAmount(int $intervalValue, array $loanFactors, array $installmentFactory, float $stepRate, int $installmentStartDateAsIndex, int $endDateAsIndex, float $tenor, int $installmentPaymentIntervalValue, int $appliedStepValue, int $installmentsAlreadyPaid = 0)
    {
    
        $installmentsAmounts = [];
        
        
        $loanFactoryAtEndDate = $loanFactors[$endDateAsIndex];
        
        $installmentFactorAtEndDate = $installmentFactory[$endDateAsIndex];
        
        $installmentAmount = $loanFactoryAtEndDate / ($installmentFactorAtEndDate * -1);

        $installmentsAmounts[$installmentStartDateAsIndex] = $installmentAmount;

        
        for ($i=1 ; $i <= ($tenor / $installmentPaymentIntervalValue) ; $i++) {
            $loopDateAsIndex = $installmentStartDateAsIndex ;
            $stepVal = ($appliedStepValue / $installmentPaymentIntervalValue) ;
            // the step falls on the loan's installment number, also after a re-pricing
            // round 3: a step every installment (step interval = installment interval) is stepVal 1, and anything % 1 is
            // 0: the installment never stepped while the first one was solved for the steps, so the loan ended with a
            // balance (1,000,000 semi-annual over 12 months, step-up 10 % every 6 months: 56,272.19 left)
            if ($i != 1 && ($stepVal <= 1 || (($i + $installmentsAlreadyPaid) % $stepVal) == 1)) {
                $installmentAmount = $installmentAmount * ((pow((1 + $stepRate), 1)));
            } else {
                $installmentAmount = $installmentAmount;
            }
            $installmentsAmounts[$loopDateAsIndex]=$installmentAmount;
            $installmentStartDateAsIndex = $loopDateAsIndex+$intervalValue;
        }
        
        return $installmentsAmounts;
    }

    protected function calculateLoanScheduleResult(int $intervalValue, array $datesIndexAndDaysCount, string $loanType, float $loanAmount, array $interestFactor, array $installmentAmount)
    {
        $loanScheduleResult = [];
        $loanScheduleResult['totals']['totalSchedulePayment'] = 0;
        $loanScheduleResult['totals']['totalPrincipleAmount'] = 0;
        $loanScheduleResult['totals']['totalInterestAmount'] = 0;
        $isWithoutCapitalization =  Loan::isWithoutCapitalization($loanType);
        $firstLoop = true ;
        
        foreach ($datesIndexAndDaysCount as $dateAsIndex => $currentDaysCount) {
            $previousDate = $dateAsIndex-$intervalValue;
            $i = $dateAsIndex ;
            $loanScheduleResult['beginning'][$i] =  $firstLoop ? $loanAmount : $loanScheduleResult['endBalance'][$previousDate]??0;
            $loanScheduleResult['interestAmount'][$i] = $loanScheduleResult['beginning'][$i] *   $interestFactor[$i] ;
            $loanScheduleResult['totals']['totalInterestAmount'] += $loanScheduleResult['interestAmount'][$i];
            $installmentAmountAtIndex =$installmentAmount[$i] ?? 0;
            $loanScheduleResult['schedulePayment'][$i] = $isWithoutCapitalization && $installmentAmountAtIndex == 0 ? $loanScheduleResult['interestAmount'][$i] : $installmentAmountAtIndex;
            $loanScheduleResult['totals']['totalSchedulePayment'] = $loanScheduleResult['totals']['totalSchedulePayment'] + $loanScheduleResult['schedulePayment'][$i];
            $loanScheduleResult['principleAmount'][$i] = $loanScheduleResult['schedulePayment'][$i] - $loanScheduleResult['interestAmount'][$i];
            $loanScheduleResult['totals']['totalPrincipleAmount'] += $loanScheduleResult['principleAmount'][$i];
            $loanScheduleResult['endBalance'][$i] = $loanScheduleResult['beginning'][$i]  + $loanScheduleResult['interestAmount'][$i] -$loanScheduleResult['schedulePayment'][$i];
            $loanScheduleResult['endBalance'][$i] = $loanScheduleResult['endBalance'][$i] < 1 && $loanScheduleResult['endBalance'][$i] > -1 ? 0 : $loanScheduleResult['endBalance'][$i];
            $firstLoop = false ;
        }
        return $loanScheduleResult;

    }
    

    
    public function calculateExecutionAndPaymentAndLoan(string $fixedAssetType, int $currentDateIndex, float $totalFFECost, int $ffeStartDateAsIndex,  $ffe)
    {
       
        $fixedLoanAtEndService = new CalculateFixedLoanAtEndService();
        $ffeExecutionAndPaymentService  = new FfeExecutionAndPayment();
        $contractPaymentService  = new ContractPaymentService();
        $loanWithdrawalService = new CalculateLoanWithdrawal();
        $study = $ffe->study;
        $dateIndexWithDate = $study->getDateIndexWithDate();
        $dateWithDateIndex = $study->getDateWithDateIndex();
        $contractPayments = [];
       
        
        $duration = $ffe->getDuration();
        $ffeCollectionPolicyValue  = $ffe->getCollectionPolicyValue();
        $downPaymentOneAmount = 0 ;
        $executionAndPayment =$ffeExecutionAndPaymentService->__calculate($totalFFECost, $ffeStartDateAsIndex, $duration, $dateIndexWithDate);
        $ffePayment =$contractPaymentService->__calculate($totalFFECost, $executionAndPayment, $ffeStartDateAsIndex, $downPaymentOneAmount, $ffeCollectionPolicyValue, $dateIndexWithDate, $dateWithDateIndex);
        $contractPayments['FFE Payment'] = $ffePayment;
            
            
        
        $ffeLoan = $study->getLoanStructure($fixedAssetType);
        $equityFundingRate = $ffeLoan ? $ffeLoan->getEquityFundingRatesAtMonthIndex($currentDateIndex) : 100;
            
        $ffeEquityPayment['FFE Equity Injection'] = $ffeExecutionAndPaymentService->calculateFFEEquityPayment($contractPayments['FFE Payment'], $totalFFECost, $equityFundingRate);
        /**
         * * والباقي هاخد بيه قرض
         */
            
        $ffeLoanWithdrawal['FFE Loan Withdrawal'] = $ffeExecutionAndPaymentService->calculateFFELoanWithdrawal($contractPayments['FFE Payment'], $totalFFECost, $equityFundingRate);
        $ffeLoanWithdrawalInterestAmounts = [];
            
        if ($equityFundingRate < 100 &&  $ffeLoan->getTenorsAtMonthIndex($currentDateIndex) >0 ){
            $ffeLoanType = $ffeLoan->getLoanType();
            $ffeBaseRate = 0;
            $ffeMarginRate = $ffeLoan->getMarginRateAtMonthIndex($currentDateIndex);
            $ffeTenor = $ffeLoan->getTenorsAtMonthIndex($currentDateIndex);
            $ffeInstallmentIntervalName = $ffeLoan->getInstallmentIntervalAtMonthIndex($currentDateIndex);
            $ffeStepUpRate=0;
            $ffeStepUpIntervalName='annually';
            $ffeStepDownRate=0;
            $ffeStepDownIntervalName='annually';
            $ffeGracePeriod=$ffeLoan->getGracePeriodAtMonthIndex($currentDateIndex);
            $ffeLoanPricing = $ffeMarginRate + $ffeBaseRate;
            $ffeLoanWithdrawalInterest=$loanWithdrawalService->__calculate($study->replaceIndexWithItsStringDate($ffeLoanWithdrawal['FFE Loan Withdrawal'], $dateIndexWithDate), $ffeBaseRate, $ffeMarginRate, $dateWithDateIndex);
            $ffeLoanWithdrawalInterestAmounts =$ffeLoanWithdrawalInterest['withdrawal_interest_amounts']??[];
            $ffeLoanWithdrawalEndBalance = $ffeLoanWithdrawalInterest['withdrawalEndBalance']??[];
            $ffeLoanWithdrawalAmounts = $ffeLoanWithdrawalInterest['loanWithdrawal']??[];
                
            $ffeLoanStartDate =array_key_last($ffeLoanWithdrawalInterest);
            $ffeLoanAmount = $ffeLoanWithdrawalInterest[$ffeLoanStartDate];
            if ($ffeLoanStartDate) {
                $ffeLoanStartDateAsIndex=$study->convertDateStringToDateIndex($ffeLoanStartDate);
                $ffeLoanCalculations = $fixedLoanAtEndService->__calculate([], -1, $ffeLoanType, $ffeLoanStartDate, $ffeLoanAmount, $ffeBaseRate, $ffeMarginRate, $ffeTenor, $ffeInstallmentIntervalName, $ffeStepUpRate, $ffeStepUpIntervalName, $ffeStepDownRate, $ffeStepDownIntervalName, $ffeGracePeriod, $ffeLoanStartDateAsIndex, null, null, 0, $ffe instanceof FixedAsset);
                $ffeLoanCalculations = $ffeLoanCalculations['final_result']??[];
                $currentEndBalances = $ffeLoanCalculations['endBalance']??[] ;
                $ffeLoanCalculations['endBalance'] =HArr::fillMissedKeysFromPreviousKeys($currentEndBalances, $study->getCalculatedExtendedStudyDates());
                $ffeLoanCalculations['month_as_index'] = $ffeLoanStartDateAsIndex;
                $ffeLoanCalculations['interest_rate'] = $ffeMarginRate;
                $ffeLoanCalculations['loan_type'] = $ffeLoanType;
                $ffeLoanInterestAmounts = $ffeLoanCalculations['interestAmount'] ?? [];
                $ffeLoanEndBalanceAtStudyEndDate = $ffeLoanCalculations['endBalance'][$study->getStudyEndDateFormatted()] ?? 0;
                $ffeLoanEndBalance = $ffeLoanCalculations['endBalance'];
                $ffeLoanInstallment['FFE Loan Installment'] = $ffeLoanCalculations['schedulePayment']??[];
            }
        }
           
        return [
            'contractPayments'=>$contractPayments,
            'ffeEquityPayment'=>$ffeEquityPayment,
            'ffeLoanWithdrawal'=>$ffeLoanWithdrawal,
            'ffeLoanInstallment'=>$ffeLoanInstallment??[],
            'ffeLoanInterestAmounts'=>$ffeLoanInterestAmounts??[],
            'ffeExecutionAndPayment'=>$executionAndPayment,
            'ffeLoanWithdrawalInterest'=>$ffeLoanWithdrawalInterestAmounts,
            'ffeLoanStartDate'=>$ffeLoanStartDate??null,
            'ffeLoanAmount'=>$ffeLoanAmount??0,
            'ffeLoanEndBalanceAtStudyEndDate'=>$ffeLoanEndBalanceAtStudyEndDate??null,
            'ffeLoanPricing'=>$ffeLoanPricing??0 ,
            'ffeLoanEndBalance'=>$ffeLoanEndBalance??[],
            'ffeLoanWithdrawalEndBalance'=>$ffeLoanWithdrawalEndBalance??[],
            'ffeLoanWithdrawalAmounts'=>$ffeLoanWithdrawalAmounts??[] ,
            'ffeLoanCalculations'=>$ffeLoanCalculations??[],
            'ffePayment'=>$contractPayments['FFE Payment']??[]
        ];
        
            
            
    
    
    }
    

}
