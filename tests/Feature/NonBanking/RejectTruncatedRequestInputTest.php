<?php

namespace Tests\Feature\NonBanking;

use App\Http\Middleware\RejectTruncatedRequestInput;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * * شكوى العميل (دراسة 154): بعد تطويل الدراسة لـ 3 سنين ، حفظ صفحة Microfinance All Branches وقع بـ
 * * Undefined array key "flat_rates". الفورم عدّى حد max_input_vars (1000) ، وPHP رمى آخره من غير رسالة.
 * * دلوقتي الطلب اللي وصل للحد بيترفض برسالة واضحة (422) ، وماتحفظش أي حاجة.
 */
class RejectTruncatedRequestInputTest extends NonBankingTestCase
{
	private function handle(Request $request, int $limit): Response
	{
		return (new RejectTruncatedRequestInput($limit))->handle($request, fn () => new Response('passed'));
	}

	/** @return array<string, string> $count posted values, nested like the page's inputs */
	private function postedValues(int $count): array
	{
		$values = [];
		for ($i = 0; $i < $count; $i++) {
			$values['rows'][intdiv($i, 10)]['months'][$i % 10] = (string) $i;
		}

		return $values;
	}

	public function test_a_post_under_the_limit_passes(): void
	{
		$this->assertSame('passed', $this->handle(Request::create('/x', 'POST', $this->postedValues(4)), 5)->getContent());
	}

	public function test_a_post_that_reached_the_limit_is_rejected(): void
	{
		try {
			$this->handle(Request::create('/x', 'POST', $this->postedValues(5)), 5);
			$this->fail('A post with as many values as max_input_vars must be rejected.');
		} catch (HttpException $e) {
			$this->assertSame(422, $e->getStatusCode());
			$this->assertStringContainsString('max_input_vars = 5', $e->getMessage());
		}
	}

	public function test_a_post_spoofed_as_put_is_still_checked(): void
	{
		// PHP parses the body of the real POST: the _method field does not change what was cut
		$this->expectException(HttpException::class);
		$this->handle(Request::create('/x', 'POST', $this->postedValues(5) + ['_method' => 'PUT']), 5);
	}

	public function test_get_and_json_requests_are_not_checked(): void
	{
		$this->assertSame('passed', $this->handle(Request::create('/x', 'GET', $this->postedValues(20)), 5)->getContent());

		$json = Request::create('/x', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($this->postedValues(20)));
		$this->assertSame('passed', $this->handle($json, 5)->getContent());
	}

	public function test_no_limit_means_no_check(): void
	{
		$this->assertSame('passed', $this->handle(Request::create('/x', 'POST', $this->postedValues(20)), 0)->getContent());
	}

	public function test_the_microfinance_page_gets_a_clear_message_and_nothing_is_saved(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$admin = $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
		$flatRatesBefore = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)
			->table('microfinance_product_sales_projects')->where('id', 759)->value('flat_rates');
		// the web group resolves the middleware from the container: a small limit stands in for the server's 1000
		$this->app->bind(RejectTruncatedRequestInput::class, fn () => new RejectTruncatedRequestInput(50));

		$payload = ['microfinanceProductSalesProjects' => [17 => [
			'id' => 759, 'type' => 'all-branches', 'microfinance_product_id' => 17, 'avg_amount' => 1000,
			'flat_rates' => array_fill(8, 60, '30'),
		]]];
		$this->actAs($admin)
			->post(route('store.all-branches.microfinance', ['company' => $study->company_id, 'study' => $study->id]), $payload, [
				'X-Requested-With' => 'XMLHttpRequest',
				'Accept' => 'application/json',
			])
			->assertStatus(422)
			->assertJsonFragment(['message' => __('This form has more fields than the server accepts (max_input_vars = :limit), so nothing was saved. Please ask the system administrator to raise max_input_vars.', ['limit' => 50])]);

		$this->assertSame($flatRatesBefore, DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)
			->table('microfinance_product_sales_projects')->where('id', 759)->value('flat_rates'));
	}
}
