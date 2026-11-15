<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * * المرحلة 5: توزيع الأرباح بمعادلة FinVero (قرار اليوزر).
 * * Vero كان بياخد نسب الاحتياطي القانوني واحتياطي الهيئة وحصة العاملين والمجلس والتوزيعات في صفحة General Assumptions
 * * ومبيسجلش أي حاجة منهم. العمود الجديد بيشيل نتيجة كل سنة ، متحسبة مع التدفقات النقدية:
 * *   legal_reserve / fra_reserve في ديسمبر ، وبنود التوزيع في يناير اللي بعدها ، والدفع (payments) في أبريل.
 *
 * * إضافة بس (عمود nullable) ، فالدراسات الموجودة بتفتح زي ما هي لحد ما التدفقات تتحسب تاني.
 * *   php artisan migrate --database=non_banking_service --path=database/migrations/non_banking_service
 */
return new class extends Migration
{
	protected $connection = NON_BANKING_SERVICE_CONNECTION_NAME;

	private const TABLE = 'cashflow_statement_reports';
	private const COLUMN = 'profit_distributions';

	public function up(): void
	{
		$schema = Schema::connection($this->connection);
		if (! $schema->hasTable(self::TABLE) || $schema->hasColumn(self::TABLE, self::COLUMN)) {
			return;
		}
		$schema->table(self::TABLE, function (Blueprint $table) {
			$table->json(self::COLUMN)->nullable();
		});
	}

	public function down(): void
	{
		$schema = Schema::connection($this->connection);
		if ($schema->hasTable(self::TABLE) && $schema->hasColumn(self::TABLE, self::COLUMN)) {
			$schema->table(self::TABLE, function (Blueprint $table) {
				$table->dropColumn(self::COLUMN);
			});
		}
	}
};
