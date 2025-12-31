<?php

use App\Models\CashExpense;
use Illuminate\Database\Migrations\Migration;

class RemoveCashPaymentWithNoCashExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
		foreach(['PayableCheque','CashPayment','OutgoingTransfer'] as $modelName){
			$fullModelName = 'App\Models\\' . $modelName;
			$models = $fullModelName::get();
			foreach($models as $payableCheque){
				if(is_null($payableCheque->cash_expense_id))
					continue ;
				$cashExpense = CashExpense::find($payableCheque->cash_expense_id);
				if(is_null($cashExpense)){
					$payableCheque->delete();
				}
				
			}
		}
	
		
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
