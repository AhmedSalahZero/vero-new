<?php

namespace Tests\Feature\Partners;

use App\Http\Middleware\PreventLocalPartnerCreationForOdooCompanies;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * * الشركة المربوطة بأودو ماينفعش تعمل partner من السيستم: الـ partner
 * * المحلي مالوش odoo_id و عمره ما بيتربط ، فبيكسر أول ما يتبعت لأودو
 * * (خطاب الضمان 204 في الشركة 92)
 *
 * * الشرط على مستوى الشركة: اليوزر في التستات دي مالوش بيانات أودو
 * * و برضه لازم يترفض
 */
class OdooCompanyCannotCreatePartnersLocallyTest extends TestCase
{
    private const MIDDLEWARE = 'noLocalPartnersForOdoo';

    private const ADD_NEW_ROUTES = ['add.new.partner', 'add.new.partner.type'];

    private const SCREENS = ['partners', 'customers', 'suppliers', 'employees', 'shareholders', 'other.partners', 'subsidiary.companies'];

    private ?string $originalDatabase = null;

    private bool $inTransaction = false;

    public function createApplication()
    {
        putenv('ROUTING_LOCALE=en');
        $_ENV['ROUTING_LOCALE'] = 'en';
        $_SERVER['ROUTING_LOCALE'] = 'en';

        return parent::createApplication();
    }

    protected function tearDown(): void
    {
        putenv('ROUTING_LOCALE');
        unset($_ENV['ROUTING_LOCALE'], $_SERVER['ROUTING_LOCALE']);

        if ($this->inTransaction) {
            DB::rollBack();
            $this->inTransaction = false;
        }

        if ($this->originalDatabase !== null) {
            config(['database.connections.mysql.database' => $this->originalDatabase]);
            DB::purge('mysql');
        }

        parent::tearDown();
    }

    /* ───────────── كل route بيعمل partner عليه الحماية ───────────── */

    public function test_every_route_that_creates_a_partner_is_guarded(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $this->assertInstanceOf(\Illuminate\Foundation\Http\Kernel::class, $kernel);
        $this->assertSame(
            PreventLocalPartnerCreationForOdooCompanies::class,
            $kernel->getMiddlewareAliases()[self::MIDDLEWARE] ?? null
        );

        $unguarded = [];
        foreach ($this->creatingRouteNames() as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "route {$name} مش موجود");

            if (! in_array(self::MIDDLEWARE, $route->gatherMiddleware(), true)) {
                $unguarded[] = $name;
            }
        }

        $this->assertSame([], $unguarded, "These routes create partners without the Odoo guard:\n  ".implode("\n  ", $unguarded));
    }

    /* ───────────── شركة مربوطة بأودو ───────────── */

    public function test_an_odoo_company_cannot_store_a_partner_from_any_screen(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: true);
        $before = $this->partnersCount($company);

        foreach (self::SCREENS as $screen) {
            $response = $this->actingAs($actor)->postJson(
                route("{$screen}.store", ['company' => $company->id]),
                ['name' => 'Local '.$screen, 'company_id' => $company->id]
            );

            $response->assertOk();
            $response->assertJson(['status' => false, 'message' => $this->blockedMessage()], "{$screen}.store");
        }

        $this->assertSame($before, $this->partnersCount($company), 'مفيش partner يتعمل');
    }

    public function test_an_odoo_company_cannot_add_a_partner_from_inside_a_form(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: true);
        $before = $this->partnersCount($company);

        /**
         * * زراير Add New بتبعت بـ $.ajax ، فالطلب عليه X-Requested-With
         */
        $this->actingAs($actor)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('add.new.partner', ['company' => $company->id, 'type' => 'Customer']), ['customerName' => 'Offshore Shukeir Oil Company'])
            ->assertOk()
            ->assertJson(['status' => false, 'message' => $this->blockedMessage()]);

        $this->actingAs($actor)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('add.new.partner.type', ['company' => $company->id, 'type' => 'Customer']), ['value' => 'Offshore Shukeir Oil Company'])
            ->assertOk()
            ->assertJson(['status' => false, 'message' => $this->blockedMessage()]);

        $this->assertSame($before, $this->partnersCount($company), 'مفيش partner يتعمل');
    }

    public function test_a_plain_form_post_is_sent_back_with_the_message_instead_of_raw_json(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: true);
        $before = $this->partnersCount($company);

        $this->actingAs($actor)
            ->post(route('customers.store', ['company' => $company->id]), ['name' => 'Plain Post', 'company_id' => $company->id])
            ->assertRedirect()
            ->assertSessionHas('fail', $this->blockedMessage());

        $this->assertSame($before, $this->partnersCount($company), 'مفيش partner يتعمل');
    }

    public function test_an_odoo_company_is_sent_back_from_every_create_page(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: true);

        foreach (self::SCREENS as $screen) {
            $this->actingAs($actor)
                ->get(route("{$screen}.create", ['company' => $company->id]))
                ->assertRedirect()
                ->assertSessionHas('fail', $this->blockedMessage());
        }
    }

    /* ───────────── شركة مش مربوطة: لازم تفضل شغالة زي ما هي ───────────── */

    public function test_a_company_without_odoo_still_creates_partners_from_the_add_new_buttons(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: false);

        $this->actingAs($actor)
            ->post(route('add.new.partner', ['company' => $company->id, 'type' => 'Customer']), ['customerName' => 'Add New Customer'])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->actingAs($actor)
            ->post(route('add.new.partner.type', ['company' => $company->id, 'type' => 'Supplier']), ['value' => 'Add New Supplier', 'type' => 'Supplier'])
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertTrue(DB::table('partners')->where('company_id', $company->id)->where('name', 'Add New Customer')->where('is_customer', 1)->exists());
        $this->assertTrue(DB::table('partners')->where('company_id', $company->id)->where('name', 'Add New Supplier')->where('is_supplier', 1)->exists());
    }

    public function test_a_company_without_odoo_still_creates_customers_from_the_customers_screen(): void
    {
        [$company, $actor] = $this->companyAndUser(withOdoo: false);

        $this->actingAs($actor)
            ->postJson(route('customers.store', ['company' => $company->id]), ['name' => 'Screen Customer', 'company_id' => $company->id])
            ->assertOk()
            ->assertJsonStructure(['redirectTo']);

        $this->assertTrue(DB::table('partners')->where('company_id', $company->id)->where('name', 'Screen Customer')->where('is_customer', 1)->exists());
    }

    /* ───────────── الواجهة ───────────── */

    public function test_the_add_new_partner_buttons_are_hidden_for_odoo_companies(): void
    {
        $guard = '@if(!$company->hasOdooCredentials())';
        $forms = [
            'contracts/form',
            'reports/LetterOfCreditIssuance/lc-facility-form',
            'reports/LetterOfCreditIssuance/against-cd-form',
            'reports/LetterOfCreditIssuance/against-td-form',
            'reports/LetterOfCreditIssuance/hundred-percentage-cash-cover-form',
        ];

        foreach ($forms as $form) {
            $source = $this->viewSource($form);
            $guardAt = strpos($source, $guard);
            $buttonAt = strpos($source, 'data-target="#add-new-customer-modal"');
            $endifAt = $guardAt === false ? false : strpos($source, '@endif', $buttonAt);

            $this->assertNotFalse($guardAt, "{$form}: Add New مش جوه شرط أودو");
            $this->assertNotFalse($buttonAt, "{$form}: زرار Add New مش موجود");
            $this->assertLessThan($buttonAt, $guardAt, "{$form}: الشرط لازم يبقى قبل الزرار");
            $this->assertGreaterThan(strpos($source, 'id="add-new-customer-modal"'), $endifAt, "{$form}: الـ modal لازم يبقى جوه الشرط");
        }

        $openingBalance = $this->viewSource('opening-balance/form');
        $this->assertStringNotContainsString(':add-new-modal="true" :add-new-modal-modal-type="\'Customer\'"', $openingBalance);
        $this->assertSame(2, substr_count($openingBalance, ':add-new-modal="!$company->hasOdooCredentials()"'));

        $this->assertStringContainsString("@if(!\$company->hasOdooCredentials())\n            <div class=\"flex-tabs\">\n                <a href=\"{{ route('partners.create'", $this->viewSource('partners/index'));
    }

    /* ───────────── helpers ───────────── */

    private function creatingRouteNames(): array
    {
        $names = self::ADD_NEW_ROUTES;
        foreach (self::SCREENS as $screen) {
            $names[] = "{$screen}.create";
            $names[] = "{$screen}.store";
        }

        return $names;
    }

    private function blockedMessage(): string
    {
        return __('This company is linked to Odoo. Partners can only be added in Odoo and then loaded with "Read Odoo Partners".');
    }

    private function partnersCount(Company $company): int
    {
        return DB::table('partners')->where('company_id', $company->id)->count();
    }

    private function viewSource(string $view): string
    {
        return file_get_contents(resource_path("views/{$view}.blade.php"));
    }

    /**
     * * اليوزر مالوش بيانات أودو عمدا ، عشان نثبت إن الشرط على الشركة
     *
     * @return array{0: Company, 1: User}
     */
    private function companyAndUser(bool $withOdoo): array
    {
        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ]);

        $this->connectToSmokeDatabase();

        $company = Company::create(['name' => ['en' => 'Partner Guard Co', 'ar' => 'Partner Guard Co']]);
        if ($withOdoo) {
            $company->forceFill([
                'odoo_db_url' => 'http://127.0.0.1:9',
                'odoo_db_name' => 'no_such_db',
                'odoo_integration_start_date' => '2025-11-01',
            ])->save();
        }
        CompanySystem::create([
            'company_id' => $company->id,
            'system_name' => CASH_VERO,
        ]);

        /** @var User $actor */
        $actor = User::create([
            'name' => 'Partner Guard Tester '.uniqid(),
            'email' => 'partner-guard-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $company->id,
        ]);
        $actor->assignRole('user');
        $actor->companies()->attach($company->id);
        $actor->load('roles', 'permissions', 'companies');

        $this->assertFalse($company->hasOdooIntegrationCredentials($actor), 'اليوزر مالوش بيانات أودو');

        return [$company, $actor];
    }

    private function connectToSmokeDatabase(): void
    {
        $this->originalDatabase = config('database.connections.mysql.database');
        $candidates = array_values(array_unique(array_filter([
            env('SMOKE_DB'),
            'veroanalysis',
            'veroanalysisb_dev',
            $this->originalDatabase,
        ])));

        foreach ($candidates as $database) {
            config(['database.connections.mysql.database' => $database]);
            DB::purge('mysql');
            try {
                DB::connection('mysql')->getPdo();
                DB::table('partners')->limit(1)->get();
                DB::beginTransaction();
                $this->inTransaction = true;

                return;
            } catch (\Throwable) {
                continue;
            }
        }

        $this->markTestSkipped('Development database not reachable.');
    }
}
