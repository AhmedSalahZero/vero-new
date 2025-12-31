<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuritizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('securitizations', function (Blueprint $table) {
            $table->id();
			$table->string('revenue_stream_type');
			$table->unsignedInteger('disbursement_date');
			$table->unsignedInteger('securitization_date');
			$table->decimal('discount_rate',14,2)->default(0);
			$table->decimal('collection_revenue_rate',14,2)->default(0);
			$table->decimal('early_settlements_expense_rate',14,2)->default(0);
			$table->decimal('expense_amount',14,2)->default(0);
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
