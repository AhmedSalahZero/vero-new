<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReverseFactoringAdministrationFeesRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('reverse_factoring_administration_fees_rates', function (Blueprint $table) {
			$table->id();
			$table->json('admin_fees_rates')->nullable();
			$table->json('ecl_rates')->nullable();
			$table->unsignedBigInteger('study_id');
			$time = now()->toTimeString();
			$key = Str::uuid();
			$fullKey = $time.$key;
		// ref	$this->foreign('study_id','study_'.$fullKey)->references('id')->on('studies')->cascadeOnDelete();
		$table->unsignedBigInteger('company_id');
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
