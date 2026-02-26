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
        Schema::connection('property_management')->create('manpowers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('type')->default('general')->comment('general , branch , all-branches');
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedInteger('existing_count')->default(0);
            $table->decimal('monthly_net_salary', 14)->default(0);
            $table->longText('hiring_counts')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->longText('manpower_salaries')->nullable();
            $table->longText('accumulated_manpower_counts')->nullable();
            $table->longText('salary_expenses')->nullable();
            $table->longText('salary_payments')->nullable();
            $table->longText('tax_and_social_insurance_statement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('manpowers');
    }
};
