<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * * الجولة 2 (قرار اليوزر): صفحة الأرصدة الافتتاحية بتتأكد إن الأصول = الالتزامات + حقوق الملكية قبل ما تحفظ أي حاجة.
 * * قبل كده كانت بتحفظ الأول وبعدين تطلّع الرسالة ، فميزانية افتتاحية مختلة كانت بتتحفظ (دراسات 99 و 102–106 و 137 ؛
 * * 104 مختلة 113.47 مليون: المبالغ متكتبة في خانة الاسم). مثال: كاش 1,000 ورأس مال 900 → الفرق −100: مفيش حاجة بتتحفظ ،
 * * والصفحة (بتحفظ بـ ajax) بتفضل بالأرقام اللي اليوزر كتبها وبتطلّع الرسالة.
 */
class OpeningBalancesCheckedBeforeSavingTest extends NonBankingTestCase
{
	private function nb()
	{
		return DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
	}

	private function admin(Study $study): User
	{
		return $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
	}

	private function url(Study $study): string
	{
		return route('store.opening.balances.for.non.banking', ['company' => $study->company_id, 'study' => $study->id]);
	}

	/** the page saves by jQuery ajax: a form body (FormData) with X-Requested-With */
	private const AJAX = ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'];

	private function body(float $cash, float $capital): array
	{
		return [
			// as the page sends a row (its interest lists, empty here)
			'cashAndBankOpeningBalances' => [['cash_and_bank_amount' => $cash, 'customer_receivable_amount' => 0, 'interests' => [], 'non_performing_interests' => []]],
			'equityOpeningBalances'      => [['paid_up_capital_amount' => $capital]],
		];
	}

	/** @return array<string, mixed> the opening balances as stored */
	private function stored(Study $study): array
	{
		return [
			'cash'   => $this->nb()->table('cash_and_bank_opening_balances')->where('study_id', $study->id)->orderBy('id')->pluck('cash_and_bank_amount')->all(),
			'equity' => $this->nb()->table('equity_opening_balances')->where('study_id', $study->id)->orderBy('id')->pluck('paid_up_capital_amount')->all(),
			'credit' => $this->nb()->table('other_credits_opening_balances')->where('study_id', $study->id)->count(),
		];
	}

	public function test_an_opening_balance_sheet_that_does_not_balance_is_not_saved(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->stored($study);

		$this->actAs($this->admin($study))->post($this->url($study), $this->body(1000, 900), self::AJAX)
			->assertStatus(422)
			->assertJsonPath('message', 'Total Assets Must Be Equal Total Liabilities And Owners Equity [ Difference = -100 ]');

		$this->assertSame($before, $this->stored($study), 'nothing is saved: the 13 tables are as they were');
	}

	public function test_a_form_post_is_refused_the_same_way(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->stored($study);

		$this->actAs($this->admin($study))->post($this->url($study), $this->body(1000, 900))
			->assertRedirect()->assertSessionHas('fail');

		$this->assertSame($before, $this->stored($study));
	}

	public function test_a_balanced_opening_balance_sheet_is_saved(): void
	{
		$study = $this->loadStudyFixture('study_140');

		$this->actAs($this->admin($study))->post($this->url($study), $this->body(1000, 1000), self::AJAX)
			->assertOk()
			->assertJsonPath('redirectTo', route('view.non.banking.forecast.income.statement', ['company' => $study->company_id, 'study' => $study->id]));

		$stored = $this->stored($study);
		$this->assertEqualsWithDelta(1000, (float) $stored['cash'][0], 0.01);
		$this->assertEqualsWithDelta(1000, (float) $stored['equity'][0], 0.01);
		$this->assertSame(0, $stored['credit'], 'the rows not sent are deleted, as before');
	}
}
