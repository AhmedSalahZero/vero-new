<?php

namespace Tests\Feature\Reports;

use App\Http\Controllers\ChequesReportController;
use App\Models\Bank;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\FinancialInstitution;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Cheques Report: the cheques received from customers or payable to
 * suppliers whose due date is on or after a chosen date, in the chosen
 * currencies, optionally narrowed to one bank.
 *
 * Every row is written inside a transaction that is rolled back, and every
 * date sits in 2099 so the company's real cheques never reach an assertion.
 * The controller is called directly: this project gates screens by menu
 * visibility and in the controller, not by route middleware.
 */
class ChequesReportTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private Partner $customer;

    private Partner $supplier;

    private Bank $bankA;

    private Bank $bankB;

    private ?int $deliveryBankId;

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

        $banks = Bank::query()->limit(2)->get();
        if ($banks->count() < 2) {
            $this->markTestSkipped('Development database needs two banks.');
        }
        [$this->bankA, $this->bankB] = [$banks[0], $banks[1]];
        $this->deliveryBankId = FinancialInstitution::query()->whereNotNull('bank_id')->value('id');

        $this->company = Company::create(['name' => ['en' => 'Cheques Report Co', 'ar' => 'Cheques Report Co']]);
        CompanySystem::create(['company_id' => $this->company->id, 'system_name' => CASH_VERO]);
        $this->company = $this->company->fresh('systems');

        $this->customer = Partner::create(['company_id' => $this->company->id, 'name' => 'Cheques Customer '.uniqid(), 'is_customer' => 1]);
        $this->supplier = Partner::create(['company_id' => $this->company->id, 'name' => 'Cheques Supplier '.uniqid(), 'is_supplier' => 1]);
    }

    private function actAsUserWith(array $permissions): void
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Cheques Report '.uniqid(),
            'email' => 'cheques-report-'.uniqid().'@example.test',
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

    private function receivedCheque(string $number, string $dueDate, string $currency, float $amount, Bank $bank, string $status = 'in-safe'): void
    {
        $moneyId = DB::table('money_received')->insertGetId([
            'company_id' => $this->company->id,
            'partner_id' => $this->customer->id,
            'type' => 'cheque',
            'receiving_date' => '2099-01-01',
            'received_amount' => $amount,
            'currency' => $currency,
            'receiving_currency' => $currency,
        ]);
        DB::table('cheques')->insert([
            'money_received_id' => $moneyId,
            'company_id' => $this->company->id,
            'cheque_number' => $number,
            'status' => $status,
            'drawee_bank_id' => $bank->id,
            'account_type' => 'current',
            'due_date' => $dueDate,
        ]);
    }

    private function payableCheque(string $number, string $dueDate, string $currency, float $amount): void
    {
        $moneyId = DB::table('money_payments')->insertGetId([
            'company_id' => $this->company->id,
            'partner_id' => $this->supplier->id,
            'type' => 'payable-cheque',
            'delivery_date' => '2099-01-01',
            'paid_amount' => $amount,
            'currency' => $currency,
            'payment_currency' => $currency,
        ]);
        DB::table('payable_cheques')->insert([
            'money_payment_id' => $moneyId,
            'company_id' => $this->company->id,
            'cheque_number' => $number,
            'status' => 'pending',
            'delivery_bank_id' => $this->deliveryBankId,
            'account_type' => 'current',
            'due_date' => $dueDate,
        ]);
    }

    /** @return array<string, mixed> the data handed to the result view */
    private function reportData(array $query): array
    {
        $request = Request::create('/en/'.$this->company->id.'/cheques-report/result', 'GET', $query);
        $this->app->instance('request', $request);

        return app(ChequesReportController::class)->result($this->company, $request)->getData();
    }

    /** @return array<int, string> */
    private function chequeNumbers(array $data): array
    {
        return collect($data['paginator']->items())->pluck('chequeNumber')->all();
    }

    public function test_received_cheques_are_listed_from_the_due_date_onwards(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->receivedCheque('CHQ-EARLY', '2099-03-01', 'EGP', 100, $this->bankA);
        $this->receivedCheque('CHQ-ON-DATE', '2099-04-01', 'EGP', 200, $this->bankA);
        $this->receivedCheque('CHQ-LATER', '2099-06-01', 'EGP', 300, $this->bankA);

        $numbers = $this->chequeNumbers($this->reportData(['type' => 'received', 'due_date' => '2099-04-01']));

        $this->assertEqualsCanonicalizing(['CHQ-ON-DATE', 'CHQ-LATER'], $numbers);
    }

    public function test_a_row_carries_customer_bank_amount_currency_due_date_and_status(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->receivedCheque('CHQ-DETAIL', '2099-05-01', 'USD', 1250.5, $this->bankA, 'rejected');

        $row = $this->reportData(['type' => 'received', 'due_date' => '2099-05-01', 'currencies' => ['USD']])['paginator']->items()[0];

        $this->assertSame($this->customer->name, $row['partnerName']);
        $this->assertSame(1250.5, $row['amount']);
        $this->assertSame('USD', $row['currency']);
        $this->assertSame('01-05-2099', $row['dueDate']);
        $this->assertSame('rejected', $row['status']);
        $this->assertSame(__('Rejected'), $row['statusLabel']);
        $this->assertSame($this->bankA->getName(), $row['bankName']);
    }

    public function test_only_the_chosen_currencies_are_listed_and_totals_are_per_currency(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->receivedCheque('CHQ-EGP', '2099-07-01', 'EGP', 100, $this->bankA);
        $this->receivedCheque('CHQ-USD-1', '2099-07-02', 'USD', 40, $this->bankA);
        $this->receivedCheque('CHQ-USD-2', '2099-07-03', 'USD', 60, $this->bankA);

        $usdOnly = $this->reportData(['type' => 'received', 'due_date' => '2099-07-01', 'currencies' => ['USD']]);
        $this->assertEqualsCanonicalizing(['CHQ-USD-1', 'CHQ-USD-2'], $this->chequeNumbers($usdOnly));
        $this->assertEquals(['USD' => 100.0], $usdOnly['totals']['perCurrency']);

        $both = $this->reportData(['type' => 'received', 'due_date' => '2099-07-01', 'currencies' => ['USD', 'EGP']]);
        $this->assertEqualsCanonicalizing(['CHQ-EGP', 'CHQ-USD-1', 'CHQ-USD-2'], $this->chequeNumbers($both));
        $this->assertSame(3, $both['totals']['count']);

        $all = $this->reportData(['type' => 'received', 'due_date' => '2099-07-01']);
        $this->assertEqualsCanonicalizing(['CHQ-EGP', 'CHQ-USD-1', 'CHQ-USD-2'], $this->chequeNumbers($all));
    }

    public function test_the_bank_filter_narrows_the_rows_but_keeps_every_bank_in_the_list(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->receivedCheque('CHQ-BANK-A', '2099-08-01', 'EGP', 10, $this->bankA);
        $this->receivedCheque('CHQ-BANK-B', '2099-08-02', 'EGP', 20, $this->bankB);

        $data = $this->reportData(['type' => 'received', 'due_date' => '2099-08-01', 'bank_id' => $this->bankB->id]);

        $this->assertSame(['CHQ-BANK-B'], $this->chequeNumbers($data));
        $this->assertEquals(['EGP' => 20.0], $data['totals']['perCurrency']);
        $this->assertEqualsCanonicalizing([$this->bankA->id, $this->bankB->id], collect($data['banks'])->pluck('id')->all());
    }

    public function test_payable_cheques_come_from_money_payments(): void
    {
        $this->actAsUserWith(['view supplier payment']);
        $this->payableCheque('PAY-CHQ-1', '2099-09-01', 'EGP', 750);
        $this->receivedCheque('CHQ-NOT-PAYABLE', '2099-09-01', 'EGP', 5, $this->bankA);

        $data = $this->reportData(['type' => 'payable', 'due_date' => '2099-09-01']);

        $this->assertSame(['PAY-CHQ-1'], $this->chequeNumbers($data));
        $row = $data['paginator']->items()[0];
        $this->assertSame($this->supplier->name, $row['partnerName']);
        $this->assertSame(750.0, $row['amount']);
        $this->assertSame(__('Pending'), $row['statusLabel']);
    }

    public function test_a_user_cannot_open_a_kind_they_cannot_see_on_its_own_screen(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->payableCheque('PAY-CHQ-FORBIDDEN', '2099-09-01', 'EGP', 1);

        $this->expectException(HttpException::class);
        $this->reportData(['type' => 'payable', 'due_date' => '2099-09-01']);
    }

    public function test_the_form_only_offers_the_kinds_the_user_may_open(): void
    {
        $this->actAsUserWith(['view supplier payment']);

        $view = app(ChequesReportController::class)->index($this->company);

        $this->assertSame(['payable'], array_keys($view->getData()['types']));
    }

    public function test_the_form_is_refused_to_a_user_who_may_open_neither_kind(): void
    {
        $this->actAsUserWith(['view home']);

        $this->expectException(HttpException::class);
        app(ChequesReportController::class)->index($this->company);
    }

    public function test_the_due_date_and_type_are_required(): void
    {
        $this->actAsUserWith(['view money received']);

        $this->expectException(ValidationException::class);
        $this->reportData(['type' => 'received']);
    }

    public function test_the_excel_export_is_a_download_of_the_filtered_range(): void
    {
        $this->actAsUserWith(['view money received']);
        $this->receivedCheque('CHQ-EXPORT', '2099-10-01', 'EGP', 10, $this->bankA);

        $request = Request::create('/en/'.$this->company->id.'/cheques-report/export', 'GET', ['type' => 'received', 'due_date' => '2099-10-01']);
        $this->app->instance('request', $request);
        $response = app(ChequesReportController::class)->exportExcel($this->company, $request);

        $this->assertStringContainsString('Cheques-Report-received-2099-10-01.xlsx', $response->headers->get('content-disposition'));
    }
}
