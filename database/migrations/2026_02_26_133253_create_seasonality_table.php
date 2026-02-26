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
        Schema::connection('non_banking_service')->create('seasonality', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id');
            $table->string('model_name');
            $table->enum('type', ['flat', 'quarterly', 'monthly'])->default('flat');
            $table->longText('percentages')->nullable()->comment('زي ما هي في الفورم بالظبط علشان لما نيجي نجيب الاولد داتا في الفيو');
            $table->longText('distributed_percentages')->nullable()->comment('بنفرد الكولوم السابق شهور يعني شهر واحد قيمته كذا وشهر اتنين قيمته كذا وهكذا لحد اخر شهر في السنه');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('seasonality');
    }
};
