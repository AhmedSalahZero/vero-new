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
        Schema::connection('non_banking_service')->create('microfinance_loan_officers_cases_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->comment('all-branches , new-branches,by-branch');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_senior')->default(false);
            $table->longText('existing_cases')->nullable();
            $table->longText('hiring')->nullable();
            $table->unsignedBigInteger('existing_count')->nullable();
            $table->longText('new_cases')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('total_existing_officers_cases_count')->nullable();
            $table->longText('total_new_officers_cases_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('microfinance_loan_officers_cases_projects');
    }
};
