<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToOverdraftAgainstAssignmentOfContractBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('overdraft_against_assignment_of_contract_bank_statements', function (Blueprint $table) {
        //     $table->unsignedBigInteger('overdraft_against_assignment_of_contract_limit_id')->nullable()->after('company_id');
        // });
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
