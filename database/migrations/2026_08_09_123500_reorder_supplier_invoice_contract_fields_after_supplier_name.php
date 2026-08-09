<?php

use App\Http\Controllers\ExportTable;
use App\Models\CustomizedFieldsExportation;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$exportTable = new ExportTable();
		$exportations = CustomizedFieldsExportation::where('model_name', 'SupplierInvoice')->get();

		foreach ($exportations as $exportation) {
			$fields = $exportation->fields ?? [];
			if (!is_array($fields) || $fields === []) {
				continue;
			}

			$reordered = $exportTable->reorderSupplierInvoiceContractFields($fields);
			if ($reordered === $fields) {
				continue;
			}

			$exportation->fields = $reordered;
			$exportation->save();
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Ordering is a presentation preference; no safe automatic reverse.
	}
};
