<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Register excel amount fields in tables_fields (ported from cashvero):
 * - SupplierInvoice.excel_paid_amount → Previous Payments
 * - CustomerInvoice.excel_collected_amount → Previous Collection
 *
 * Columns already exist on the invoice tables. This only exposes them
 * in the export/upload field picker. Old header labels remain accepted
 * via getImportHeaderAliases() on the models.
 */
return new class extends Migration
{
	public function up(): void
	{
		$this->upsertField('SupplierInvoice', 'excel_paid_amount', 'Previous Payments', ['Excel Paid Amount']);
		$this->upsertField('CustomerInvoice', 'excel_collected_amount', 'Previous Collection', ['Excel Collected Amount']);
	}

	public function down(): void
	{
		DB::table('tables_fields')
			->where('model_name', 'SupplierInvoice')
			->where('field_name', 'excel_paid_amount')
			->delete();

		DB::table('tables_fields')
			->where('model_name', 'CustomerInvoice')
			->where('field_name', 'excel_collected_amount')
			->delete();
	}

	/**
	 * @param  list<string>  $legacyViewNames
	 */
	private function upsertField(string $modelName, string $fieldName, string $viewName, array $legacyViewNames): void
	{
		$existing = DB::table('tables_fields')
			->where('model_name', $modelName)
			->where('field_name', $fieldName)
			->first();

		if ($existing) {
			if (in_array($existing->view_name, $legacyViewNames, true) || $existing->view_name !== $viewName) {
				DB::table('tables_fields')
					->where('id', $existing->id)
					->update([
						'view_name' => $viewName,
						'updated_at' => now(),
					]);
			}

			return;
		}

		DB::table('tables_fields')->insert([
			'model_name' => $modelName,
			'field_name' => $fieldName,
			'view_name' => $viewName,
			'is_sales_trend' => 0,
			'company_id' => null,
			'created_at' => now(),
			'updated_at' => now(),
		]);
	}
};
