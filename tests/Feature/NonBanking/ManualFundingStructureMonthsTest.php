<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use Illuminate\Support\Facades\View;

/**
 * * جدول "Manual Funding Structure" في صفحة الـ cash in/out flow لازم يغطي كل شهور الدراسة ، زي قائمة التدفقات اللي فوقه.
 *
 * * قبل الإصلاح: كان بياخد عدد الشهور من getMicrofinanceMonths() (11 أو 23 بس) ، فدراسة 3 سنين كانت بتظهر سنتين بس ،
 * * ومكانش ينفع تدخل ضخ رأس مال في السنة التالتة مع إن الحسبة بتقرا الضخ لكل شهر.
 */
class ManualFundingStructureMonthsTest extends NonBankingTestCase
{
	public function test_a_one_year_study_still_gets_its_twelve_months(): void
	{
		$study = $this->loadStudyFixture('study_140');   // one year: 2026-01 .. 2026-12

		$this->assertSame(range(0, 11), $this->manualFundingMonthIndexes($study));
	}

	public function test_a_three_year_study_gets_all_thirty_six_months(): void
	{
		$study = $this->extendToThreeYears($this->loadStudyFixture('study_140'));   // 2026-01 .. 2028-12

		$this->assertSame(35, $study->getViewStudyEndDateAsIndex());
		$this->assertSame(range(0, 35), $this->manualFundingMonthIndexes($study));
	}

	public function test_saved_injections_show_in_their_months_including_the_third_year(): void
	{
		$study = $this->extendToThreeYears($this->loadStudyFixture('study_140'));
		$study->cashflowStatementReport->update(['has_manual_equity_injection' => 1, 'manual_equity_injection' => [0 => 1000, 30 => 2500]]);

		$html = $this->cashInOutFlowPage($study);

		$this->assertSame('1000', $this->hiddenInputValue($html, 0));
		$this->assertSame('2500', $this->hiddenInputValue($html, 30));
		$this->assertSame('0', $this->hiddenInputValue($html, 35));
	}

	/** @return int[] the month indexes the Manual Funding Structure row has an input for */
	private function manualFundingMonthIndexes(Study $study): array
	{
		preg_match_all('/name="manual_equity_injection\[(\d+)\]"/', $this->cashInOutFlowPage($study), $matches);
		$indexes = array_map('intval', array_unique($matches[1]));
		sort($indexes);

		return $indexes;
	}

	private function cashInOutFlowPage(Study $study): string
	{
		$company = $this->companyOf($study);
		$admin = $this->makeUser(User::SUPER_ADMIN, [$company]);
		// in the app, AppServiceProvider shares the URL's company with every view at boot (before a test request exists)
		View::share('company', $company);

		return $this->actAs($admin)
			->get(route('non.banking.cash.in.out.flow.result', ['company' => $company->id, 'study' => $study->id]))
			->assertOk()
			->assertViewIs('non_banking_services.income-statement.cash-flow-with-odas')
			->getContent();
	}

	private function hiddenInputValue(string $html, int $monthIndex): string
	{
		$pattern = '/<input\b[^>]*\bname="manual_equity_injection\['.$monthIndex.'\]"[^>]*>/';
		$this->assertSame(1, preg_match($pattern, $html, $input), "no input for month {$monthIndex}");
		$this->assertSame(1, preg_match('/\bvalue="([^"]*)"/', $input[0], $value));

		return $value[1];
	}

	/** Edits the study the way the study page does: same start, now three years (to December 2028). */
	private function extendToThreeYears(Study $study): Study
	{
		$this->actAs($this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]))
			->putJson(route('non.banking.update.study', ['company' => $study->company_id, 'study' => $study->id]), [
				'name' => $study->name,
				'study_start_date' => '2026-01',
				'study_end_date' => '2028-12-01',
				'duration_in_years' => 3,
				'operation_start_date' => '2026-09',
				'has_micro_finance' => 1,
			])
			->assertOk();
		$study = $study->fresh();
		NonBankingServiceProvider::bindStudyDates($study);

		return $study;
	}
}
