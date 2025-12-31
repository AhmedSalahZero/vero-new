<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentEnColumnToLcOverdraftBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lc_overdraft_bank_statements', function (Blueprint $table) {
            $table->string('comment_en')->nullable();
            $table->string('comment_ar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lc_overdraft_bank_statements', function (Blueprint $table) {
            //
        });
    }
}
