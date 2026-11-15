<?php

namespace Tests\Feature\NonBanking;

use App\Models\Company;
use App\Models\NonBankingService\Study;
use App\Providers\NonBankingServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\Feature\Security\SecurityTestCase;

/**
 * * أساس tests الـ non-banking.
 *
 * * - قاعدة الـ non-banking في الـ tests هي `vero_nb_test` على السيرفر المحلي بس (نفس user قاعدة الـ tests الرئيسية).
 * *   اسمها لازم يخلص بـ _test ، وإلا الـ test بيرفض يشتغل.
 * * - الـ schema بتتحمّل لوحدها من tests/Fixtures/NonBanking/schema.sql (هيكل قاعدة الإنتاج من غير داتا)
 * *   + migrations الـ non-banking ، وبتتعاد لو الملف أو الـ migrations اتغيروا.
 * * - كل test بيرجع في القاعدتين (transaction على الاتنين).
 * * - loadStudyFixture() بيحمّل دراسة حقيقية محفوظة (tests/Fixtures/NonBanking/study_*.json) تحت شركة جديدة،
 * *   ويربط تواريخها زي ما الصفحة بتعمل.
 */
abstract class NonBankingTestCase extends SecurityTestCase
{
	public const NON_BANKING_TEST_DATABASE = 'vero_nb_test';

	/** both databases roll back after every test */
	protected $connectionsToTransact = ['mysql', NON_BANKING_SERVICE_CONNECTION_NAME];

	private const SCHEMA_FILE = 'tests/Fixtures/NonBanking/schema.sql';
	private const MIGRATIONS_PATH = 'database/migrations/non_banking_service';
	private const FINGERPRINT_TABLE = 'test_schema_fingerprints';

	private static ?string $loadedFingerprint = null;

	protected function setUpTraits()
	{
		$this->useLocalNonBankingTestDatabase();

		return parent::setUpTraits();
	}

	private function useLocalNonBankingTestDatabase(): void
	{
		$database = (string) env('DB_NON_BANK_SERVICE_DATABASE', self::NON_BANKING_TEST_DATABASE);
		if (! preg_match('/^[A-Za-z0-9_]+_test$/', $database)) {
			throw new RuntimeException("Non-banking tests only run on a database whose name ends with _test ({$database} given).");
		}
		$mysql = config('database.connections.mysql');
		if (! in_array($mysql['host'], ['127.0.0.1', 'localhost'], true)) {
			throw new RuntimeException('Non-banking tests only run on a local MySQL server.');
		}
		config(['database.connections.'.NON_BANKING_SERVICE_CONNECTION_NAME => array_merge(
			config('database.connections.'.NON_BANKING_SERVICE_CONNECTION_NAME),
			['host' => $mysql['host'], 'port' => $mysql['port'], 'username' => $mysql['username'], 'password' => $mysql['password'], 'database' => $database],
		)]);
		DB::purge(NON_BANKING_SERVICE_CONNECTION_NAME);

		$this->ensureNonBankingSchema($database);
	}

	/** Loads the schema (and the non-banking migrations) once per run, and again whenever they change. */
	private function ensureNonBankingSchema(string $database): void
	{
		$migrations = glob(base_path(self::MIGRATIONS_PATH.'/*.php')) ?: [];
		$fingerprint = sha1(file_get_contents(base_path(self::SCHEMA_FILE)).'|'.implode('|', array_map(fn (string $f) => basename($f).':'.sha1_file($f), $migrations)));
		if (self::$loadedFingerprint === $fingerprint) {
			return;
		}

		// DDL commits implicitly in MySQL: this runs before the test transactions begin.
		$server = DB::connection('mysql');
		$server->statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);

		$stored = $nb->getSchemaBuilder()->hasTable(self::FINGERPRINT_TABLE)
			? $nb->table(self::FINGERPRINT_TABLE)->value('fingerprint')
			: null;
		if ($stored !== $fingerprint) {
			$nb->statement('SET FOREIGN_KEY_CHECKS = 0');
			foreach ($nb->select('SELECT table_name AS t FROM information_schema.tables WHERE table_schema = ?', [$database]) as $row) {
				$nb->statement("DROP TABLE IF EXISTS `{$row->t}`");
			}

			// tables come in name order, so a foreign key may point at a table that is created later
			foreach (explode("-- statement-break\n", (string) file_get_contents(base_path(self::SCHEMA_FILE))) as $chunk) {
				$sql = trim((string) preg_replace('/^--.*$/m', '', $chunk));
				if ($sql !== '') {
					$nb->unprepared($sql);
				}
			}
			$nb->statement('SET FOREIGN_KEY_CHECKS = 1');
			if ($migrations) {
				Artisan::call('migrate', ['--database' => NON_BANKING_SERVICE_CONNECTION_NAME, '--path' => self::MIGRATIONS_PATH, '--force' => true]);
			}
			$nb->statement('CREATE TABLE `'.self::FINGERPRINT_TABLE.'` (`fingerprint` char(40) NOT NULL)');
			$nb->table(self::FINGERPRINT_TABLE)->insert(['fingerprint' => $fingerprint]);
		}
		self::$loadedFingerprint = $fingerprint;
	}

	/**
	 * * بيحمّل دراسة محفوظة (كل صفوفها من الإنتاج) تحت شركة جديدة في قاعدة الـ tests ، ويرجّعها.
	 *
	 * @param  string  $name  fixture file name without .json, e.g. "study_140"
	 */
	protected function loadStudyFixture(string $name, array $systems = [NON_BANKING_SERVICE]): Study
	{
		$fixture = json_decode((string) file_get_contents(base_path("tests/Fixtures/NonBanking/{$name}.json")), true, 512, JSON_THROW_ON_ERROR);
		$company = $this->makeCompany('Non Banking '.$name, $systems);

		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		foreach ($fixture['tables'] as $table => $rows) {
			foreach (array_chunk($rows, 25) as $chunk) {
				$nb->table($table)->insert(array_map(
					fn (array $row) => array_key_exists('company_id', $row) ? ['company_id' => $company->id] + $row : $row,
					$chunk,
				));
			}
		}

		/** @var Study $study */
		$study = Study::findOrFail((int) $fixture['_meta']['study_id']);
		NonBankingServiceProvider::bindStudyDates($study);

		return $study;
	}

	/** The company a loaded fixture study belongs to (created by loadStudyFixture). */
	protected function companyOf(Study $study): Company
	{
		return Company::findOrFail($study->company_id);
	}
}
