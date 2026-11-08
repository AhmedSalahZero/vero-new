<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\RevenueBusinessLine;
use App\Models\User;

/**
 * Unauthenticated users must never reach protected functionality.
 */
class GuestAccessTest extends SecurityTestCase
{
	private Company $company;

	protected function setUp(): void
	{
		parent::setUp();
		$this->company = $this->makeCompany('Guest Co');
	}

	public function test_artisan_debug_route_is_gone(): void
	{
		$this->get('/eee')->assertNotFound();
	}

	public function test_formerly_unauthenticated_routes_now_redirect_guests_to_login(): void
	{
		$id = $this->company->id;
		$requests = [
			['delete', "/deleteMultiRowsFromCaching/{$id}/SalesGathering"],
			['get', "/deleteAllRowsFromCaching/{$id}/SalesGathering"],
			['post', "/get-uploading-percentage/{$id}/SalesGathering"],
			['get', "/en/remove-company-image/{$id}"],
			['get', '/getStartDateAndEndDateOfIncomeStatementForCompany?income_statement_id=1'],
			['get', '/removeSessionForRedirect'],
		];
		foreach ($requests as [$method, $uri]) {
			$this->{$method}($uri)->assertRedirect(route('login'));
		}
	}

	public function test_protected_application_routes_reject_guests(): void
	{
		$line = RevenueBusinessLine::create(['company_id' => $this->company->id, 'name' => 'Guarded']);
		$this->post(route('admin.edit.table.cell', ['company' => $this->company->id]), [
			'modelName' => 'RevenueBusinessLine', 'modelId' => $line->id, 'columnName' => 'name', 'data' => 'pwned',
		])->assertRedirect(route('login'));
		$this->assertSame('Guarded', $line->fresh()->name);

		$this->get(route('dashboard', ['company' => $this->company->id]))->assertRedirect(route('login'));
		$this->postJson(route('get.net.sales.modal.for.type'), ['company_id' => $this->company->id])->assertUnauthorized();
		$this->get(route('user.index'))->assertRedirect(route('login'));
		$this->get(route('companySection.index'))->assertRedirect(route('login'));
	}

	public function test_non_super_admin_cannot_delete_company_logos(): void
	{
		$member = $this->makeUser(User::COMPANY_ADMIN, [$this->company]);
		$this->actAs($member)->get("/en/remove-company-image/{$this->company->id}")->assertForbidden();
	}
}
