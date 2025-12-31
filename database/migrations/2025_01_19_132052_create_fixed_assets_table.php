<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('fixed_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
			$table->decimal('ffe_item_cost',14,0)->default(0);
			$table->decimal('vat_rate',14,2)->nullable();
			$table->decimal('withhold_tax_rate',14,2)->nullable();
			$table->decimal('cost_annual_increase_rate',14,2)->default(0);
            $table->string('payment_terms')->nullable();
			$table->integer('depreciation_duration')->default(0);
			$table->decimal('replacement_cost_rate',14,2)->default(0);
			$table->json('ffe_counts')->nullable();
			$table->unsignedBigInteger('company_id');
			$table->unsignedBigInteger('study_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}
