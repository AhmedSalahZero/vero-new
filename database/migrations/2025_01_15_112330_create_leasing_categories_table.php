<?php

use App\Models\Company;
use App\Models\NonBankingService\LeasingCategory;
use App\Models\NonBankingService\LeasingRevenueStreamBreakdown;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeasingCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('leasing_categories', function (Blueprint $table) {
            $table->id();
			$table->string('title');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
		$companies = Company::all();
		foreach($companies as $company){
			LeasingCategory::createAllForCompany($company->id );
		}
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leasing_categories');
    }
}
