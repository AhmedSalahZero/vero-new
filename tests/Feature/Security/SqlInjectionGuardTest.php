<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * SQL injection in the analysis / dashboard endpoints that build raw SQL by
 * string concatenation (GuardAnalysisSqlInput + sqlValue() + bindings).
 */
class SqlInjectionGuardTest extends SecurityTestCase
{
	private Company $companyA;
	private Company $companyB;
	private User $userA;

	protected function setUp(): void
	{
		parent::setUp();
		$this->companyA = $this->makeCompany('SQL A');
		$this->companyB = $this->makeCompany('SQL B');
		$this->userA = $this->makeUser(User::COMPANY_ADMIN, [$this->companyA]);

		$rows = [
			[$this->companyA->id, "Men's Wear", 100],
			[$this->companyA->id, 'Cairo', 50],
			[$this->companyB->id, 'Cairo', 7777],
			[$this->companyB->id, 'Secret Zone', 9999],
		];
		foreach ($rows as [$companyId, $zone, $value]) {
			DB::table('sales_gathering')->insert([
				'company_id' => $companyId, 'zone' => $zone, 'net_sales_value' => $value,
				'date' => '2024-03-15', 'customer_name' => 'Customer '.$companyId, 'document_type' => 'INV',
			]);
		}
	}

	private function netSales(array $payload)
	{
		return $this->actAs($this->userA)->postJson(route('get.net.sales.modal.for.type'), array_merge([
			'company_id' => $this->companyA->id, 'type' => 'zone', 'selectedType' => 'Cairo',
			'start_date' => '2024-01-01', 'end_date' => '2024-12-31', 'modal_id' => 'modal1',
		], $payload));
	}

	public function test_body_company_id_must_be_a_company_of_the_user(): void
	{
		$this->netSales(['company_id' => $this->companyB->id])->assertForbidden();
	}

	public function test_identifier_and_date_injection_is_rejected(): void
	{
		$this->netSales(['type' => 'zone = zone OR 1=1 OR zone'])->assertStatus(422);
		$this->netSales(['type' => 'zone`--'])->assertStatus(422);
		$this->netSales(['start_date' => "2024-01-01\" OR \"1\"=\"1"])->assertStatus(422);
		$this->netSales(['company_id' => $this->companyA->id.' OR 1=1'])->assertStatus(422);
	}

	public function test_quoted_value_breakout_returns_only_own_company_data(): void
	{
		// Classic breakout of the old  type = "<value>"  concatenation. With bound
		// parameters the payload is just a value that matches nothing.
		// (getNetSalesValueSum shares the same binding but its follow-up report
		// crashes on empty data — a pre-existing functional bug — so the
		// breakout is asserted through topAndBottomsForDashboard.)
		$payload = '" OR company_id='.$this->companyB->id.' OR "a"="';
		$response = $this->actAs($this->userA)->postJson(route('getTopAndBottomsForDashboard'), [
			'company_id' => $this->companyA->id, 'type' => 'zone', 'selected_type' => $payload,
			'column' => 'zone', 'direction' => 'top', 'date_from' => '2024-01-01', 'date_to' => '2024-12-31',
		])->assertOk();
		$this->assertSame([], $response->json('data'));
		$this->assertStringNotContainsString('Secret Zone', $response->getContent());
		$this->assertStringNotContainsString('9999', $response->getContent());
	}

	public function test_legitimate_apostrophes_still_work(): void
	{
		$response = $this->netSales(['selectedType' => "Men's Wear"])->assertOk();
		$this->assertSame('100', (string) $response->json('data.0.total_sales_value'));

		$own = $this->netSales([])->assertOk();
		$this->assertSame('50', (string) $own->json('data.0.total_sales_value'), 'company B rows must never be included');
	}

	public function test_top_and_bottoms_rejects_subquery_columns(): void
	{
		$this->actAs($this->userA)->postJson(route('getTopAndBottomsForDashboard'), [
			'company_id' => $this->companyA->id, 'type' => 'zone', 'selected_type' => 'Cairo',
			'column' => '(select password from users limit 1)', 'direction' => 'top',
			'date_from' => '2024-01-01', 'date_to' => '2024-12-31',
		])->assertStatus(422);

		$ok = $this->actAs($this->userA)->postJson(route('getTopAndBottomsForDashboard'), [
			'company_id' => $this->companyA->id, 'type' => 'zone', 'selected_type' => 'Cairo',
			'column' => 'customer_name', 'direction' => 'top', 'date_from' => '2024-01-01', 'date_to' => '2024-12-31',
		])->assertOk();
		$this->assertSame('Customer '.$this->companyA->id, $ok->json('data.0.customer_name'));
	}

	public function test_filter_column_endpoint_rejects_arbitrary_tables_and_columns(): void
	{
		$url = route('filter.column.based.on.another.column', ['company' => $this->companyA->id]);
		$this->actAs($this->userA)->getJson($url.'?'.http_build_query([
			'filterTableName' => 'users', 'mainColumnName' => 'id', 'mainColumnValues' => [1], 'secondColumnName' => 'password',
		]))->assertStatus(422);
		$this->actAs($this->userA)->getJson($url.'?'.http_build_query([
			'filterTableName' => 'expense_analysis', 'mainColumnName' => 'id', 'mainColumnValues' => [1], 'secondColumnName' => 'id) as total, (select password from users limit 1',
		]))->assertStatus(422);
	}

	public function test_balance_details_route_params_are_validated(): void
	{
		$this->actAs($this->userA)->get(route('show.total.net.balance.in', [
			'company' => $this->companyA->id, 'currency' => 'EGP" OR "1"="1', 'modelType' => 'CustomerInvoice',
		]))->assertStatus(422);
		$this->actAs($this->userA)->get(route('show.total.net.balance.in', [
			'company' => $this->companyA->id, 'currency' => 'EGP', 'modelType' => 'User',
		]))->assertNotFound();
	}
}
