<?php

namespace Tests\Feature\Contracts;

use App\Http\Controllers\ContractsController;
use App\Http\Middleware\CashManagementMiddleware;
use App\Http\Middleware\CheckIfAccountExpired;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\Contract;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * The Contracts list has a fourth tab, All: every contract whatever its
 * state, each row saying whether it is Running, Running And Against or
 * Finished. The three status tabs are unchanged.
 */
class AllContractsTabTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private Partner $customer;

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

        $this->company = Company::create(['name' => ['en' => 'All Tab Co', 'ar' => 'All Tab Co']]);
        CompanySystem::create(['company_id' => $this->company->id, 'system_name' => CASH_VERO]);
        $this->company = $this->company->fresh('systems');
        $this->customer = Partner::create(['company_id' => $this->company->id, 'name' => 'All Tab Customer '.uniqid(), 'is_customer' => 1]);

        /** @var User $user */
        $user = User::create([
            'name' => 'All Contracts Tab '.uniqid(),
            'email' => 'all-contracts-tab-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $this->company->id,
        ]);
        $user->assignRole('user');
        $user->givePermissionTo(['view customers contracts', 'update customers contracts', 'delete customers contracts']);
        $user->companies()->attach($this->company->id);
        $user->load('roles', 'permissions', 'companies');
        app()->forgetInstance(Company::class);
        $this->actingAs($user);
    }

    private function makeContract(string $type, string $status): Contract
    {
        $tag = uniqid('all-');

        return Contract::create([
            'company_id' => $this->company->id,
            'partner_id' => $this->customer->id,
            'model_type' => $type,
            'name' => 'All Tab '.$tag,
            'code' => 'ALL-'.$tag,
            'currency' => 'EGP',
            'amount' => 100,
            'exchange_rate' => 1,
            'status' => $status,
            'start_date' => '2099-01-01',
            'end_date' => '2099-12-31',
        ]);
    }

    /** The whole page as the browser gets it, layout and all */
    private function page(string $type): string
    {
        // Session / errors middleware stays on - the layout reads $errors; only the gates are skipped
        return $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
            CheckIfAccountExpired::class,
            CashManagementMiddleware::class,
        ])
            ->get(route('contracts.index', ['company' => $this->company->id, 'type' => $type]))
            ->assertOk()
            ->getContent();
    }

    /** @return \Illuminate\View\View */
    private function index(string $type)
    {
        $request = Request::create('/en/'.$this->company->id.'/contracts/'.$type, 'GET');
        $this->app->instance('request', $request);

        return app(ContractsController::class)->index($this->company, $type);
    }

    public function test_the_all_tab_is_the_fourth_tab(): void
    {
        $data = $this->index('Customer')->getData();

        $this->assertSame(['running', 'running_and_against', 'finished', 'all'], $data['contractStatues']);
        $this->assertArrayHasKey('all', $data['paginators']);
    }

    public function test_the_all_tab_lists_every_status_and_says_which_each_one_is(): void
    {
        $running = $this->makeContract('Customer', Contract::RUNNING);
        $against = $this->makeContract('Customer', Contract::RUNNING_AND_AGAINST);
        $finished = $this->makeContract('Customer', Contract::FINISHED);

        $all = $this->index('Customer')->getData()['items']['all'];

        $this->assertSame(Contract::RUNNING, $all[$running->id]['parent']['status']);
        $this->assertSame(Contract::RUNNING_AND_AGAINST, $all[$against->id]['parent']['status']);
        $this->assertSame(Contract::FINISHED, $all[$finished->id]['parent']['status']);
    }

    public function test_the_status_tabs_still_hold_only_their_own_contracts(): void
    {
        $running = $this->makeContract('Customer', Contract::RUNNING);
        $finished = $this->makeContract('Customer', Contract::FINISHED);

        $items = $this->index('Customer')->getData()['items'];

        $this->assertArrayHasKey($running->id, $items['running']);
        $this->assertArrayNotHasKey($finished->id, $items['running'] ?? []);
        $this->assertArrayHasKey($finished->id, $items['finished']);
        $this->assertArrayNotHasKey($running->id, $items['finished'] ?? []);
    }

    public function test_the_all_tab_only_holds_the_contracts_of_the_page_type(): void
    {
        $customerContract = $this->makeContract('Customer', Contract::RUNNING);
        $supplierContract = $this->makeContract('Supplier', Contract::RUNNING);

        $customerAll = $this->index('Customer')->getData()['items']['all'];
        $supplierAll = $this->index('Supplier')->getData()['items']['all'];

        $this->assertArrayHasKey($customerContract->id, $customerAll);
        $this->assertArrayNotHasKey($supplierContract->id, $customerAll);
        $this->assertArrayHasKey($supplierContract->id, $supplierAll);
        $this->assertArrayNotHasKey($customerContract->id, $supplierAll);
    }

    public function test_the_all_tab_total_counts_every_status(): void
    {
        $this->makeContract('Customer', Contract::RUNNING);
        $this->makeContract('Customer', Contract::RUNNING_AND_AGAINST);
        $this->makeContract('Customer', Contract::FINISHED);

        $this->assertSame(3, $this->index('Customer')->getData()['paginators']['all']->total());
    }

    public function test_the_page_shows_a_status_column_and_a_badge_per_row_on_the_all_tab_only(): void
    {
        $this->makeContract('Customer', Contract::RUNNING);
        $this->makeContract('Customer', Contract::FINISHED);

        $html = $this->page('Customer');

        // the All pane carries the extra Status header and one badge per contract
        preg_match('/<div class="tab-pane[^"]*" id="all"(.*)$/s', $html, $allPane);
        $this->assertNotEmpty($allPane, 'The All tab pane must be rendered.');
        $this->assertStringContainsString('badge-success', $allPane[1]);
        $this->assertStringContainsString('badge-secondary', $allPane[1]);
        $this->assertStringContainsString('href="#all"', $html);

        // the three status panes keep their seven columns - no Status header, no badges
        preg_match('/<div class="tab-pane[^"]*" id="running"(.*?)<div class="tab-pane/s', $html, $runningPane);
        $this->assertStringNotContainsString('badge-success', $runningPane[1]);
    }

    public function test_the_same_contract_does_not_reuse_modal_ids_between_tabs(): void
    {
        $contract = $this->makeContract('Customer', Contract::RUNNING);

        $html = $this->page('Customer');

        $this->assertSame(1, substr_count($html, 'id="mark-as-finished-contract-'.$contract->id.'"'));
        $this->assertSame(1, substr_count($html, 'id="mark-as-finished-contract-'.$contract->id.'-all"'));
        $this->assertSame(1, substr_count($html, 'id="modal-delete-'.$contract->id.'"'));
        $this->assertSame(1, substr_count($html, 'id="modal-delete-'.$contract->id.'-all"'));
    }
}
