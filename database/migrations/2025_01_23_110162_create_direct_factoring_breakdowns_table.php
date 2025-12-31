<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectFactoringBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection('non_banking_service')->dropIfExists('direct_factoring_breakdowns');
        Schema::connection('non_banking_service')->create('direct_factoring_breakdowns', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('category');
			$table->decimal('margin_rate',14,2);
			$table->json('percentage_payload')->nullable();
			$table->json('amount_payload')->nullable();
			$table->json('beginning_balance')->nullable();
			$table->json('interest_revenue')->nullable();
			$table->json('unearned_interest')->nullable();
			$table->json('end_balance')->nullable();
			$table->json('net_funding_amounts')->nullable();
			
			$table->json('statement_beginning_balance')->nullable();
			$table->json('direct_factoring_amounts')->nullable();
			$table->json('direct_factoring_settlements')->nullable();
			$table->json('statement_end_balance')->nullable();
			
			
			
			foreach(['bank_beginning_balance','bank_loan_amounts','bank_loan_settlements','bank_interest_expense_payments','bank_total_dues','bank_interest_expense','bank_end_balance'] as $columnName){
				$table->json($columnName)->nullable();
			}
			
			
			
			$table->studyFields();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
