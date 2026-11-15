<?php

namespace Tests\Feature\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\Manpower;
use App\Models\NonBankingService\Study;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use Illuminate\Support\Facades\DB;

/**
 * * شكوى العميل (دراسة 154): الدراسة كانت سنة واتطوّلت لـ 3 سنين. مصروف في صفحة Expenses آخره 12/2027 ، بس قائمة الدخل
 * * (General Expense) فيها مبالغ في 2028.
 *
 * * السبب: دراسة السنة بتتخزن بـ 24 شهر (سنة زيادة جوه Vero) ، والمصروف اللي آخره آخر الدراسة كان بيتمد لآخر الـ 24 شهر
 * * (Study::recalculateExpenses). لما الدراسة اتطوّلت ، التواريخ اتبنت تاني بس المصروفات والمرتبات متحسبتش تاني ،
 * * فالمبالغ القديمة فضلت متخزنة وقائمة الدخل بتقراها زي ما هي.
 *
 * * الدراسة المستخدمة: Vero 140 "Maak Outlook" (يناير–ديسمبر 2026 ، 24 شهر متخزنين: 2026 و 2027).
 */
class StudyExtensionRecalculatesExpensesTest extends NonBankingTestCase
{
	private const EXPENSE_NAME = 'Study Extension Test Office Rent';

	private const MONTHLY_AMOUNT = 1000;

	private const MANPOWER_ID = 616;   // the study's only manpower row (HR Director, a general expense)

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	public function test_an_expense_that_ran_to_the_old_study_end_stops_there_after_the_study_is_extended(): void
	{
		// the customer's case one year earlier: the row ends at the 1-year study's end (12/2026 here, 12/2027 in study 154)
		$study = $this->loadStudyFixture('study_140');
		$expense = $this->generalExpenseEnding($study, 11);
		$this->assertSame(range(0, 23), $this->monthsWithAmounts($expense), 'a 1-year study stretches it into its extra year');

		$study = $this->extendToThreeYears($study);

		$this->assertSame(range(0, 11), $this->monthsWithAmounts($expense->fresh()));
		$line = $this->generalExpenseLine($study);
		// the income statement shows the operation months: September to December 2026 (8–11)
		$this->assertEqualsWithDelta(4 * self::MONTHLY_AMOUNT, $this->sumOfMonths($line, 8, 11), 0.01);
		$this->assertEqualsWithDelta(0, $this->sumOfMonths($line, 12, 35), 0.01, 'nothing after the expense end date');
	}

	public function test_an_expense_ending_inside_the_old_extra_year_stops_at_its_own_end_date_after_the_extension(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$expense = $this->generalExpenseEnding($study, 17);   // June 2027
		$this->assertSame(range(0, 23), $this->monthsWithAmounts($expense), 'a 1-year study stretches it to its last stored month');

		$this->extendToThreeYears($study);

		$this->assertSame(range(0, 17), $this->monthsWithAmounts($expense->fresh()));
	}

	public function test_the_salaries_cover_the_new_months_after_the_study_is_extended(): void
	{
		$study = $this->loadStudyFixture('study_140');
		Manpower::findOrFail(self::MANPOWER_ID)->update(['existing_count' => 2, 'monthly_net_salary' => 10000]);
		$study->recalculateManpower();
		$this->assertSame([], $this->salaryMonthsWithAmountsFrom(Manpower::findOrFail(self::MANPOWER_ID), 24));

		$this->extendToThreeYears($study);

		$this->assertSame(range(24, 35), $this->salaryMonthsWithAmountsFrom(Manpower::findOrFail(self::MANPOWER_ID), 24));
	}

	public function test_an_edit_that_keeps_the_study_dates_recalculates_nothing(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$expense = $this->generalExpenseEnding($study, 11);
		// a marker no recalculation would write: it stays only if nothing is recalculated
		$marker = json_encode([3 => 7]);
		$this->nb()->table('expenses')->where('id', $expense->id)->update(['monthly_repeating_amounts' => $marker]);
		$this->nb()->table('manpowers')->where('id', self::MANPOWER_ID)->update(['salary_expenses' => $marker]);

		$this->actAs($this->admin($study))
			->putJson(route('non.banking.update.study', ['company' => $study->company_id, 'study' => $study->id]), $this->studyFormData($study, [
				'name' => $study->name.' renamed',
				'study_end_date' => '2026-12-01',
				'duration_in_years' => 1,
			]))
			->assertOk();

		$this->assertSame($study->name.' renamed', $study->fresh()->name);
		$this->assertSame($marker, $this->nb()->table('expenses')->where('id', $expense->id)->value('monthly_repeating_amounts'));
		$this->assertSame($marker, $this->nb()->table('manpowers')->where('id', self::MANPOWER_ID)->value('salary_expenses'));
	}

	public function test_the_recalculate_command_brings_a_stale_expense_back_to_its_end_date(): void
	{
		// amounts saved further than the row's end date (as study 154's rows are after its extension)
		$study = $this->loadStudyFixture('study_140');
		$expense = $this->generalExpenseEnding($study, 11);
		$this->nb()->table('expenses')->where('id', $expense->id)->update(['end_date' => 5]);
		$this->assertSame(range(0, 23), $this->monthsWithAmounts($expense->fresh()));

		$this->artisan('non-banking:recalculate', ['--study' => [$study->id]])
			->expectsOutputToContain('0 failed')
			->assertSuccessful();

		$this->assertSame(range(0, 5), $this->monthsWithAmounts($expense->fresh()));
	}

	/** An Expenses-page row (fixed monthly amount, General Expense) from the study's first month, calculated as the page saves it. */
	private function generalExpenseEnding(Study $study, int $endDateAsIndex): Expense
	{
		$expenseNameId = $this->nb()->table('expense_names')->insertGetId([
			'company_id' => $study->company_id,
			'expense_type' => 'general-expense',
			'name' => self::EXPENSE_NAME,
			'is_employee_expense' => 0,
			'is_branch_expense' => 0,
			'created_at' => now(),
		]);
		$expense = Expense::create([
			'expense_name_id' => $expenseNameId,
			'expense_category' => 'general-expense',
			'start_date' => 0,
			'end_date' => $endDateAsIndex,
			'payment_terms' => 'cash',
			'vat_rate' => 0,
			'is_deductible' => 0,
			'withhold_tax_rate' => 0,
			'increase_interval' => 'annually',
			'increase_rates' => [0, 0, 0],
			'amount' => self::MONTHLY_AMOUNT,
			'amortization_months' => 12,
			'model_id' => $study->id,
			'model_name' => 'Study',
			'study_id' => $study->id,
			'expense_type' => 'Expense',
			'relation_name' => 'fixed_monthly_repeating_amount',
			'company_id' => $study->company_id,
		]);
		$study->recalculateExpenses('Study', $study->id, 'Expense', ['fixed_monthly_repeating_amount']);

		return $expense->fresh();
	}

	/** Edits the study the way the study page does: same start, now three years (to December 2028). */
	private function extendToThreeYears(Study $study): Study
	{
		$this->actAs($this->admin($study))
			->putJson(route('non.banking.update.study', ['company' => $study->company_id, 'study' => $study->id]), $this->studyFormData($study, [
				'study_end_date' => '2028-12-01',
				'duration_in_years' => 3,
			]))
			->assertOk();
		$study = $study->fresh();
		NonBankingServiceProvider::bindStudyDates($study);
		$this->assertSame(35, $study->getStudyEndDateAsIndex());

		return $study;
	}

	private function studyFormData(Study $study, array $changes): array
	{
		return array_merge([
			'name' => $study->name,
			'study_start_date' => '2026-01',
			'study_end_date' => '2026-12-01',
			'duration_in_years' => 1,
			'operation_start_date' => '2026-09',
			'has_micro_finance' => 1,
		], $changes);
	}

	private function admin(Study $study): User
	{
		return $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
	}

	/** @return int[] the month indexes the expense has an amount in */
	private function monthsWithAmounts(Expense $expense): array
	{
		return $this->monthsWithAmountsIn((array) $expense->monthly_repeating_amounts);
	}

	/** @return int[] the month indexes from $from on that the salary expenses have an amount in */
	private function salaryMonthsWithAmountsFrom(Manpower $manpower, int $from): array
	{
		$salaries = (array) json_decode((string) $this->nb()->table('manpowers')->where('id', $manpower->id)->value('salary_expenses'), true);

		return array_values(array_filter($this->monthsWithAmountsIn($salaries), fn (int $month) => $month >= $from));
	}

	/** @return int[] */
	private function monthsWithAmountsIn(array $series): array
	{
		$months = array_map('intval', array_keys(array_filter($series, fn ($value) => abs((float) $value) > 0.0001)));
		sort($months);

		return $months;
	}

	/** @return array<int, float> the expense's line under General Expense in the income statement */
	private function generalExpenseLine(Study $study): array
	{
		$table = (new IncomeStatementController())->index($this->companyOf($study), Study::findOrFail($study->id), true)['tableDataFormatted'];

		return (array) ($table[6]['sub_items'][self::EXPENSE_NAME]['data'] ?? []);
	}

	private function sumOfMonths(array $series, int $from, int $to): float
	{
		$total = 0.0;
		for ($month = $from; $month <= $to; $month++) {
			$total += (float) ($series[$month] ?? 0);
		}

		return $total;
	}
}
