<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * * نفس مشكلة expense_amount في التوريق: رقم فاضي بيوصل null ، والعمود NOT NULL ، فالـ INSERT بيضرب 1048.
 * * (الـ UPDATE مش بيضرب لأن الاتصال non-strict ، فالمشكلة في الصفوف الجديدة بس.)
 * * الرقم الفاضي = صفر ، والرقم المكتوب بيتحفظ زي ما هو.
 * *
 * * الدراسة: 140 ، شهور التشغيل من سبتمبر 2026 (8) لحد ديسمبر 2026 (11).
 */
class EmptyNumbersAreSavedAsZeroTest extends NonBankingTestCase
{
	private const DEPARTMENT_ID = 39;

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function admin(Study $study): User
	{
		return $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
	}

	/** A position that has no manpower row yet, so saving the page inserts one. */
	private function newPosition(Study $study): int
	{
		return (int) $this->nb()->table('positions')->insertGetId([
			'name' => 'Analyst', 'expense_type' => 'general-expense', 'department_id' => self::DEPARTMENT_ID,
			'company_id' => $study->company_id, 'created_at' => now(), 'updated_at' => now(),
		]);
	}

	private function saveManpower(Study $study, int $positionId, mixed $monthlyNetSalary): TestResponse
	{
		return $this->actAs($this->admin($study))
			->postJson(route('store.department.positions.for.non.banking', ['company' => $study->company_id, 'study' => $study->id]), [
				'manpowers' => [$positionId => [
					'hiring_counts' => [8 => 1, 9 => 0, 10 => 0, 11 => 0],
					'monthly_net_salary' => $monthlyNetSalary,
					'existing_count' => 0,
				]],
				'submit_button' => 'save',
			]);
	}

	private function storedSalary(Study $study, int $positionId): mixed
	{
		return $this->nb()->table('manpowers')->where('study_id', $study->id)->where('position_id', $positionId)->value('monthly_net_salary');
	}

	public function test_an_empty_monthly_net_salary_of_a_new_position_is_saved_as_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$positionId = $this->newPosition($study);

		$this->saveManpower($study, $positionId, null)->assertOk();

		$this->assertEquals(0, $this->storedSalary($study, $positionId));
	}

	public function test_a_monthly_net_salary_is_saved_as_entered(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$positionId = $this->newPosition($study);

		$this->saveManpower($study, $positionId, 15000)->assertOk();

		$this->assertEquals(15000, $this->storedSalary($study, $positionId));
	}

	/**
	 * * صفحة الفروع الجديدة: صف جديد في الـ repeater وعدد الفروع فاضي → الـ JS بيطلع NaN ، فـ total_branches المخفي بيفضل فاضي.
	 * * الصفحة بتحفظ الصفوف دي بـ storeRepeaterRelations (NewBranchesMicrofinanceController@store).
	 */
	private function saveNewBranchRows(Study $study, array $rows): void
	{
		$study->storeRepeaterRelations(new Request(['newBranchMicrofinanceOpeningProjections' => $rows]), ['newBranchMicrofinanceOpeningProjections'], $this->companyOf($study), []);
	}

	private function newBranchRow(array $overrides = []): array
	{
		return array_merge(['id' => 0, 'counts' => 2, 'start_date' => '2026-09', 'operation_date' => '2026-10', 'total_branches' => 2], $overrides);
	}

	private function storedTotalBranches(Study $study): array
	{
		return $this->nb()->table('new_branch_microfinance_opening_projections')->where('study_id', $study->id)->orderBy('id')->pluck('total_branches')->map(fn ($v) => (int) $v)->all();
	}

	public function test_an_empty_total_branches_of_a_new_branch_row_is_saved_as_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveNewBranchRows($study, [$this->newBranchRow(['total_branches' => null])]);

		$this->assertSame([0], $this->storedTotalBranches($study));
	}

	public function test_a_total_branches_is_saved_as_entered(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveNewBranchRows($study, [$this->newBranchRow(['total_branches' => 2])]);

		$this->assertSame([2], $this->storedTotalBranches($study));
	}

	/**
	 * * صفحة الأرصدة الافتتاحية: زرار "Add ..." بيضيف صف فاضي (الـ repeater مالوش default للـ amount). اليوزر كتب الاسم
	 * * وساب المبلغ → amount = null. الصف بيتجاهل بس لو الاسم والمبلغ الاتنين فاضيين.
	 * * الصفحة بتحفظهم بـ storeRepeaterRelations (OpeningBalancesController@store).
	 *
	 * * كل قسم بيبعت مع الصف جدول سداده (payload / installments / rent_payment) ، فاضي هنا.
	 *
	 * @return array<string, array{string, string, array<string, array<int, float>>}>
	 */
	public static function openingBalancesAddedWithAButton(): array
	{
		return [
			'other creditors' => ['otherCreditorsOpeningBalances', 'other_credits_opening_balances', ['payload' => []]],
			'other debtors' => ['otherDebtorsOpeningBalances', 'other_debtors_opening_balances', ['payload' => []]],
			'long term loans' => ['longTermLoanOpeningBalances', 'long_term_loan_opening_balances', ['installments' => []]],
			'other long term assets' => ['otherLongTermAssetsOpeningBalances', 'other_long_term_assets_opening_balances', ['payload' => []]],
			'other long term liabilities' => ['otherLongTermLiabilitiesOpeningBalances', 'other_long_term_liabilities_opening_balances', ['payload' => []]],
			'long term investments' => ['longTermInvestmentsOpeningBalances', 'long_term_investments_opening_balances', []],
			'right of use assets' => ['rightOfUseAssetOpeningBalances', 'right_of_use_asset_opening_balances', ['payload' => []]],
			'lease rent liabilities' => ['leaseRentLiabilityOpeningBalances', 'lease_rent_liability_opening_opening_balances', ['rent_payment' => [], 'rent_interest' => []]],
		];
	}

	private function saveOpeningBalanceRows(Study $study, string $relation, array $rows): void
	{
		$study->storeRepeaterRelations(new Request([$relation => $rows]), [$relation], $this->companyOf($study), ['study_id' => $study->id]);
	}

	private function storedAmounts(Study $study, string $table): array
	{
		return $this->nb()->table($table)->where('study_id', $study->id)->orderBy('id')->pluck('amount')->map(fn ($v) => (float) $v)->all();
	}

	#[DataProvider('openingBalancesAddedWithAButton')]
	public function test_an_opening_balance_row_with_a_name_and_an_empty_amount_is_saved_as_zero(string $relation, string $table, array $schedule): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveOpeningBalanceRows($study, $relation, [['id' => 0, 'name' => 'Added row', 'amount' => null] + $schedule]);

		$this->assertSame([0.0], $this->storedAmounts($study, $table));
	}

	#[DataProvider('openingBalancesAddedWithAButton')]
	public function test_an_opening_balance_amount_is_saved_as_entered(string $relation, string $table, array $schedule): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveOpeningBalanceRows($study, $relation, [['id' => 0, 'name' => 'Added row', 'amount' => 250000] + $schedule]);

		$this->assertSame([250000.0], $this->storedAmounts($study, $table));
	}

	/** Every section above except "other long term assets", whose model never skipped an empty row. */
	public static function openingBalancesThatSkipAnEmptyRow(): array
	{
		return array_diff_key(self::openingBalancesAddedWithAButton(), ['other long term assets' => true]);
	}

	#[DataProvider('openingBalancesThatSkipAnEmptyRow')]
	public function test_an_opening_balance_row_with_no_name_and_no_amount_is_still_skipped(string $relation, string $table, array $schedule): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveOpeningBalanceRows($study, $relation, [['id' => 0, 'name' => null, 'amount' => null] + $schedule]);

		$this->assertSame([], $this->storedAmounts($study, $table));
	}

	/**
	 * * "Other Long Term Assets" مالوش قاعدة تجاهل الصف الفاضي (زي باقي الأقسام). الصف الأول في الصفحة بيترسم بمبلغ 0 ،
	 * * فحفظ الصفحة من غير ما حد يلمسه أصلًا بيحفظ صف من غير اسم بصفر. الصف المضاف الفاضي دلوقتي زيه بالظبط (كان بيضرب 1048).
	 */
	public function test_an_empty_other_long_term_assets_row_is_saved_as_zero_like_the_untouched_first_row(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->saveOpeningBalanceRows($study, 'otherLongTermAssetsOpeningBalances', [
			['id' => 0, 'name' => null, 'amount' => 0, 'payload' => []],     // the first row, as the page renders it
			['id' => 0, 'name' => null, 'amount' => null, 'payload' => []],  // a row added with the button
		]);

		$this->assertSame([0.0, 0.0], $this->storedAmounts($study, 'other_long_term_assets_opening_balances'));
	}

	/**
	 * * صفحة الأصول الثابتة (Vue ، InputNumber): الخانة لما تتمسح بتبعت null. الصفحة بتحفظ الصفوف بـ storeRepeaterRelations
	 * * (FixedAssetsController@store). الفاضي بياخد الـ default بتاع العمود نفسه (replacement_interval = 1 ، والباقي 0).
	 */
	private const FIXED_ASSET_NUMBERS = ['ffe_item_cost', 'contingency_rate', 'cost_annual_increase_rate', 'depreciation_duration', 'replacement_cost_rate', 'replacement_interval', 'counts'];

	private function saveFixedAssetRow(Study $study, array $numbers): array
	{
		$study->storeRepeaterRelations(new Request(['fixedAssets' => [['id' => 0, 'type' => 'ffe', 'payment_terms' => 'cash'] + $numbers]]), ['fixedAssets'], $this->companyOf($study));
		$row = (array) $this->nb()->table('fixed_assets')->where('study_id', $study->id)->select(self::FIXED_ASSET_NUMBERS)->sole();

		return array_map(fn ($v) => (float) $v, $row);
	}

	public function test_empty_numbers_of_a_new_fixed_asset_take_the_column_defaults(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$stored = $this->saveFixedAssetRow($study, array_fill_keys(self::FIXED_ASSET_NUMBERS, null));

		$this->assertSame([
			'ffe_item_cost' => 0.0, 'contingency_rate' => 0.0, 'cost_annual_increase_rate' => 0.0, 'depreciation_duration' => 0.0,
			'replacement_cost_rate' => 0.0, 'replacement_interval' => 1.0, 'counts' => 0.0,
		], $stored);
	}

	public function test_fixed_asset_numbers_are_saved_as_entered_and_an_entered_zero_stays_zero(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$entered = [
			'ffe_item_cost' => 120000.0, 'contingency_rate' => 5.0, 'cost_annual_increase_rate' => 10.0, 'depreciation_duration' => 4.0,
			'replacement_cost_rate' => 20.0, 'replacement_interval' => 0.0, 'counts' => 3.0,
		];

		$this->assertSame($entered, $this->saveFixedAssetRow($study, $entered));
	}
}
