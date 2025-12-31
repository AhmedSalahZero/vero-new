<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeasingRevenueStreamBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('leasing_revenue_stream_breakdowns', function (Blueprint $table) {
            $table->id();
			// $table->string('type')->comment('by category or general');
			$table->unsignedBigInteger('category_id');
			$table->string('loan_nature');
			$table->string('loan_type');
			$table->integer('tenor');
			$table->integer('grace_period');
			$table->decimal('margin_rate',14,2);
			$table->decimal('sensitivity_1_margin_rate',14,2)->comment('هيتحكم فيه من الداش بورد')->default(0);
			$table->decimal('sensitivity_2_margin_rate',14,2)->comment('هيتحكم فيه من الداش بورد')->default(0);
			$table->string('installment_interval');
			$table->decimal('step_up',14,2)->default(0);
			$table->decimal('step_down',14,2)->default(0);
			$table->string('step_interval')->nullable();
			
			$table->json('loan_amounts')->nullable();
			// $table->json('growth_rate')->nullable();
			
			$table->unsignedBigInteger('study_id');
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
        Schema::dropIfExists('leasing_revenue_stream_breakdowns');
    }
}
