<?php

namespace Tests\Feature\LetterOfGuarantee;

use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\LetterOfGuaranteeIssuance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * Renewal of an LG is allowed before the current expiry date.
 * The form and the history table must both be on the page even when
 * renewal_date is still in the future. Cancelled LGs keep the form hidden.
 */
class LgRenewalDateFormVisibilityTest extends TestCase
{
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
        Carbon::setTestNow();
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

    public function test_the_renewal_blade_does_not_gate_the_form_on_is_expired(): void
    {
        $blade = file_get_contents(resource_path('views/reports/LetterOfGuaranteeIssuance/renewal-date/index.blade.php'));

        $this->assertStringContainsString('Adjusted Renewal Date Section', $blade);
        $this->assertStringContainsString("{{ __('Date') }}", $blade);
        $this->assertDoesNotMatchRegularExpression(
            '/@if\([^)]*isExpired\(\)/',
            $blade
        );
        $this->assertMatchesRegularExpression(
            '/@if\s*\(\s*isset\(\$model\)\s*\|\|\s*!\s*\$letterOfGuaranteeIssuance->isCancelled\(\)\s*\)/',
            $blade
        );
    }

    public function test_the_renewal_page_shows_the_form_and_history_before_expiry(): void
    {
        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ]);

        $this->connectToSmokeDatabase();

        Carbon::setTestNow('2026-08-25');

        $company = Company::create(['name' => ['en' => 'LG Renewal Form Co', 'ar' => 'LG Renewal Form Co']]);
        CompanySystem::create([
            'company_id' => $company->id,
            'system_name' => CASH_VERO,
        ]);

        /** @var User $actor */
        $actor = User::create([
            'name' => 'LG Renewal Tester '.uniqid(),
            'email' => 'lg-renewal-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $company->id,
        ]);
        $actor->assignRole('user');
        $actor->companies()->attach($company->id);
        $actor->load('roles', 'permissions', 'companies');

        $lgId = (int) DB::table('letter_of_guarantee_issuances')->insertGetId([
            'company_id' => $company->id,
            'lg_code' => 'LG-EARLY-'.uniqid(),
            'lg_type' => 'final-lgs',
            'category_name' => LetterOfGuaranteeIssuance::NEW_ISSUANCE,
            'status' => LetterOfGuaranteeIssuance::RUNNING,
            'source' => LetterOfGuaranteeIssuance::LG_FACILITY,
            'transaction_name' => 'Early Renewal',
            'issuance_date' => '2026-01-10',
            'renewal_date' => '2027-01-10',
            'lg_amount' => 100000,
            'lg_currency' => 'EGP',
            'cash_cover_rate' => 10,
            'cash_cover_amount' => 10000,
        ]);

        $letterOfGuaranteeIssuance = LetterOfGuaranteeIssuance::findOrFail($lgId);
        $this->assertFalse($letterOfGuaranteeIssuance->isExpired());
        $this->assertFalse($letterOfGuaranteeIssuance->isCancelled());

        $response = $this->actingAs($actor)->get(route('letter.of.issuance.renewal.date', [
            'company' => $company->id,
            'letterOfGuaranteeIssuance' => $lgId,
        ]));

        $response->assertOk();
        $response->assertSee('Adjusted Renewal Date Section', false);
        $response->assertSee(__('Date'), false);
        $response->assertSee(__('Days Count'), false);
        $response->assertSee(__('Fees Amount'), false);
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
                DB::table('letter_of_guarantee_issuances')->limit(1)->get();
                DB::beginTransaction();
                $this->inTransaction = true;

                return;
            } catch (\Throwable $e) {
                continue;
            }
        }

        $this->markTestSkipped('Development database not reachable.');
    }
}
