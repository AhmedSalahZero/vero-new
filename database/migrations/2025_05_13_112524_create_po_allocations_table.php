<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_allocations', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->unsignedBigInteger('invoice_id')->nullable();
            // $table->integer('money_payment_id')->nullable()->index('settlement_allocations_money_payment_id_foreign');
            // $table->unsignedBigInteger('letter_of_credit_issuance_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable()->index('allocations_contract_id_foreign');
            $table->unsignedBigInteger('purchase_order_id')->nullable()->index('po_allocations__id_foreign');
            $table->unsignedBigInteger('partner_id')->nullable()->index('po_allocations_partner_id_foreign');
            $table->decimal('allocation_percentage', 14)->default(0);
            $table->decimal('allocation_amount', 14)->default(0);
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
        Schema::dropIfExists('settlement_allocations');
    }
}
