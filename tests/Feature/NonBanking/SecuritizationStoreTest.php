<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * * حفظ صفحة التوريق كان بيوقع بـ 500 في حالتين:
 * * (1) Securitization Expense فاضي (صف جديد في الـ repeater بيتمسح فيه كل حاجة) → expense_amount = null والعمود NOT NULL.
 * *     المصروف صفر عادي ، فالفاضي بيتحفظ صفر زي باقي النسب.
 * * (2) شهر برا تواريخ الدراسة (study_dates) → Undefined array key. دلوقتي بيرجع رسالة validation (422) ومفيش حاجة بتتحفظ.
 * *
 * * الدراسة: 140 ، study_dates من يناير 2026 لحد ديسمبر 2027 ، ومفيهاش توريق.
 */
class SecuritizationStoreTest extends NonBankingTestCase
{
	private const SEPTEMBER_2026 = 8;
	private const NOVEMBER_2026 = 10;

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function row(array $overrides = []): array
	{
		return array_merge([
			'id' => 0,
			'revenue_stream_type' => Study::LEASING,
			'disbursement_date' => '2026-09',
			'securitization_date' => '2026-11',
			'discount_rate' => 18,
			'collection_revenue_rate' => 0,
			'early_settlements_expense_rate' => 1,
			'expense_amount' => 5000,
		], $overrides);
	}

	private function save(Study $study, array $rows): TestResponse
	{
		return $this->actAs($this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]))
			->postJson(route('store.securitization', ['company' => $study->company_id, 'study' => $study->id]), [
				'securitizations' => $rows,
				'save' => 'save',
			]);
	}

	private function storedRows(Study $study): array
	{
		return $this->nb()->table('securitizations')->where('study_id', $study->id)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
	}

	public function test_an_empty_expense_amount_is_saved_as_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->save($study, [$this->row(['expense_amount' => ''])])->assertOk();

		$rows = $this->storedRows($study);
		$this->assertCount(1, $rows);
		$this->assertEquals(0, $rows[0]['expense_amount']);
		$this->assertSame(self::SEPTEMBER_2026, (int) $rows[0]['disbursement_date']);
		$this->assertSame(self::NOVEMBER_2026, (int) $rows[0]['securitization_date']);
	}

	public function test_a_zero_expense_amount_is_saved_as_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->save($study, [$this->row(['expense_amount' => 0])])->assertOk();

		$rows = $this->storedRows($study);
		$this->assertCount(1, $rows);
		$this->assertEquals(0, $rows[0]['expense_amount']);
	}

	public function test_an_expense_amount_is_saved_as_entered(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->save($study, [$this->row(['expense_amount' => 5000])])->assertOk();

		$this->assertEquals(5000, $this->storedRows($study)[0]['expense_amount']);
	}

	public function test_editing_a_row_and_emptying_its_expense_amount_saves_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->save($study, [$this->row(['expense_amount' => 5000])])->assertOk();
		$id = (int) $this->storedRows($study)[0]['id'];

		$this->save($study, [$this->row(['id' => $id, 'expense_amount' => ''])])->assertOk();

		$rows = $this->storedRows($study);
		$this->assertCount(1, $rows);
		$this->assertSame($id, (int) $rows[0]['id']);
		$this->assertEquals(0, $rows[0]['expense_amount']);
	}

	public function test_a_disbursement_month_before_the_study_dates_is_rejected_and_nothing_is_saved(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->save($study, [$this->row(['disbursement_date' => '2025-05'])])
			->assertStatus(422)
			->assertJsonValidationErrors(['securitizations.0.disbursement_date' => __('Disbursement Date Must Be Within The Study Dates')]);

		$this->assertSame([], $this->storedRows($study));
	}

	public function test_a_securitization_month_after_the_study_dates_is_rejected_and_nothing_is_saved(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->save($study, [$this->row(['securitization_date' => '2028-01'])])
			->assertStatus(422)
			->assertJsonValidationErrors(['securitizations.0.securitization_date' => __('Securitization Date Must Be Within The Study Dates')]);

		$this->assertSame([], $this->storedRows($study));
	}

	public function test_the_first_and_last_months_of_the_study_dates_are_accepted(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$studyDates = $study->getStudyDates();

		$this->save($study, [$this->row(['disbursement_date' => '2026-01', 'securitization_date' => '2027-12'])])->assertOk();

		$rows = $this->storedRows($study);
		$this->assertCount(1, $rows);
		$this->assertSame(array_search('2026-01-01', $studyDates, true), (int) $rows[0]['disbursement_date']);
		$this->assertSame(array_search('2027-12-01', $studyDates, true), (int) $rows[0]['securitization_date']);
	}
}
