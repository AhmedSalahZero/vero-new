<?php

namespace Tests\Feature\NonBanking;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

/**
 * * المرحلة 7: أمر non-banking:recalculate (كل دراسة بتتحسب تاني بالكود الحالي ، والتقرير قبل وبعد) وأمر
 * * non-banking:prune-deleted-companies (دراسات الشركات اللي اتمسحت) ، على دراسة 140 الحقيقية.
 * * دراسة 140 المحفوظة في الإنتاج متسجلة قبل إصلاح V6: رأس المال والاحتياطي بيبدأوا من التشغيل (سبتمبر) ، فميزانية
 * * الشهور اللي قبله مختلة بـ 230,031,996 لحد ما الدراسة تتحسب تاني.
 */
class RecalculateAndPruneCommandsTest extends NonBankingTestCase
{
	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	public function test_a_dry_run_reports_the_study_before_and_after_and_saves_nothing(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->savedStudy($study->id);

		$this->artisan('non-banking:recalculate', ['--study' => [$study->id], '--dry-run' => true])
			->expectsOutputToContain('| 230,031,996.00     | 0.00 ')   // the worst balance-sheet check before (V6) and after
			->expectsOutputToContain('Dry run: nothing was saved')
			->assertSuccessful();

		// every table the recalculation writes is as it was (the recalculation itself does change them: next test)
		$this->assertSame($before, $this->savedStudy($study->id));
	}

	/** what the recalculation writes for a study: its opening balances, reports, statements and loans */
	private function savedStudy(int $studyId): array
	{
		$rows = [];
		foreach (['equity_opening_balances', 'cash_and_bank_opening_balances', 'income_statement_reports', 'cashflow_statement_reports',
			'income_statements', 'loan_schedule_payments', 'fixed_assets', 'manpowers', 'expenses'] as $table) {
			$rows[$table] = $this->nb()->table($table)->where('study_id', $studyId)->orderBy('id')->get()
				->map(fn ($row) => array_diff_key((array) $row, ['updated_at' => 0]))->all();
		}

		return $rows;
	}

	public function test_a_recalculation_saves_the_study_and_its_balance_sheet_balances(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->savedStudy($study->id);

		$this->artisan('non-banking:recalculate', ['--study' => [$study->id]])
			->expectsOutputToContain('saved')
			->expectsOutputToContain('0 failed')
			->assertSuccessful();

		// the capital is there from the first month now (it started with the operation, in September)
		$capital = json_decode($this->nb()->table('equity_opening_balances')->where('study_id', $study->id)->value('paid_up_capital_extended'), true);
		$this->assertEqualsWithDelta(205000000, $capital[0] ?? 0, 0.01);
		$this->assertNotNull($this->nb()->table('cashflow_statement_reports')->where('study_id', $study->id)->value('profit_distributions'));
		$this->assertNotSame($before, $this->savedStudy($study->id), 'the dry run test above compares tables a real run changes');
	}

	public function test_old_opening_balance_rows_with_no_distributions_or_no_collection_schedule_are_saved_again(): void
	{
		// rows saved before those fields were on the page keep null (studies 99–103 in production): saving them again
		// failed, so these studies could not be recalculated
		$study = $this->loadStudyFixture('study_140');
		$this->nb()->table('equity_opening_balances')->where('study_id', $study->id)
			->update(['retained_earning_distribution_amounts' => null, 'retained_earning_distribution_payments' => null]);
		$this->nb()->table('cash_and_bank_opening_balances')->where('study_id', $study->id)->update(['non_performing_payload' => null]);

		$this->artisan('non-banking:recalculate', ['--study' => [$study->id]])
			->expectsOutputToContain('0 failed')
			->assertSuccessful();
	}

	public function test_a_study_whose_company_was_deleted_is_skipped_and_the_prune_command_deletes_it_only_with_force(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$company = $this->companyOf($study);
		DB::table('companies')->where('id', $company->id)->delete();
		$this->assertNull(Company::find($company->id));

		$this->artisan('non-banking:recalculate', ['--study' => [$study->id]])
			->expectsOutputToContain('its company was deleted')
			->assertSuccessful();

		$this->artisan('non-banking:prune-deleted-companies')
			->expectsOutputToContain((string) $study->id)
			->expectsOutputToContain('Nothing deleted')
			->assertSuccessful();
		$this->assertTrue($this->nb()->table('studies')->where('id', $study->id)->exists(), 'a listing deletes nothing');

		$this->artisan('non-banking:prune-deleted-companies', ['--force' => true])->assertSuccessful();

		$this->assertFalse($this->nb()->table('studies')->where('id', $study->id)->exists());
		$this->assertSame(0, $this->nb()->table('loan_schedule_payments')->where('study_id', $study->id)->count());
		$this->assertSame(0, $this->nb()->table('income_statement_reports')->where('study_id', $study->id)->count());
	}

	public function test_a_study_whose_rows_could_not_all_be_deleted_is_kept(): void
	{
		// delete:study gives up on a table without failing; the study row must stay, or the rows left could not be found
		// again. Here a delete:study that deletes nothing stands for it.
		$study = $this->loadStudyFixture('study_140');
		DB::table('companies')->where('id', $study->company_id)->delete();
		\Illuminate\Support\Facades\Artisan::command('delete:study {study_id} {--connection=}', fn () => 0);

		$this->artisan('non-banking:prune-deleted-companies', ['--force' => true])
			->expectsOutputToContain('rows are left in')
			->assertFailed();

		$this->assertTrue($this->nb()->table('studies')->where('id', $study->id)->exists());
		$this->assertGreaterThan(0, $this->nb()->table('loan_schedule_payments')->where('study_id', $study->id)->count());
	}
}
