<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * canViewCurrentCompany must enforce company membership from the matched
 * {company} route parameter — with AND without the locale prefix (the bypass:
 * POST /{company}/... used to skip the segment(2) check entirely).
 */
class TenantIsolationTest extends SecurityTestCase
{
	private Company $companyA;
	private Company $companyB;
	private User $userA;

	protected function setUp(): void
	{
		parent::setUp();

		Route::middleware(['web', 'auth'])->group(function () {
			Route::post('{company}/__security_probe', fn () => 'reached');
			Route::post('en/{company}/__security_probe_locale', fn (Company $company) => 'reached:'.$company->id);
			Route::post('__security_legacy/{companyId}/probe', fn () => 'reached');
		});

		$this->companyA = $this->makeCompany('Tenant A');
		$this->companyB = $this->makeCompany('Tenant B');
		$this->userA = $this->makeUser(User::USER, [$this->companyA]);
	}

	public function test_member_can_reach_own_company_routes(): void
	{
		$this->actAs($this->userA)->post("/{$this->companyA->id}/__security_probe")->assertOk()->assertSee('reached');
		$this->actAs($this->userA)->post("/en/{$this->companyA->id}/__security_probe_locale")->assertOk();
	}

	public function test_locale_less_url_cannot_bypass_tenant_check(): void
	{
		// Before the fix: segment(2) = "__security_probe" (non-numeric) → allowed.
		$this->actAs($this->userA)->post("/{$this->companyB->id}/__security_probe")->assertForbidden();
	}

	public function test_locale_prefixed_url_is_checked_for_bound_company_models(): void
	{
		$this->actAs($this->userA)->post("/en/{$this->companyB->id}/__security_probe_locale")->assertForbidden();
	}

	public function test_legacy_segment_two_company_routes_are_still_checked(): void
	{
		$this->actAs($this->userA)->post("/__security_legacy/{$this->companyB->id}/probe")->assertForbidden();
		$this->actAs($this->userA)->post("/__security_legacy/{$this->companyA->id}/probe")->assertOk();
	}

	public function test_guest_is_redirected_to_login_instead_of_crashing(): void
	{
		$this->post("/{$this->companyA->id}/__security_probe")->assertRedirect(route('login'));
	}

	public function test_real_company_route_rejects_non_members(): void
	{
		$this->actAs($this->userA)
			->delete(route('delete.model', ['company' => $this->companyB->id]), ['modelName' => 'RevenueBusinessLine', 'recordId' => 1])
			->assertForbidden();
	}
}
