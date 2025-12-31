<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorporateTaxesRateToFinancialStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_statements', function (Blueprint $table) {
            $table->decimal('corporate_taxes_rate',14,5)->default(0);
            $table->decimal('salary_taxes_rate',14,5)->default(0);
            $table->decimal('social_insurance_rate',14,5)->default(0);
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
