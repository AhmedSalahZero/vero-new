<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lc_against_td_or_cd_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable()->comment('CertificateOfDeposit , TimeOfDeposit');
            $table->string('currency');
            $table->string('lc_type');
            $table->integer('financial_institution_id')->index('td_cc_fname');
            $table->unsignedBigInteger('lc_opening_balance_id')->index('td_opname_c');
            $table->date('lc_end_date');
            $table->string('account_type')->comment('td or cd only');
            $table->string('account_number')->nullable()->comment('td or cd account number');
            $table->decimal('amount', 20, 5)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lc_against_td_or_cd_opening_balances');
    }
};
