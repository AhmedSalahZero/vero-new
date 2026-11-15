<?php

namespace Tests\Feature\NonBanking;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * * مسح الشركة بيمسح دراساتها من قاعدة كل module كمان (المرحلة 3 ، V9).
 *
 * * قبل الإصلاح: `delete:all` كان بيمسح من القاعدة الرئيسية بس ، فدراسات الشركة في قاعدة الـ non-banking كانت بتفضل
 * * من غير شركة (زي دراسات شركة 31 في الإنتاج).
 */
class DeleteCompanyDeletesItsStudiesTest extends NonBankingTestCase
{
	private const PM_TEST_DATABASE = 'vero_pm_test';

	/** the property-management connection rolls back too */
	protected $connectionsToTransact = ['mysql', NON_BANKING_SERVICE_CONNECTION_NAME, PROPERTY_MANAGEMENT_CONNECTION_NAME];

	protected function setUpTraits()
	{
		// the command looks at the property-management database too: a local test one (no studies table there)
		$mysql = config('database.connections.mysql');
		config(['database.connections.'.PROPERTY_MANAGEMENT_CONNECTION_NAME => array_merge(
			config('database.connections.'.PROPERTY_MANAGEMENT_CONNECTION_NAME),
			['host' => $mysql['host'], 'port' => $mysql['port'], 'username' => $mysql['username'], 'password' => $mysql['password'], 'database' => self::PM_TEST_DATABASE],
		)]);
		DB::purge(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		DB::connection('mysql')->statement('CREATE DATABASE IF NOT EXISTS `'.self::PM_TEST_DATABASE.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
		// a tiny property-management schema: its studies and one table of study rows (DDL commits, so before the transactions)
		$pm = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		$pm->statement('CREATE TABLE IF NOT EXISTS `studies` (`id` bigint unsigned NOT NULL PRIMARY KEY, `company_id` bigint unsigned NOT NULL)');
		$pm->statement('CREATE TABLE IF NOT EXISTS `pm_study_rows` (`id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, `study_id` bigint unsigned NOT NULL)');

		return parent::setUpTraits();
	}

	public function test_deleting_a_company_deletes_its_non_banking_studies_and_leaves_the_others(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$company = $this->companyOf($study);
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$otherCompanyId = $company->id + 1000;
		$nb->table('income_statement_reports')->insert(['study_id' => 987654, 'company_id' => $otherCompanyId]);
		$this->assertSame(1, $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count());

		$this->assertSame(0, Artisan::call('delete:all', ['company_id' => $company->id]));

		$this->assertFalse($nb->table('studies')->where('id', $study->id)->exists());
		$this->assertSame(0, $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->count());
		$this->assertSame(0, $nb->table('loan_schedule_payments')->where('study_id', $study->id)->count());
		$this->assertSame(0, $nb->table('income_statement_reports')->where('company_id', $company->id)->count());
		// another company's rows stay
		$this->assertSame(1, $nb->table('income_statement_reports')->where('company_id', $otherCompanyId)->count());
	}

	public function test_the_company_property_management_studies_go_too_and_another_company_keeps_its_own(): void
	{
		// a property-management study of the company with the same number as its non-banking study (140), and another
		// company's property-management study: only the first goes, with its rows
		$study = $this->loadStudyFixture('study_140');
		$company = $this->companyOf($study);
		$otherCompanyId = $company->id + 1000;
		$pm = DB::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME);
		$pm->table('studies')->insert([['id' => $study->id, 'company_id' => $company->id], ['id' => $study->id + 1, 'company_id' => $otherCompanyId]]);
		$pm->table('pm_study_rows')->insert([['study_id' => $study->id], ['study_id' => $study->id], ['study_id' => $study->id + 1]]);

		$this->assertSame(0, Artisan::call('delete:all', ['company_id' => $company->id]));

		$this->assertFalse($pm->table('studies')->where('id', $study->id)->exists());
		$this->assertSame(0, $pm->table('pm_study_rows')->where('study_id', $study->id)->count());
		$this->assertTrue($pm->table('studies')->where('id', $study->id + 1)->exists());
		$this->assertSame(1, $pm->table('pm_study_rows')->where('study_id', $study->id + 1)->count());
	}

	public function test_a_company_id_that_is_not_a_whole_number_is_refused_and_nothing_is_deleted(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$company = $this->companyOf($study);

		$this->assertSame(1, Artisan::call('delete:all', ['company_id' => $company->id.'abc']));

		$this->assertTrue(DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies')->where('id', $study->id)->exists());
	}
}
