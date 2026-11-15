<?php

namespace Tests\Feature\NonBanking;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * * مسح دراسة بيمسح من قاعدة الـ module بتاعها بس.
 *
 * * قبل الإصلاح: مسح دراسة property management كان بينده `delete:study` اللي بيمسح من قاعدة الـ non-banking ،
 * * فمسح دراسة عقارات رقم 140 كان بيمسح داتا دراسة non-banking رقم 140 (عميل تاني) ، وداتا العقارات نفسها بتفضل.
 */
class DeleteStudyStaysInItsModuleTest extends NonBankingTestCase
{
	private const PM_TEST_DATABASE = 'vero_pm_test';

	/** the property-management connection rolls back too */
	protected $connectionsToTransact = ['mysql', NON_BANKING_SERVICE_CONNECTION_NAME, PROPERTY_MANAGEMENT_CONNECTION_NAME];

	protected function setUpTraits()
	{
		// a tiny local property-management database, prepared before the test transactions begin (DDL commits)
		$mysql = config('database.connections.mysql');
		config(['database.connections.'.PROPERTY_MANAGEMENT_CONNECTION_NAME => array_merge(
			config('database.connections.'.PROPERTY_MANAGEMENT_CONNECTION_NAME),
			['host' => $mysql['host'], 'port' => $mysql['port'], 'username' => $mysql['username'], 'password' => $mysql['password'], 'database' => self::PM_TEST_DATABASE],
		)]);
		DB::purge(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		DB::connection('mysql')->statement('CREATE DATABASE IF NOT EXISTS `'.self::PM_TEST_DATABASE.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->statement('CREATE TABLE IF NOT EXISTS `pm_study_rows` (`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, `study_id` bigint unsigned NOT NULL)');

		return parent::setUpTraits();
	}

	public function test_deleting_a_property_management_study_leaves_the_non_banking_study_with_the_same_number(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$pm = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		$pm->table('pm_study_rows')->insert([['study_id' => $study->id], ['study_id' => $study->id], ['study_id' => 999]]);
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$nbRows = $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count();
		$this->assertSame(1, $nbRows);

		$this->assertSame(0, Artisan::call('delete:study', ['study_id' => $study->id, '--connection' => PROPERTY_MANAGEMENT_CONNECTION_NAME]));

		$this->assertSame(0, $pm->table('pm_study_rows')->where('study_id', $study->id)->count());
		$this->assertSame(1, $pm->table('pm_study_rows')->where('study_id', 999)->count());
		$this->assertSame($nbRows, $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count());
		$this->assertTrue($nb->table('studies')->where('id', $study->id)->exists());
	}

	public function test_deleting_a_non_banking_study_still_deletes_its_rows_by_default(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$pm = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		$pm->table('pm_study_rows')->insert(['study_id' => $study->id]);

		$this->assertSame(0, Artisan::call('delete:study', ['study_id' => $study->id]));

		$this->assertSame(0, DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count());
		$this->assertSame(1, $pm->table('pm_study_rows')->where('study_id', $study->id)->count());
	}

	public function test_an_unknown_module_database_is_refused_and_nothing_is_deleted(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->assertSame(1, Artisan::call('delete:study', ['study_id' => $study->id, '--connection' => 'mysql']));

		$this->assertSame(1, DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count());
	}

	public function test_the_property_management_page_deletes_from_its_own_database(): void
	{
		$source = file_get_contents(app_path('Http/Controllers/PropertyManagements/StudyController.php'));

		$this->assertMatchesRegularExpression("/Artisan::call\\('delete:study',\\s*\\['study_id'=>\\\$study->id,\\s*'--connection'=>PROPERTY_MANAGEMENT_CONNECTION_NAME\\]\\)/", $source);
	}
}
