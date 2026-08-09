<?php

use App\Models\CustomizedFieldsExportation;
use App\Models\TablesField;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$exists = TablesField::where('model_name', 'SupplierInvoice')
			->where('field_name', 'contract_name')
			->exists();

		if (!$exists) {
			DB::table('tables_fields')->insert([
				'id' => 1790,
				'model_name' => 'SupplierInvoice',
				'field_name' => 'contract_name',
				'view_name' => 'Contract Name',
				'is_sales_trend' => 0,
				'company_id' => null,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}

		$exportations = CustomizedFieldsExportation::where('model_name', 'SupplierInvoice')->get();

		foreach ($exportations as $exportation) {
			$fields = $exportation->fields ?? [];
			if (!is_array($fields) || !in_array('contract_code', $fields, true)) {
				continue;
			}
			if (in_array('contract_name', $fields, true)) {
				continue;
			}

			$codeIndex = array_search('contract_code', $fields, true);
			array_splice($fields, $codeIndex, 0, ['contract_name']);
			$exportation->fields = array_values($fields);
			$exportation->save();
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		TablesField::where('model_name', 'SupplierInvoice')
			->where('field_name', 'contract_name')
			->delete();

		$exportations = CustomizedFieldsExportation::where('model_name', 'SupplierInvoice')->get();

		foreach ($exportations as $exportation) {
			$fields = $exportation->fields ?? [];
			if (!is_array($fields)) {
				continue;
			}
			$filtered = array_values(array_filter($fields, fn ($field) => $field !== 'contract_name'));
			if ($filtered === $fields) {
				continue;
			}
			$exportation->fields = $filtered;
			$exportation->save();
		}
	}
};
