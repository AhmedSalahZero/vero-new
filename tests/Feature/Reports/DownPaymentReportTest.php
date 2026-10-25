<?php

namespace Tests\Feature\Reports;

use App\Http\Controllers\DownPaymentReportController;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Down Payment Report: the down payments received from customers (Money
 * Received) or paid to suppliers (Money Payment) on or after a date, in
 * the chosen currencies - each with the amount settled against invoices
 * and what is left, zero balances included.
 *
 * Every row is written inside a transaction that is rolled back, and every
 * date sits in 2099 so the company's real down payments never reach an
 * assertion. The controller is called directly: this project gates screens
 * by menu visibility and in the controller, not by route middleware.
 */
class DownPaymentReportTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private Partner $customer;

    private Partner $supplier;

    protected function setUpTraits()
    {
        config(['database.connections.mysql.database' => env('SMOKE_DB', 'veroanalysis')]);
        DB::purge('mysql');

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Development database not reachable: '.$e->getMessage());
        }

        $this->company = Company::create(['name' => ['en' => 'Down Payment Report Co', 'ar' => 'Down Payment Report Co']]);
        CompanySystem::create(['company_id' => $this->company->id, 'system_name' => CASH_VERO]);
        $this->company = $this->company->fresh('systems');

        $this->customer = Partner::create(['company_id' => $this->company->id, 'name' => 'Down Payment Customer '.uniqid(), 'is_customer' => 1]);
        $this->supplier = Partner::create(['company_id' => $this->company->id, 'name' => 'Down Payment Supplier '.uniqid(), 'is_supplier' => 1]);
    }

    private function actAsUserWith(array $permissions): void
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Down Payment Report '.uniqid(),
            'email' => 'down-payment-report-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $this->company->id,
        ]);
        $user->assignRole('user');
        $user->givePermissionTo($permissions);
        $user->companies()->attach($this->company->id);
        $user->load('roles', 'permissions', 'companies');

        app()->forgetInstance(Company::class);
        $this->actingAs($user);
    }

    /**
     * @param  array<int, float>  $settlements  amounts settled against invoices
     */
    private function customerDownPayment(string $date, string $currency, float $amount, array $settlements = []): int
    {
        $id = DB::table('money_received')->insertGetId([
            'company_id' => $this->company->id,
            'partner_id' => $this->customer->id,
            'money_type' => 'down-payment',
            'type' => 'cash-in-safe',
            'receiving_date' => $date,
            'received_amount' => $amount,
            'amount_in_invoice_currency' => $amount,
            'currency' => $currency,
            'receiving_currency' => $currency,
        ]);
        foreach ($settlements as $settled) {
            DB::table('settlements')->insert([
                'money_received_id' => $id,
                'company_id' => $this->company->id,
                'partner_id' => $this->customer->id,
                'settlement_amount' => (string) $settled,
            ]);
        }

        return $id;
    }

    private function supplierDownPayment(string $date, string $currency, float $amount, array $settlements = []): int
    {
        $id = DB::table('money_payments')->insertGetId([
            'company_id' => $this->company->id,
            'partner_id' => $this->supplier->id,
            'money_type' => 'down-payment',
            'type' => 'cash-payment',
            'delivery_date' => $date,
            'paid_amount' => $amount,
            'amount_in_invoice_currency' => $amount,
            'currency' => $currency,
            'payment_currency' => $currency,
        ]);
        foreach ($settlements as $settled) {
            DB::table('payment_settlements')->insert([
                'money_payment_id' => $id,
                'company_id' => $this->company->id,
                'partner_id' => $this->supplier->id,
                'settlement_amount' => (string) $settled,
                'letter_of_credit_issuance_id' => 0,
            ]);
        }

        return $id;
    }

    /** @return array<string, mixed> the data handed to the result view */
    private function reportData(array $query): array
    {
        $request = Request::create('/en/'.$this->company->id.'/down-payment-report/result', 'GET', $query);
        $this->app->instance('request', $request);

        return app(DownPaymentReportController::class)->result($this->company, $request)->getData();
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function rowsOf(array $data)
    {
        return collect($data['paginator']->items());
    }

    public function test_customer_down_payments_show_amount_settled_and_balance(): void
    {
        $this->actAsUserWith(['view money received']);
        $partlyUsed = $this->customerDownPayment('2099-03-01', 'USD', 1000, [300, 200]);
        $untouched = $this->customerDownPayment('2099-03-02', 'USD', 500);

        $data = $this->reportData(['type' => 'customer', 'from_date' => '2099-03-01', 'currencies' => ['USD']]);

        $partly = $this->rowsOf($data)->firstWhere('id', $partlyUsed);
        $this->assertSame($this->customer->name, $partly['partnerName']);
        $this->assertSame(1000.0, $partly['amount']);
        $this->assertSame(500.0, $partly['settled']);
        $this->assertSame(500.0, $partly['balance']);
        $this->assertSame('USD', $partly['currency']);

        $none = $this->rowsOf($data)->firstWhere('id', $untouched);
        $this->assertSame(0.0, $none['settled']);
        $this->assertSame(500.0, $none['balance']);
    }

    public function test_a_fully_settled_down_payment_is_still_listed_with_a_zero_balance(): void
    {
        $this->actAsUserWith(['view money received']);
        $used = $this->customerDownPayment('2099-04-01', 'EGP', 800, [500, 300]);

        $row = $this->rowsOf($this->reportData(['type' => 'customer', 'from_date' => '2099-04-01']))->firstWhere('id', $used);

        $this->assertNotNull($row, 'A down payment with nothing left must still appear.');
        $this->assertSame(0.0, $row['balance']);
    }

    public function test_only_down_payments_from_the_date_onwards_are_listed(): void
    {
        $this->actAsUserWith(['view money received']);
        $early = $this->customerDownPayment('2099-05-01', 'EGP', 10);
        $onDate = $this->customerDownPayment('2099-05-10', 'EGP', 20);
        $later = $this->customerDownPayment('2099-05-20', 'EGP', 30);

        $ids = $this->rowsOf($this->reportData(['type' => 'customer', 'from_date' => '2099-05-10']))->pluck('id')->all();

        $this->assertContains($onDate, $ids);
        $this->assertContains($later, $ids);
        $this->assertNotContains($early, $ids);
    }

    public function test_an_ordinary_money_received_is_not_a_down_payment(): void
    {
        $this->actAsUserWith(['view money received']);
        $ordinary = DB::table('money_received')->insertGetId([
            'company_id' => $this->company->id,
            'partner_id' => $this->customer->id,
            'money_type' => 'money-received',
            'type' => 'cash-in-safe',
            'receiving_date' => '2099-06-01',
            'received_amount' => 99,
            'amount_in_invoice_currency' => 99,
            'currency' => 'EGP',
            'receiving_currency' => 'EGP',
        ]);

        $ids = $this->rowsOf($this->reportData(['type' => 'customer', 'from_date' => '2099-06-01']))->pluck('id')->all();

        $this->assertNotContains($ordinary, $ids);
    }

    public function test_only_the_chosen_currencies_are_listed_and_totals_are_per_currency(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->customerDownPayment('2099-07-01', 'EGP', 100, [40]);
        $this->customerDownPayment('2099-07-02', 'USD', 10);
        $this->customerDownPayment('2099-07-03', 'USD', 30, [30]);

        $usdOnly = $this->reportData(['type' => 'customer', 'from_date' => '2099-07-01', 'currencies' => ['USD']]);
        $this->assertSame(['USD'], $this->rowsOf($usdOnly)->pluck('currency')->unique()->values()->all());
        $this->assertEquals(['USD' => ['amount' => 40.0, 'settled' => 30.0, 'balance' => 10.0]], $usdOnly['totals']['perCurrency']);

        $both = $this->reportData(['type' => 'customer', 'from_date' => '2099-07-01', 'currencies' => ['USD', 'EGP']]);
        $this->assertSame(3, $both['totals']['count']);
        $this->assertEqualsCanonicalizing(['EGP', 'USD'], array_keys($both['totals']['perCurrency']));
    }

    public function test_supplier_down_payments_come_from_money_payments(): void
    {
        $this->actAsUserWith(['view supplier payment']);
        $paid = $this->supplierDownPayment('2099-08-01', 'EGP', 900, [400]);
        $this->customerDownPayment('2099-08-01', 'EGP', 5);

        $data = $this->reportData(['type' => 'supplier', 'from_date' => '2099-08-01']);

        $this->assertSame([$paid], $this->rowsOf($data)->pluck('id')->all());
        $row = $this->rowsOf($data)->first();
        $this->assertSame($this->supplier->name, $row['partnerName']);
        $this->assertSame(900.0, $row['amount']);
        $this->assertSame(400.0, $row['settled']);
        $this->assertSame(500.0, $row['balance']);
    }

    public function test_a_user_cannot_open_a_kind_they_cannot_see_on_its_own_screen(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->supplierDownPayment('2099-09-01', 'EGP', 1);

        $this->expectException(HttpException::class);
        $this->reportData(['type' => 'supplier', 'from_date' => '2099-09-01']);
    }

    public function test_the_form_only_offers_the_kinds_the_user_may_open(): void
    {
        $this->actAsUserWith(['view money received']);

        $view = app(DownPaymentReportController::class)->index($this->company);

        $this->assertSame(['customer'], array_keys($view->getData()['types']));
    }

    public function test_the_form_is_refused_to_a_user_who_may_open_neither_kind(): void
    {
        $this->actAsUserWith(['view home']);

        $this->expectException(HttpException::class);
        app(DownPaymentReportController::class)->index($this->company);
    }

    public function test_the_excel_export_is_a_download_of_the_filtered_range(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->customerDownPayment('2099-10-01', 'EGP', 10);

        $request = Request::create('/en/'.$this->company->id.'/down-payment-report/export', 'GET', ['type' => 'customer', 'from_date' => '2099-10-01']);
        $this->app->instance('request', $request);
        $response = app(DownPaymentReportController::class)->exportExcel($this->company, $request);

        $this->assertStringContainsString('Down-Payment-Report-customer-2099-10-01.xlsx', $response->headers->get('content-disposition'));
    }
}
