<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * * بيئة tests الـ non-banking نفسها: القاعدة المحلية ، تحميل دراسة محفوظة ، والرجوع بعد كل test.
 */
class NonBankingTestDatabaseTest extends NonBankingTestCase
{
	public function test_the_non_banking_connection_is_the_local_test_database(): void
	{
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);

		$this->assertSame('vero_nb_test', $nb->getDatabaseName());
		$this->assertContains($nb->getConfig('host'), ['127.0.0.1', 'localhost']);
		$this->assertTrue($nb->getSchemaBuilder()->hasTable('general_and_reserve_assumptions'));
		$this->assertSame(2, (int) $nb->selectOne("SELECT COUNT(*) AS n FROM information_schema.triggers WHERE trigger_schema = 'vero_nb_test'")->n);
	}

	public function test_a_saved_study_loads_under_a_new_company_with_its_dates_bound(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->assertSame('Maak Outlook', $study->name);
		$this->assertSame($study->company_id, $this->companyOf($study)->id);
		$this->assertSame('2026-01-01', app('dateIndexWithDate')[0]);
		$this->assertCount(24, app('dateIndexWithDate'));   // one-year studies run on 24 months inside Vero
		$this->assertSame(0, app('dateWithDateIndex')['2026-01-01']);
		$this->assertNotNull($study->generalAndReserveAssumption);
	}

	public function test_nothing_from_an_earlier_test_is_left_behind(): void
	{
		$this->assertSame(0, Study::query()->count());
		$this->assertSame(0, DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->count());
	}

	public function test_the_balance_sheet_page_of_a_saved_study_renders(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$company = $this->companyOf($study);
		$admin = $this->makeUser(User::SUPER_ADMIN, [$company]);

		$this->actAs($admin)
			->get(route('non.banking.balance.sheet.result', ['company' => $company->id, 'study' => $study->id]))
			->assertOk()
			->assertViewIs('non_banking_services.income-statement.cash-flow');
	}
}
