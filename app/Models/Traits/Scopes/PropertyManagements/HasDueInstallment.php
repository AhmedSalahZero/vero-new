<?php
namespace App\Models\Traits\Scopes\PropertyManagements;

use Carbon\Carbon;


trait HasDueInstallment
{
	public function calculatePropertyDueInstallments():void
	{
		$allTotalInstallmentPayments = [];
		if($this->dueInstallment->isRegularInstallment()){
			$allDates = [];
		$contractSigningPaymentAmount = $this->dueInstallment->signing_payment;
		
		$contractSigningPaymentDate = $this->dueInstallment->signing_payment_date;
		$allDates[$contractSigningPaymentDate] = $contractSigningPaymentDate;
		$reservationPaymentAmount = $this->dueInstallment->reservation_payment;
		$reservationPaymentDate = $this->dueInstallment->reservation_payment_date;
		$allDates[$reservationPaymentDate] = $reservationPaymentDate;
		$installmentAmounts = $this->dueInstallment->regular_installments_amounts?:[];
		$intervals = [
			'monthly' => 1,
			'quarterly' => 3,
			'semi-annually' => 6,
			'annually' => 12,
		];
		$installmentAmountsFormatted  = [];
		foreach($installmentAmounts as $index=>$installmentAmount){
			$amount = $installmentAmount['amount'];
			if($amount > 0){
			
				$installmentCount = $installmentAmount['installment_count'];
			$startDate = $installmentAmount['start_date'];
			$installmentPaymentInterval = $installmentAmount['installment_payment_interval'];
			$intervalNumber = $intervals[$installmentPaymentInterval];
			for($i = 0; $i < $installmentCount; $i++){
		        $currentDateIndex =  $i * $intervalNumber;
				/** @phpstan-ignore-next-line */
				$dateAsString = is_numeric($startDate) ? $startDate+$currentDateIndex  : Carbon::make($startDate)->addMonths($currentDateIndex)->format('Y-m-d') ;
				$installmentAmountsFormatted[$dateAsString] = $amount;
				if(!isset($allDates[$dateAsString])){
					$allDates[$dateAsString] = $dateAsString;
				}
			}
			}
			
		}
		  $annualAmountsFormatted = [];
		    $amount = $this->dueInstallment->annual_amount?:0;
			$installmentCount = $this->dueInstallment->annual_count?:0;
			$startDate = $this->dueInstallment->annual_start_date?:0;
		
			if($amount > 0){
				//$installmentPaymentInterval = $installmentAmount['installment_payment_interval'];
				$intervalNumber = $intervals['annually'];
				for($i = 0; $i < $installmentCount; $i++){
					$currentDateIndex = $i * $intervalNumber;
					/** @phpstan-ignore-next-line */
					$dateAsString = is_numeric($startDate) ? $startDate+$currentDateIndex  : Carbon::make($startDate)->addMonths($currentDateIndex)->format('Y-m-d') ;
					$annualAmountsFormatted[$dateAsString] = $amount;
					if(!isset($allDates[$dateAsString])){
						$allDates[$dateAsString] = $dateAsString;
					}
					
				}
			}
			
			
			
			
			
			
			$deliveryPaymentsAmountsFormatted = [];
		    $amount = $this->dueInstallment->delivery_payments_amount?:0;
			$installmentCount = $this->dueInstallment->delivery_payments_count?:0;
			$startDate = $this->dueInstallment->delivery_payments_start_date?:0;
			if($amount > 0){
				$installmentPaymentInterval = $this->dueInstallment->delivery_payments_payment_interval?:'monthly';
				$intervalNumber = $intervals[$installmentPaymentInterval];
				for($i = 0; $i < $installmentCount; $i++){
					$currentDateIndex = $i * $intervalNumber;
					/** @phpstan-ignore-next-line */
					$dateAsString = is_numeric($startDate) ? $startDate+$currentDateIndex  : Carbon::make($startDate)->addMonths($currentDateIndex)->format('Y-m-d') ;
					$deliveryPaymentsAmountsFormatted[$dateAsString] = $amount;
					if(!isset($allDates[$dateAsString])){
						$allDates[$dateAsString] = $dateAsString;
					}
					
				}
			}
			
			$maintenancePaymentsAmountsFormatted = [];
		    $amount = $this->dueInstallment->maintenance_payments_amount?:0;
			$installmentCount = $this->dueInstallment->maintenance_payments_count?:0;
			$startDate = $this->dueInstallment->maintenance_payments_start_date?:0;
			if($amount > 0){
				$installmentPaymentInterval = $this->dueInstallment->maintenance_payments_payment_interval?:'monthly';
				$intervalNumber = $intervals[$installmentPaymentInterval];
				for($i = 0; $i < $installmentCount; $i++){
					$currentDateIndex = $i * $intervalNumber;
					/** @phpstan-ignore-next-line */
					$dateAsString = is_numeric($startDate) ? $startDate+$currentDateIndex  : Carbon::make($startDate)->addMonths($currentDateIndex)->format('Y-m-d') ;
					$maintenancePaymentsAmountsFormatted[$dateAsString] = $amount;
					if(!isset($allDates[$dateAsString])){
						$allDates[$dateAsString] = $dateAsString;
					}
				}
			}
			$allTotalInstallmentPayments = [];
			foreach( $allDates as $date){
				$allTotalInstallmentPayments[$date] = ($installmentAmountsFormatted[$date]??0) + ($annualAmountsFormatted[$date]??0) + ($deliveryPaymentsAmountsFormatted[$date]??0) + ($maintenancePaymentsAmountsFormatted[$date]??0);
			}
			$allTotalInstallmentPayments[$contractSigningPaymentDate] = isset($allTotalInstallmentPayments[$contractSigningPaymentDate]) ? $allTotalInstallmentPayments[$contractSigningPaymentDate] + $contractSigningPaymentAmount : $contractSigningPaymentAmount;
			$allTotalInstallmentPayments[$reservationPaymentDate] = isset($allTotalInstallmentPayments[$reservationPaymentDate]) ? $allTotalInstallmentPayments[$reservationPaymentDate] + $reservationPaymentAmount : $reservationPaymentAmount;
		}else{
			$variableInstallmentAmounts = $this->dueInstallment->variable_installment_amounts;
			foreach($variableInstallmentAmounts as $index=>$variableInstallmentAmount){
				$amount = $variableInstallmentAmount['amount'];
				if($amount > 0){
					$date = $variableInstallmentAmount['date'];
					$allTotalInstallmentPayments[$date] = isset($allTotalInstallmentPayments[$date]) ? $allTotalInstallmentPayments[$date] + $amount : $amount;
				}
			}
		}
	
	
			$this->dueInstallment->update([
				'total_due_installments' => $allTotalInstallmentPayments,
			]);
			
	
	}
        
} 
