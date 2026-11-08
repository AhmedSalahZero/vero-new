<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\RevenueBusinessLine;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The generic helper endpoints (edit-table-cell, delete-model, store-new-model,
 * updateBasedOnGlobalController, get-edit-form) used to accept any model class,
 * record id, column and method name from the request.
 */
class GenericModelHelpersTest extends SecurityTestCase
{
	private Company $companyA;
	private Company $companyB;
	private User $userA;
	private RevenueBusinessLine $lineA;
	private RevenueBusinessLine $lineB;

	protected function setUp(): void
	{
		parent::setUp();
		$this->companyA = $this->makeCompany('Helpers A');
		$this->companyB = $this->makeCompany('Helpers B');
		$this->userA = $this->makeUser(User::USER, [$this->companyA]);
		$this->lineA = RevenueBusinessLine::create(['company_id' => $this->companyA->id, 'name' => 'Line A']);
		$this->lineB = RevenueBusinessLine::create(['company_id' => $this->companyB->id, 'name' => 'Line B']);
	}

	private function editCell(array $payload)
	{
		return $this->actAs($this->userA)->postJson(
			route('admin.edit.table.cell', ['company' => $this->companyA->id]),
			array_merge(['modelName' => 'RevenueBusinessLine', 'modelId' => $this->lineA->id, 'columnName' => 'name', 'data' => 'Renamed '.uniqid()], $payload)
		);
	}

	public function test_edit_cell_still_works_for_own_record(): void
	{
		$this->editCell(['data' => 'My new name'])->assertOk();
		$this->assertSame('My new name', $this->lineA->fresh()->name);
	}

	public function test_edit_cell_rejects_other_company_record(): void
	{
		$this->editCell(['modelId' => $this->lineB->id, 'data' => 'pwned'])->assertNotFound();
		$this->assertSame('Line B', $this->lineB->fresh()->name);
	}

	public function test_edit_cell_rejects_sensitive_models(): void
	{
		$system = CompanySystem::where('company_id', $this->companyA->id)->first();
		$this->editCell(['modelName' => 'CompanySystem', 'modelId' => $system->id, 'columnName' => 'system_name', 'data' => NON_BANKING_SERVICE])->assertNotFound();
		$this->assertSame(CASH_VERO, $system->fresh()->system_name);

		$this->editCell(['modelName' => 'User', 'modelId' => $this->userA->id, 'columnName' => 'email', 'data' => 'x@evil.test'])->assertNotFound();
		$this->editCell(['modelName' => 'Company', 'modelId' => $this->companyA->id])->assertNotFound();
		$this->editCell(['modelName' => '..\\User', 'modelId' => $this->userA->id])->assertNotFound();
	}

	public function test_edit_cell_rejects_protected_and_unknown_columns(): void
	{
		foreach (['company_id', 'id', 'created_at', 'not_a_column', 'name;DROP TABLE users'] as $column) {
			$this->editCell(['columnName' => $column, 'data' => $this->companyB->id])->assertStatus(422);
		}
		$this->assertSame($this->companyA->id, (int) $this->lineA->fresh()->company_id);
	}

	public function test_edit_cell_cannot_invoke_destructive_methods_as_relations(): void
	{
		$before = RevenueBusinessLine::count();
		foreach (['delete', 'forceDelete', 'truncate', 'update', 'save'] as $method) {
			$this->editCell(['isRelation' => 1, 'relationName' => $method])->assertStatus(422);
		}
		$this->assertSame($before, RevenueBusinessLine::count());
		$this->assertNotNull($this->lineA->fresh());
	}

	public function test_edit_cell_relation_target_must_belong_to_company(): void
	{
		$categoryB = ServiceCategory::create(['company_id' => $this->companyB->id, 'revenue_business_line_id' => $this->lineB->id, 'name' => 'Cat B']);
		// A row of company A pointing (maliciously) at company B's category.
		$categoryA = ServiceCategory::create(['company_id' => $this->companyA->id, 'revenue_business_line_id' => $this->lineB->id, 'name' => 'Cat A']);
		$this->editCell(['modelName' => 'ServiceCategory', 'modelId' => $categoryA->id, 'isRelation' => 1, 'relationName' => 'revenueBusinessLine', 'data' => 'pwned'])->assertNotFound();
		$this->assertSame('Line B', $this->lineB->fresh()->name);
		$this->assertSame('Cat B', $categoryB->fresh()->name);
	}

	public function test_delete_model_only_deletes_own_records(): void
	{
		$url = route('delete.model', ['company' => $this->companyA->id]);

		$this->actAs($this->userA)->deleteJson($url, ['modelName' => 'RevenueBusinessLine', 'recordId' => $this->lineB->id])->assertNotFound();
		$this->assertNotNull($this->lineB->fresh());

		$this->actAs($this->userA)->deleteJson($url, ['modelName' => 'User', 'recordId' => $this->userA->id])->assertNotFound();
		$this->assertNotNull($this->userA->fresh());

		$this->actAs($this->userA)->deleteJson($url, ['modelName' => 'RevenueBusinessLine', 'recordId' => $this->lineA->id])->assertOk();
		$this->assertNull(RevenueBusinessLine::find($this->lineA->id));
	}

	public function test_store_new_model_forces_route_company_and_rejects_foreign_parents(): void
	{
		$url = route('admin.store.new.modal', ['company' => $this->companyA->id]);

		$this->actAs($this->userA)->postJson($url, [
			'modalName' => 'RevenueBusinessLine', 'value' => 'Injected', 'company_id' => $this->companyB->id,
		])->assertOk()->assertJson(['status' => true]);
		$created = RevenueBusinessLine::where('name', 'Injected')->firstOrFail();
		$this->assertSame($this->companyA->id, (int) $created->company_id);

		$this->actAs($this->userA)->postJson($url, [
			'modalName' => 'RevenueBusinessLine', 'value' => 'X1', 'additionalColumnName' => 'company_id', 'additionalColumnValue' => $this->companyB->id,
		])->assertStatus(422);

		$this->actAs($this->userA)->postJson($url, [
			'modalName' => 'ServiceCategory', 'value' => 'X2', 'previousSelectorNameInDb' => 'revenue_business_line_id', 'previousSelectorValue' => $this->lineB->id,
		])->assertStatus(422);

		$before = User::count();
		$this->actAs($this->userA)->postJson($url, ['modalName' => 'User', 'value' => 'evil'])->assertNotFound();
		$this->assertSame($before, User::count());
		$this->assertSame(0, ServiceCategory::where('company_id', $this->companyB->id)->where('name', 'X2')->count());
	}

	public function test_update_based_on_global_cannot_call_arbitrary_methods(): void
	{
		ServiceCategory::create(['company_id' => $this->companyA->id, 'revenue_business_line_id' => $this->lineA->id, 'name' => 'Own Cat']);
		$base = "/en/{$this->companyA->id}/helpers/updateBasedOnGlobalController";
		$before = RevenueBusinessLine::count();

		foreach (['delete', 'truncate', 'forceDelete'] as $method) {
			$this->actAs($this->userA)->getJson($base.'?'.http_build_query([
				'parentModelName' => 'RevenueBusinessLine', 'parentModelId' => $this->lineA->id, 'childRelationName' => $method,
			]))->assertStatus(422);
		}
		$this->assertSame($before, RevenueBusinessLine::count());
		$this->assertNotNull($this->lineA->fresh());

		$this->actAs($this->userA)->getJson($base.'?parentModelName=User&parentModelId='.$this->userA->id.'&childRelationName=roles')->assertNotFound();
	}

	public function test_update_based_on_global_returns_own_children_only(): void
	{
		ServiceCategory::create(['company_id' => $this->companyA->id, 'revenue_business_line_id' => $this->lineA->id, 'name' => 'Own Cat']);
		ServiceCategory::create(['company_id' => $this->companyB->id, 'revenue_business_line_id' => $this->lineB->id, 'name' => 'Secret Cat']);
		$base = "/en/{$this->companyA->id}/helpers/updateBasedOnGlobalController?";
		$query = ['parentModelName' => 'RevenueBusinessLine', 'childRelationName' => 'serviceCategories', 'model_id' => 'id', 'model_value' => 'name'];

		$own = $this->actAs($this->userA)->getJson($base.http_build_query($query + ['parentModelId' => $this->lineA->id]))->assertOk();
		$this->assertStringContainsString('Own Cat', $own->json('result'));

		$foreign = $this->actAs($this->userA)->getJson($base.http_build_query($query + ['parentModelId' => $this->lineB->id]))->assertOk();
		$this->assertStringNotContainsString('Secret Cat', (string) $foreign->json('result'));
	}

	public function test_get_edit_form_rejects_arbitrary_classes(): void
	{
		$this->actAs($this->userA)->get("/en/{$this->companyA->id}/get-edit-form?modelName=User")->assertNotFound();
	}

	public function test_bulk_truncate_rejects_non_upload_models(): void
	{
		$before = DB::table('company_systems')->count();
		$this->actAs($this->userA)->get(route('truncate', ['company' => $this->companyA->id, 'model' => 'CompanySystem']))->assertNotFound();
		$this->assertSame($before, DB::table('company_systems')->count());
	}
}
