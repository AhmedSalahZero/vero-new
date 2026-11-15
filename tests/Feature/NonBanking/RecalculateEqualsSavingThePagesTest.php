<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2: non-banking:recalculate لازم يساوي إن اليوزر يحفظ كل صفحات الدراسة. قبل كده كان بيعمل جزء من اللي الصفحات
 * * بتعمله لما تتحفظ ، والفرق كان 4 حاجات (على دراسة 140):
 * *   1. الأرصدة الافتتاحية: كان بيعيد حفظ 4 جداول بس من 13. مثال: "Operating Lease" في الدائنين الآخرين 60,515,707 من غير
 * *      سداد ، فرصيده آخر كل شهر 60,515,707 ، وكان بيفضل زي ما اتحفظ آخر مرة مهما اتغير.
 * *   2. حالات التمويل متناهي الصغر وعقوده (صفحات الفروع): الحالات = حالات الـ loan officers × نسبة المنتج.
 * *   3. مصروفات صفحة Expenses كلها (StudyExtensionRecalculatesExpensesTest).
 * *   4. إيجار الفروع الجديدة وحق الانتفاع (صفحة New Branches).
 * * كل test بيبوّظ القيمة المحفوظة ، ويشغّل الأمر ، ويتأكد إنها رجعت للي الحفظ بيطلّعه.
 */
class RecalculateEqualsSavingThePagesTest extends NonBankingTestCase
{
	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function recalculate(int $studyId): void
	{
		$this->artisan('non-banking:recalculate', ['--study' => [$studyId]])->expectsOutputToContain('0 failed')->assertSuccessful();
	}

	public function test_every_opening_balance_table_is_saved_again(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$lease = $this->nb()->table('other_credits_opening_balances')->where('study_id', $study->id)->where('name', 'Operating Lease')->first();
		$this->nb()->table('other_credits_opening_balances')->where('id', $lease->id)->update(['statement' => json_encode(['monthly' => ['end_balance' => [0 => 1]]])]);

		$this->recalculate($study->id);

		// 60,515,707 with no settlement: the same balance at the end of every month
		$endBalance = json_decode($this->nb()->table('other_credits_opening_balances')->where('id', $lease->id)->value('statement'), true)['monthly']['end_balance'];
		$this->assertEqualsWithDelta(60515707, $endBalance[0], 0.01);
		$this->assertEqualsWithDelta(60515707, $endBalance[11], 0.01);
	}

	public function test_the_microfinance_cases_are_the_officers_cases_times_the_product_mix(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$product = $this->nb()->table('microfinance_product_sales_projects')->where('study_id', $study->id)->where('type', 'all-branches')
			->orderBy('id')->get()->first(fn ($row) => array_sum(array_map('floatval', (array) json_decode($row->monthly_product_mixes ?? '[]', true))) > 0);
		$this->nb()->table('microfinance_product_sales_projects')->where('id', $product->id)->update(['total_cases_counts' => json_encode([])]);

		$this->recalculate($study->id);

		// the existing branches' officers' cases of the month × the product's share of them
		$officerCases = [];
		foreach ($this->nb()->table('microfinance_loan_officers_cases_projects')->where('study_id', $study->id)->where('type', 'all-branches')->get() as $officers) {
			foreach (['total_existing_officers_cases_count', 'total_new_officers_cases_count'] as $column) {
				foreach ((array) json_decode($officers->{$column} ?? '[]', true) as $month => $cases) {
					$officerCases[$month] = ($officerCases[$month] ?? 0) + (float) $cases;
				}
			}
		}
		$mix = (array) json_decode($product->monthly_product_mixes, true);
		$cases = (array) json_decode($this->nb()->table('microfinance_product_sales_projects')->where('id', $product->id)->value('total_cases_counts'), true);
		$checked = 0;
		foreach ($mix as $month => $share) {
			if ((float) $share > 0 && ($officerCases[$month] ?? 0) > 0) {
				$this->assertEqualsWithDelta(($officerCases[$month] ?? 0) * $share / 100, (float) $cases[$month], 0.01, "month {$month}");
				$checked++;
			}
		}
		$this->assertGreaterThan(0, $checked, 'the fixture lends in some month');
	}

	public function test_the_new_branches_rent_is_worked_out_again(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$this->recalculate($study->id);
		$saved = $this->nb()->table('income_statement_reports')->where('study_id', $study->id)->value('new_branches_rent_amortization');
		$this->nb()->table('income_statement_reports')->where('study_id', $study->id)->update(['new_branches_rent_amortization' => json_encode([])]);

		$this->recalculate($study->id);

		// what the New Branches page's Save writes (Study::calculateRightOfUseRentAndStatement)
		$this->assertSame(json_decode($saved, true), json_decode($this->nb()->table('income_statement_reports')->where('study_id', $study->id)->value('new_branches_rent_amortization'), true));
		$this->assertNotEmpty(json_decode($saved, true));
	}
}
