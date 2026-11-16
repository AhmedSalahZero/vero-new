<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 3 (قرار اليوزر): نسب الـ ECL المتحفظة لشهور أقل من الدراسة. Study::recalculateMonthlyAndAccumulatedEcl كان بيلف على
 * * النسب المتحفظة بس ، فالشهور اللي بعدها مالهاش ECL في قائمة الدخل ، والمخصص المتراكم اللي الميزانية بتقراه مش موجود (صفر):
 * * المخصص بيختفي من الميزانية من غير ما يتسجل ، والميزانية بتختل بيه (دراسة 131 ، وسيناريو يوليو: 28,365,409).
 * * دلوقتي كل شهر لحد آخر الدراسة ، والشهر اللي مالوش نسبة بياخد آخر نسبة قبله (HArr::valueAtOrBefore).
 * * مثال: دراسة 140 (سنة 2026) ، محفظة leasing مليون كل شهر ، ECL ‏2% متحفظة ليناير–مارس بس: المخصص 20,000 كل شهر لحد ديسمبر
 * * (كان مالوش قيمة من أبريل) ، ومصروف الـ ECL 20,000 في يناير وصفر بعد كده.
 */
class EclRatesToTheStudyEndTest extends NonBankingTestCase
{
	public function test_a_month_with_no_ecl_rate_keeps_the_last_rate_and_the_reserve_stays(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$nb->table('ecl_and_new_portfolio_funding_rates')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)->delete();
		$nb->table('ecl_and_new_portfolio_funding_rates')->insert([
			'revenue_stream_type' => Study::LEASING, 'study_id' => $study->id, 'company_id' => $study->company_id,
			'admin_fees_rates' => json_encode([0 => 0]), 'ecl_rates' => json_encode([0 => 2, 1 => 2, 2 => 2]),
			'equity_funding_rates' => json_encode([0 => 100]), 'equity_funding_values' => json_encode([0 => 0]),
		]);

		Study::findOrFail($study->id)->recalculateMonthlyAndAccumulatedEcl(Study::LEASING, array_fill(0, 12, 1000000));

		$row = $nb->table('ecl_and_new_portfolio_funding_rates')->where('study_id', $study->id)->where('revenue_stream_type', Study::LEASING)->first();
		$accumulated = json_decode($row->accumulated_ecl_values, true);
		$monthly = json_decode($row->monthly_ecl_values, true);
		$this->assertEqualsWithDelta(20000, (float) $accumulated[11], 0.01, 'the reserve in December (it had none from April)');
		$this->assertEqualsWithDelta(20000, (float) $monthly[0], 0.01);
		$this->assertEqualsWithDelta(0, (float) $monthly[6], 0.01);
		$this->assertEqualsWithDelta((float) $accumulated[11], array_sum(array_map('floatval', $monthly)), 0.01, 'the expenses add up to the reserve');
	}
}
