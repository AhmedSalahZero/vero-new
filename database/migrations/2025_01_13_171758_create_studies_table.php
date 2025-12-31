<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('studies', function (Blueprint $table) {
            $table->id();
			$table->string('study_name')->comment('اسم الدراسة');
			$table->string('company_nature')->comment('نوع الشركة ');
			$table->unsignedBigInteger('to_be_consolidated_from_study_id')->nullable()->comment('هيكون رقم الدراسة اللي هيختارها');
			$table->date('study_start_date');
			$table->integer('duration_in_years');
			$table->date('study_end_date');
			$table->float('operation_start_month');
			$table->date('operation_start_date');
			$table->string('financial_year_start_month');
			$table->decimal('corporate_taxes_rate',14,2)->default(0);
			$table->decimal('salary_taxes_rate',14,2)->default(0);
			$table->decimal('social_insurance_rate',14,2)->default(0);
			$table->decimal('investment_return_rate',14,2)->default(0);
			$table->decimal('perpetual_growth_rate',14,2)->default(0);
			$table->decimal('shareholder_equity_multiplier',14,2)->default(0);
			$table->json('operation_dates')->nullable();
			$table->json('study_dates')->nullable();
			$table->unsignedBigInteger('company_id');
	// ref	$table->foreign('company_id')->references('id')->on('veroanalysis_db.companies')->cascadeOnDelete();
            $table->timestamps();
        });
    }

}
