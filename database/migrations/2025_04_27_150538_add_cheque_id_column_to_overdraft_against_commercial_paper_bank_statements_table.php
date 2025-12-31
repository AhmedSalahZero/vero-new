<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChequeIdColumnToOverdraftAgainstCommercialPaperBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('overdraft_against_commercial_paper_bank_statements', function (Blueprint $table) {
            $table->unsignedBigInteger('cheque_id')->nullable()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('overdraft_against_commercial_paper_bank_statements', function (Blueprint $table) {
            //
        });
    }
}
