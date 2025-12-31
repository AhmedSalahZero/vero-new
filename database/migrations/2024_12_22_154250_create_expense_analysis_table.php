<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseAnalysisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
        Schema::create('expense_analysis', function (Blueprint $table) {
            $table->id();
			$table->date('date');
			$table->unsignedBigInteger('company_id');
			$table->string('category_name')->nullable();
			$table->string('sub_category_name')->nullable();
			$table->string('expense_name')->nullable();
			$table->string('quantity_measurement_unit')->nullable();
			$table->string('quantity')->nullable();
			$table->string('cost_per_unit')->nullable();
			$table->string('total_cost')->nullable();
			$table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
		foreach([
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'date',
				'view_name'=>__('Date'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'category_name',
				'view_name'=>__('Category Name'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'sub_category_name',
				'view_name'=>__('Sub Category Name'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'expense_name',
				'view_name'=>__('Expense Name'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'quantity_measurement_unit',
				'view_name'=>__('Quantity Measurement Unit'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'quantity',
				'view_name'=>__('Quantity'),
			],[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'cost_per_unit',
				'view_name'=>__('Cost Per Unit'),
			],
			[
				'model_name'=>'ExpenseAnalysis',
				'field_name'=>'total_cost',
				'view_name'=>__('Total Cost'),
			],
		] as $item){
			DB::table('tables_fields')
			->insert($item);
		}
		
		DB::table('sections')->insert([
			'id'=>354,
			'name'=>json_encode([
				'en'=>'Expense Analysis Report',
				'ar'=>'Expense Analysis Report'
			]),
			'sub_of'=>37,
			'icon'=>'fa fa-crosshairs',
			'route'=>'sales.expense.analysis',
			'order'=>70,
			'trash'=>0,
			'section_side'=>'client',
			'created_by'=>1
		]);
		
		Artisan::call('refresh:permissions');
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_analysis');
    }
}
