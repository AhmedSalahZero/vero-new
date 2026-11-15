<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\MicrofinanceProductSalesProject;
use App\Models\NonBankingService\Study;
use App\Models\User;
use App\Providers\NonBankingServiceProvider;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;

/**
 * * شكوى العميل (دراسة 154): الدراسة كانت سنة واتطوّلت لـ 3 سنين ، والشهور الجديدة في صفحة Microfinance ظهرت أصفار
 * * بدل ما تكرر آخر شهر (Dec): Product Mix و Flat Rates و Setup Fees و Loan Officers Cases وصفحة By-Branch Product Mix.
 * * في الـ Loan Officers كان أسوأ: الحفظ بيكرر آخر قيمة اتبعتت لآخر الدراسة ، فالصفر كان بيتحفظ لحد الآخر.
 *
 * * الدراسة المستخدمة: Vero 140 "Maak Outlook" (يناير–ديسمبر 2026 ، التشغيل من سبتمبر 2026 = الشهور 8–11) ، 9 منتجات.
 */
class MicrofinanceStudyExtensionTest extends NonBankingTestCase
{
	private const PRODUCT_ID = 17;

	private const PRODUCT_SALES_PROJECT_ID = 759;   // all-branches row of product 17

	private function admin(Study $study): User
	{
		return $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
	}

	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	/** different values every month, so a repeated December is told apart from a default or another month */
	private function saveOneYearMicrofinanceInputs(Study $study): void
	{
		$this->nb()->table('microfinance_product_sales_projects')->where('id', self::PRODUCT_SALES_PROJECT_ID)->update([
			'product_mixes' => json_encode([8 => '10.0', 9 => '20.0', 10 => '30.0', 11 => '40.0']),
			'flat_rates' => json_encode([8 => '21.0', 9 => '22.0', 10 => '23.0', 11 => '27.5']),
			'fees_rates' => json_encode([8 => '1.0', 9 => '1.5', 10 => '2.0', 11 => '3.0']),
			'setup_fees_durations' => json_encode([8 => '12.0', 9 => '11.0', 10 => '10.0', 11 => '9.0']),
		]);
		$firstOfficersRowId = $this->nb()->table('microfinance_loan_officers_cases_projects')
			->where('study_id', $study->id)->where('type', 'all-branches')->whereNull('branch_id')->orderBy('id')->value('id');
		$this->nb()->table('microfinance_loan_officers_cases_projects')->where('id', $firstOfficersRowId)->update([
			'existing_cases' => json_encode(array_merge(array_fill(0, 11, '5'), ['7'])),
			'new_cases' => json_encode(array_merge(array_fill(0, 11, '2'), ['4'])),
		]);
	}

	/** the by-branch product mix page's inputs: one row for every active product (the by-branch planning page reads them all) */
	private function saveOneYearByBranchProductMix(Study $study): void
	{
		foreach ($this->companyOf($study)->getActiveMicrofinanceProducts() as $product) {
			$this->nb()->table('microfinance_by_branch_product_mixes')->insert([
				'study_id' => $study->id,
				'company_id' => $study->company_id,
				'microfinance_product_id' => $product->id,
				'tenor' => 12,
				'avg_amount' => 1000,
				'funded_by' => 'by-mtls',
				'flat_rates' => json_encode([8 => '24.0', 9 => '24.0', 10 => '25.0', 11 => '26.0']),
				'decrease_rates' => json_encode([]),
				'increase_rates' => json_encode([]),
			]);
		}
		$this->nb()->table('studies')->where('id', $study->id)->update([
			'product_mix_senior_loan_officers' => json_encode(array_merge(array_fill(0, 11, '6'), ['8'])),
			'product_mix_loan_officers' => json_encode(array_merge(array_fill(0, 11, '3'), ['5'])),
		]);
	}

	/** Edits the study the way the study page does: same start, now three years (to December 2028). */
	private function extendToThreeYears(Study $study): Study
	{
		$this->actAs($this->admin($study))
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

	/** @return array<string, float> every named input of the page => its value */
	private function pageInputs(string $html): array
	{
		$dom = new DOMDocument();
		$previous = libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);
		$inputs = [];
		foreach ((new DOMXPath($dom))->query('//input[@name]') as $input) {
			$inputs[$input->getAttribute('name')] = (float) str_replace(',', '', $input->getAttribute('value'));
		}

		return $inputs;
	}

	private function allBranchesPageInputs(Study $study, ?int $branchId = null): array
	{
		$html = $this->actAs($this->admin($study))
			->get(route('create.all-branches.microfinance', ['company' => $study->company_id, 'study' => $study->id, 'branch_id' => $branchId]))
			->assertOk()
			->getContent();

		return $this->pageInputs($html);
	}

	public function test_the_new_months_repeat_december_in_the_pricing_and_product_mix_tables(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->saveOneYearMicrofinanceInputs($study);
		$study = $this->extendToThreeYears($study);

		$inputs = $this->allBranchesPageInputs($study);
		$name = fn (string $field, int $month) => 'microfinanceProductSalesProjects['.self::PRODUCT_ID."][{$field}][{$month}]";

		// the page now shows the operation months to December 2028 (months 8–35)
		$this->assertArrayHasKey($name('flat_rates', 35), $inputs);
		$this->assertArrayNotHasKey($name('flat_rates', 36), $inputs);

		// the saved months keep their own values
		$this->assertSame(21.0, $inputs[$name('flat_rates', 8)]);
		$this->assertSame(20.0, $inputs[$name('product_mixes', 9)]);
		$this->assertSame(2.0, $inputs[$name('fees_rates', 10)]);
		$this->assertSame(9.0, $inputs[$name('setup_fees_durations', 11)]);

		// every new month repeats December 2026
		foreach ([12, 20, 35] as $month) {
			$this->assertSame(40.0, $inputs[$name('product_mixes', $month)], "product mix, month {$month}");
			$this->assertSame(27.5, $inputs[$name('flat_rates', $month)], "flat rate, month {$month}");
			$this->assertSame(3.0, $inputs[$name('fees_rates', $month)], "setup fees rate, month {$month}");
			$this->assertSame(9.0, $inputs[$name('setup_fees_durations', $month)], "setup fees duration, month {$month}");
		}
	}

	public function test_the_new_months_repeat_december_in_the_loan_officers_cases_tables(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->saveOneYearMicrofinanceInputs($study);
		$study = $this->extendToThreeYears($study);

		$inputs = $this->allBranchesPageInputs($study);

		// a study longer than a year shows 24 months of cases (0–23)
		$this->assertArrayHasKey('microfinanceLoanOfficerCases[0][existing_cases][23]', $inputs);
		$this->assertSame(5.0, $inputs['microfinanceLoanOfficerCases[0][existing_cases][10]']);
		$this->assertSame(2.0, $inputs['microfinanceLoanOfficerCases[0][new_cases][10]']);
		foreach ([12, 23] as $month) {
			$this->assertSame(7.0, $inputs["microfinanceLoanOfficerCases[0][existing_cases][{$month}]"], "existing cases, month {$month}");
			$this->assertSame(4.0, $inputs["microfinanceLoanOfficerCases[0][new_cases][{$month}]"], "new cases, month {$month}");
		}
	}

	public function test_the_by_branch_product_mix_page_repeats_december(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->saveOneYearByBranchProductMix($study);
		$study = $this->extendToThreeYears($study);

		$html = $this->actAs($this->admin($study))
			->get(route('create.microfinance.product.mix', ['company' => $study->company_id, 'study' => $study->id]))
			->assertOk()
			->getContent();
		$inputs = $this->pageInputs($html);

		$this->assertSame(25.0, $inputs['microfinanceByBranchProductMixes['.self::PRODUCT_ID.'][flat_rates][10]']);
		$this->assertSame(26.0, $inputs['microfinanceByBranchProductMixes['.self::PRODUCT_ID.'][flat_rates][20]']);
		$this->assertSame(26.0, $inputs['microfinanceByBranchProductMixes['.self::PRODUCT_ID.'][flat_rates][35]']);
		$this->assertSame(6.0, $inputs['product_mix_senior_loan_officers[10]']);
		$this->assertSame(8.0, $inputs['product_mix_senior_loan_officers[23]']);
		$this->assertSame(3.0, $inputs['product_mix_loan_officers[10]']);
		$this->assertSame(5.0, $inputs['product_mix_loan_officers[23]']);
	}

	public function test_the_by_branch_planning_page_opens_after_the_study_is_made_longer(): void
	{
		// it read the by-branch cases with no default, so the first new month crashed the page (Undefined array key 12)
		$study = $this->loadStudyFixture('study_140');
		$this->saveOneYearByBranchProductMix($study);
		$study = $this->extendToThreeYears($study);
		$branchId = (int) $this->nb()->table('existing_branches')->where('company_id', $study->company_id)->value('id');

		$inputs = $this->allBranchesPageInputs($study, $branchId);

		$this->assertSame(26.0, $inputs['microfinanceProductSalesProjects['.self::PRODUCT_ID.'][flat_rates][20]']);
		$this->assertSame(8.0, $inputs['microfinanceLoanOfficerCases[0][new_cases][23]']);
		$this->assertSame(5.0, $inputs['microfinanceLoanOfficerCases[1][new_cases][23]']);
	}

	public function test_a_product_with_nothing_saved_keeps_the_old_defaults(): void
	{
		$project = new MicrofinanceProductSalesProject(['type' => 'all-branches']);

		$this->assertSame(0.0, $project->getProductMixAtYearOrMonthIndex(20));
		$this->assertSame(0.0, $project->getFlatRateAtYearOrMonthIndex(20));
		$this->assertSame(1.0, $project->getSetupFeesRateAtYearOrMonthIndex(20));
		$this->assertSame(12.0, $project->getSetupFeesDurationAtYearOrMonthIndex(20));
	}
}
