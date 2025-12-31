<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIjaraMortgageBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('ijara_mortgage_breakdowns', function (Blueprint $table) {
			$table->id();
			$table->string('installment_interval');
			$table->decimal('margin_rate',14,2)->default(0);
			$table->float('tenor')->default(0);
			$table->json('percentage_payload')->nullable();
			$table->json('amount_payload')->nullable();
			$table->unsignedBigInteger('study_id');
			$time = now()->toTimeString();
			$key = Str::uuid();
			$fullKey = $time.$key;
		// ref	$this->foreign('study_id','study_'.$fullKey)->references('id')->on('studies')->cascadeOnDelete();
		$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

}
