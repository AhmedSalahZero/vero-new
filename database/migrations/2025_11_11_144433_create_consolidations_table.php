<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsolidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if(!Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasTable('consolidations')){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('consolidations', function (Blueprint $table) {
				$table->id();
				$table->string('name');
				$table->json('study_ids')->comment('studies that will be consolidated')->nullable();
				$table->string('study_type');
				$table->unsignedBigInteger('company_id');
				$table->timestamps();
			});
		}
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
