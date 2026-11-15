<?php

use App\Models\NonBankingService\GeneralAndReserveAssumption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * * شكوى العميل: "الكوريدور مش بيحتفظ بالداتا القديمة".
 *
 * * 1) عمود cbe_corridor_changes_rates (صف "CBE Corridor Changes" في الصفحة) مش موجود في قاعدة الإنتاج ،
 * *    و Laravel بيشيل أي حقل مالوش عمود من الحفظ من غير error ($guarded). فالتغيير عمره ما اتحفظ:
 * *    الصفحة بتفتح بتغيير صفر ، وبتحسب الصافي = الأساس + 0 ، وأول حفظ بيمسح التغيير من الصافي كمان.
 * *    العمود بيتضاف ، والتغيير بيرجع للصفوف اللي الصافي فيها لسه شايل آخر تغيير: التغيير = الصافي − الأساس.
 * * 2) الأشكال القديمة: هامش البنك قايمة واحدة لكل التمويل ، والأساس فاضي مع إن الصافي متسجل.
 *
 * * القيمة القديمة بتتحفظ في general_and_reserve_assumptions_legacy_backups قبل أي تعديل ، و down() بيرجّعها
 * * (العمود الجديد بيفضل ، عشان مايضيعش اللي اتكتب فيه بعد كده).
 *
 * * التشغيل على قاعدة الـ non-banking بس:
 * *   php artisan migrate --database=non_banking_service --path=database/migrations/non_banking_service
 */
return new class extends Migration
{
	protected $connection = NON_BANKING_SERVICE_CONNECTION_NAME;

	private const TABLE = 'general_and_reserve_assumptions';
	private const BACKUP_TABLE = 'general_and_reserve_assumptions_legacy_backups';
	private const COLUMNS = ['bank_lending_margin_rates', 'cbe_base_lending_corridor_rates', 'cbe_lending_corridor_rates', 'cbe_corridor_changes_rates'];

	public function up(): void
	{
		$schema = Schema::connection($this->connection);
		if (! $schema->hasTable(self::TABLE)) {
			return;
		}
		if (! $schema->hasColumn(self::TABLE, 'cbe_corridor_changes_rates')) {
			$schema->table(self::TABLE, function (Blueprint $table) {
				$table->json('cbe_corridor_changes_rates')->nullable()->after('cbe_base_lending_corridor_rates');
			});
		}
		if (! $schema->hasTable(self::BACKUP_TABLE)) {
			$schema->create(self::BACKUP_TABLE, function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('general_and_reserve_assumption_id')->index('ga_legacy_backups_ga_id_index');
				$table->string('column_name');
				$table->longText('original_value')->nullable();
				$table->timestamp('migrated_at')->useCurrent();
			});
		}

		$db = DB::connection($this->connection);
		$db->transaction(function () use ($db) {
			$db->table(self::TABLE)->select(array_merge(['id'], self::COLUMNS))->orderBy('id')
				->chunkById(200, function ($rows) use ($db) {
					foreach ($rows as $row) {
						$this->upgradeRow($db, $row);
					}
				});
		});
	}

	private function upgradeRow($db, object $row): void
	{
		$decoded = [];
		foreach (self::COLUMNS as $column) {
			$decoded[$column] = is_string($row->{$column}) ? json_decode($row->{$column}, true) : null;
		}
		$changes = [];

		// the corridor change the user last saved is still inside the saved net corridor
		$base = is_array($decoded['cbe_base_lending_corridor_rates']) ? $decoded['cbe_base_lending_corridor_rates'] : [];
		$net = is_array($decoded['cbe_lending_corridor_rates']) ? $decoded['cbe_lending_corridor_rates'] : [];
		$saved = is_array($decoded['cbe_corridor_changes_rates']) ? $decoded['cbe_corridor_changes_rates'] : [];
		if ($saved === [] && $base !== [] && $net !== []) {
			$recovered = [];
			foreach ($net as $index => $rate) {
				if (array_key_exists($index, $base) && is_numeric($rate) && is_numeric($base[$index])) {
					$recovered[$index] = round((float) $rate - (float) $base[$index], 6);
				}
			}
			if (array_filter($recovered, fn (float $change) => abs($change) > 0.0000001) !== []) {
				$changes['cbe_corridor_changes_rates'] = $recovered;
				$decoded['cbe_corridor_changes_rates'] = $recovered;
			}
		}

		$changes += GeneralAndReserveAssumption::upgradeLegacyShapes($decoded);
		if ($changes === []) {
			return;
		}
		foreach (array_keys($changes) as $column) {
			$db->table(self::BACKUP_TABLE)->insert([
				'general_and_reserve_assumption_id' => $row->id,
				'column_name' => $column,
				'original_value' => $row->{$column},
			]);
		}
		$db->table(self::TABLE)->where('id', $row->id)->update(array_map(fn (array $value) => json_encode($value), $changes));
	}

	public function down(): void
	{
		$schema = Schema::connection($this->connection);
		if (! $schema->hasTable(self::BACKUP_TABLE)) {
			return;
		}
		$db = DB::connection($this->connection);
		$db->transaction(function () use ($db) {
			foreach ($db->table(self::BACKUP_TABLE)->orderByDesc('id')->get() as $backup) {
				$db->table(self::TABLE)->where('id', $backup->general_and_reserve_assumption_id)
					->update([$backup->column_name => $backup->original_value]);
			}
		});
		$schema->drop(self::BACKUP_TABLE);
	}
};
