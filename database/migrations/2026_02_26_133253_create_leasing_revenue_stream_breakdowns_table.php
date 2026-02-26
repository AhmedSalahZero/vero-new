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
        Schema::connection('non_banking_service')->create('leasing_revenue_stream_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id');
            $table->string('loan_nature');
            $table->string('loan_type');
            $table->integer('tenor');
            $table->integer('grace_period');
            $table->decimal('margin_rate', 14);
            $table->decimal('sensitivity_margin_rate', 8, 5)->default(0);
            $table->decimal('sensitivity_1_margin_rate', 14)->default(0)->comment('هيتحكم فيه من الداش بورد');
            $table->decimal('sensitivity_2_margin_rate', 14)->default(0)->comment('هيتحكم فيه من الداش بورد');
            $table->string('installment_interval');
            $table->decimal('step_up', 14)->default(0);
            $table->decimal('step_down', 14)->default(0);
            $table->string('step_interval')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('leasing_revenue_stream_breakdowns');
    }
};
