<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\SalesGathering;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * IDOR on ids taken from the request / unbound route parameters, and mass
 * assignment of ownership columns.
 */
class MassAssignmentAndIdorTest extends SecurityTestCase
{
	private Company $companyA;
	private Company $companyB;
	private User $userA;

	protected function setUp(): void
	{
		parent::setUp();
		$this->companyA = $this->makeCompany('IDOR A');
		$this->companyB = $this->makeCompany('IDOR B');
		$this->userA = $this->makeUser(User::COMPANY_ADMIN, [$this->companyA]);
	}

	public function test_sales_gathering_rows_cannot_be_planted_into_another_company(): void
	{
		$this->actAs($this->userA)->post(route('salesGathering.store', ['company' => $this->companyA->id]), [
			'company_id' => $this->companyB->id, 'zone' => 'Planted Zone', 'net_sales_value' => 1,
		])->assertRedirect();

		$this->assertSame(0, SalesGathering::where('company_id', $this->companyB->id)->where('zone', 'Planted Zone')->count());
		$this->assertSame(1, SalesGathering::where('company_id', $this->companyA->id)->where('zone', 'Planted Zone')->count());
	}

	public function test_sales_gathering_destroy_cannot_delete_other_models_or_companies(): void
	{
		$victim = $this->makeUser(User::USER, [$this->companyB]);
		$this->actAs($this->userA)->delete(route('salesGathering.destroy', ['company' => $this->companyA->id, 'salesGathering' => $victim->id]), [
			'modelType' => 'User',
		])->assertNotFound();
		$this->assertNotNull($victim->fresh());

		$foreignRowId = DB::table('sales_gathering')->insertGetId(['company_id' => $this->companyB->id, 'zone' => 'B row']);
		$this->actAs($this->userA)->delete(route('salesGathering.destroy', ['company' => $this->companyA->id, 'salesGathering' => $foreignRowId]), [
			'modelType' => 'SalesGathering',
		])->assertNotFound();
		$this->assertTrue(DB::table('sales_gathering')->where('id', $foreignRowId)->exists());
	}

	public function test_review_endpoint_cannot_target_arbitrary_tables_or_foreign_rows(): void
	{
		$this->actAs($this->userA)->post(route('confirmed.review', ['company' => $this->companyA->id, 'model' => $this->userA->id]), [
			'table_name' => 'users',
		])->assertStatus(422);

		$foreignMoneyId = DB::table('money_received')->insertGetId(['company_id' => $this->companyB->id, 'is_reviewed' => 0]);
		$this->actAs($this->userA)->post(route('confirmed.review', ['company' => $this->companyA->id, 'model' => $foreignMoneyId]), [
			'table_name' => 'money_received',
		]);
		$this->assertSame(0, (int) DB::table('money_received')->where('id', $foreignMoneyId)->value('is_reviewed'));
	}

	public function test_foreign_exchange_rates_of_other_companies_cannot_be_changed(): void
	{
		$rateId = DB::table('foreign_exchange_rates')->insertGetId([
			'company_id' => $this->companyB->id, 'from_currency' => 'USD', 'to_currency' => 'EGP', 'date' => '2024-01-01', 'exchange_rate' => 30,
		]);

		$this->actAs($this->userA)->patch(route('update.foreign.exchange.rate', ['company' => $this->companyA->id, 'foreignExchangeRate' => $rateId]), [
			'exchange_rate' => 1, 'date' => '2024-01-01', 'from_currency' => 'USD', 'to_currency' => 'EGP',
		])->assertNotFound();
		$this->assertEquals(30, DB::table('foreign_exchange_rates')->where('id', $rateId)->value('exchange_rate'));

		$this->actAs($this->userA)->delete(route('delete.foreign.exchange.rate', ['company' => $this->companyA->id, 'foreignExchangeRate' => $rateId]))->assertNotFound();
		$this->assertTrue(DB::table('foreign_exchange_rates')->where('id', $rateId)->exists());
	}

	public function test_income_statement_reports_of_other_companies_cannot_be_deleted(): void
	{
		$statementId = DB::table('financial_statement_ables')->insertGetId([
			'company_id' => $this->companyB->id, 'type' => 'IncomeStatement', 'name' => 'B statement', 'duration' => '12', 'start_from' => '2024-01-01',
		]);
		$this->actAs($this->userA)->postJson(route('admin.destroy.income.statement.report', ['company' => $this->companyA->id]), [
			'financial_statement_able_id' => $statementId, 'financial_statement_able_item_id' => 1, 'sub_item_type' => 'forecast', 'sub_item_name' => 'x',
		])->assertNotFound();
		$this->assertTrue(DB::table('financial_statement_ables')->where('id', $statementId)->exists());
	}

	public function test_income_statement_dates_endpoint_is_scoped_to_own_companies(): void
	{
		$statementId = DB::table('financial_statement_ables')->insertGetId([
			'company_id' => $this->companyB->id, 'type' => 'IncomeStatement', 'name' => 'B statement', 'duration' => '12', 'start_from' => '2024-01-01',
		]);
		$this->actAs($this->userA)->getJson('/getStartDateAndEndDateOfIncomeStatementForCompany?income_statement_id='.$statementId)
			->assertOk()->assertJson(['status' => 0, 'dates' => []]);
	}

	public function test_overdraft_rates_of_other_companies_cannot_be_deleted(): void
	{
		$now = now()->toDateTimeString();
		$institutionA = DB::table('financial_institutions')->insertGetId(['company_id' => $this->companyA->id, 'updated_by' => $this->userA->id, 'created_at' => $now, 'updated_at' => $now]);
		$overdraftB = DB::table('clean_overdrafts')->insertGetId(['company_id' => $this->companyB->id]);
		$rateB = DB::table('clean_overdraft_rates')->insertGetId(['company_id' => $this->companyB->id, 'clean_overdraft_id' => $overdraftB, 'date' => '2024-01-01']);

		$this->actAs($this->userA)->get(route('clean-overdraft-delete-rate', [
			'company' => $this->companyA->id, 'financialInstitution' => $institutionA, 'rate' => $rateB,
		]))->assertNotFound();
		$this->assertTrue(DB::table('clean_overdraft_rates')->where('id', $rateB)->exists());
	}

	public function test_invoice_balances_of_other_companies_cannot_be_modified(): void
	{
		$partnerB = DB::table('partners')->insertGetId(['company_id' => $this->companyB->id, 'name' => 'Customer B']);
		$invoiceB = DB::table('customer_invoices')->insertGetId([
			'company_id' => $this->companyB->id, 'customer_id' => $partnerB, 'currency' => 'EGP',
			'invoice_amount' => 1000, 'net_invoice_amount' => 1000, 'invoice_date' => '2024-01-01',
		]);
		// net_balance is derived by a BEFORE INSERT trigger — compare against what it stored.
		$balanceBefore = DB::table('customer_invoices')->where('id', $invoiceB)->value('net_balance');

		$this->actAs($this->userA)->patchJson(route('update.invoice.deductions', [
			'company' => $this->companyA->id, 'modelId' => $invoiceB, 'modelType' => 'CustomerInvoice',
		]), ['deductions' => [['deduction_id' => 1, 'date' => '2024-01-02', 'amount' => 999]]])->assertNotFound();
		$this->assertEquals($balanceBefore, DB::table('customer_invoices')->where('id', $invoiceB)->value('net_balance'));
		$this->assertSame(0, DB::table('invoice_deductions')->where('invoice_id', $invoiceB)->count());

		// The invoice model name in the URL is allow-listed too.
		$this->actAs($this->userA)->patchJson(route('update.invoice.deductions', [
			'company' => $this->companyA->id, 'modelId' => $this->userA->id, 'modelType' => 'User',
		]), ['deductions' => []])->assertNotFound();
	}

	public function test_down_payment_settlements_cannot_target_other_companies(): void
	{
		$partnerB = DB::table('partners')->insertGetId(['company_id' => $this->companyB->id, 'name' => 'Customer B']);
		$downPaymentB = DB::table('money_received')->insertGetId(['company_id' => $this->companyB->id, 'is_reviewed' => 0]);
		$before = DB::table('money_received')->where('id', $downPaymentB)->first();

		$this->actAs($this->userA)->postJson(route('store.down.payment.settlement', [
			'company' => $this->companyA->id, 'downPaymentId' => $downPaymentB, 'partnerId' => $partnerB, 'modelType' => 'CustomerInvoice',
		]), ['model_type' => 'CustomerInvoice', 'down_payment_id' => $downPaymentB, 'settlement_date' => '2024-01-02', 'settlements' => []])->assertNotFound();
		$this->assertEquals((array) $before, (array) DB::table('money_received')->where('id', $downPaymentB)->first());
	}
}
