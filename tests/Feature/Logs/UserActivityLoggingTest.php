<?php

namespace Tests\Feature\Logs;

use App\Http\Middleware\CashManagementMiddleware;
use App\Http\Middleware\CheckIfAccountExpired;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * الـ login بيحدّث users.last_login_at وبيتسجل في logs من غير شركة،
 * وكل صفحة CashVero المستخدم بيفتحها بتتسجل في logs بالشركة واسم القسم.
 * صفحات Vero والـ ajax والـ JSON وتحميل الملفات ما بيتسجلوش من الـ middleware.
 */
class UserActivityLoggingTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $user;

    public function createApplication()
    {
        putenv('ROUTING_LOCALE=en');
        $_ENV['ROUTING_LOCALE'] = 'en';
        $_SERVER['ROUTING_LOCALE'] = 'en';

        return parent::createApplication();
    }

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

        $this->company = Company::create(['name' => ['en' => 'Activity Log Co', 'ar' => 'Activity Log Co']]);
        CompanySystem::create(['company_id' => $this->company->id, 'system_name' => CASH_VERO]);
        $this->company = $this->company->fresh('systems');

        $this->user = User::create([
            'name' => 'Activity Log '.uniqid(),
            'email' => 'activity-log-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
        ]);
        $this->user->assignRole('user');
        $this->user->givePermissionTo(['view customers contracts']);
        $this->user->companies()->attach($this->company->id);
        $this->user->load('roles', 'permissions', 'companies');
        app()->forgetInstance(Company::class);

        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
            CheckIfAccountExpired::class,
            CashManagementMiddleware::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        putenv('ROUTING_LOCALE');
        unset($_ENV['ROUTING_LOCALE'], $_SERVER['ROUTING_LOCALE']);

        parent::tearDown();
    }

    public function test_login_sets_last_login_at_without_touching_updated_at(): void
    {
        $updatedAtBefore = $this->user->fresh()->updated_at;
        Carbon::setTestNow(now()->addDays(3)->startOfMinute());

        Auth::login($this->user);

        $fresh = $this->user->fresh();
        $this->assertNotNull($fresh->last_login_at);
        $this->assertSame(now()->format('Y-m-d H:i:s'), $fresh->last_login_at->format('Y-m-d H:i:s'));
        $this->assertEquals($updatedAtBefore, $fresh->updated_at);
    }

    public function test_login_and_logout_are_stored_without_a_company(): void
    {
        Auth::login($this->user);
        $login = $this->lastLogRow();
        $this->assertNull($login->company_id);
        $this->assertSame(Log::generateLogMessage('successLogin'), $login->activity);

        Auth::logout();
        $logout = $this->lastLogRow();
        $this->assertNull($logout->company_id);
        $this->assertSame(Log::generateLogMessage('successLogout'), $logout->activity);
    }

    public function test_opening_a_cash_vero_page_records_the_section(): void
    {
        $this->actingAs($this->user)
            ->get($this->cashVeroPageUrl())
            ->assertOk();

        $row = $this->lastLogRow();
        $this->assertSame($this->company->id, (int) $row->company_id);
        $this->assertSame(Log::generateLogMessage('enterSection', 'Contracts (Customer)'), $row->activity);
    }

    public function test_reopening_the_same_section_is_stored_again_only_after_the_window(): void
    {
        $url = $this->cashVeroPageUrl();

        $this->actingAs($this->user)->get($url)->assertOk();
        $this->actingAs($this->user)->get($url)->assertOk();
        $this->assertSame(1, $this->companyLogCount());

        DB::table('logs')->where('id', $this->lastLogRow()->id)->update([
            'created_at' => now()->subMinutes(Log::DEDUPE_MINUTES + 1)->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($this->user)->get($url)->assertOk();
        $this->assertSame(2, $this->companyLogCount());
    }

    public function test_ajax_and_json_requests_are_not_recorded(): void
    {
        $url = $this->cashVeroPageUrl();

        $this->actingAs($this->user)->get($url, ['X-Requested-With' => 'XMLHttpRequest']);
        $this->actingAs($this->user)->getJson($url);

        $this->assertSame(0, $this->companyLogCount());
    }

    public function test_the_company_home_page_is_recorded(): void
    {
        $this->actingAs($this->user)
            ->get(route('viewHomePage', ['company' => $this->company->id]))
            ->assertOk();

        $this->assertSame(Log::generateLogMessage('enterSection', 'Home Page'), $this->lastLogRow()->activity);
    }

    public function test_pages_outside_cash_vero_are_not_recorded(): void
    {
        Route::middleware(['web', 'auth'])
            ->get('/en/{company}/section-visit-probe/vero-page', fn () => response('<html><body>vero</body></html>'))
            ->name('section.visit.probe.vero');

        $this->actingAs($this->user)
            ->get('/en/'.$this->company->id.'/section-visit-probe/vero-page')
            ->assertOk();

        $this->assertSame(0, $this->companyLogCount());
    }

    public function test_cash_vero_downloads_and_json_responses_are_not_recorded(): void
    {
        Route::middleware(['web', 'auth', 'isCashManagement'])->group(function () {
            Route::get('/en/{company}/section-visit-probe/download', fn () => response()->streamDownload(fn () => print('x'), 'x.txt'))
                ->name('section.visit.probe.download');
            Route::get('/en/{company}/section-visit-probe/json', fn () => response()->json(['ok' => true]))
                ->name('section.visit.probe.json');
            Route::get('/en/{company}/section-visit-probe/page', fn () => response('<html><body>cash</body></html>'))
                ->name('section.visit.probe.page');
        });

        $base = '/en/'.$this->company->id.'/section-visit-probe/';
        $this->actingAs($this->user)->get($base.'download')->assertOk();
        $this->actingAs($this->user)->get($base.'json')->assertOk();
        $this->assertSame(0, $this->companyLogCount());

        $this->actingAs($this->user)->get($base.'page')->assertOk();
        $this->assertSame(1, $this->companyLogCount());
    }

    /**
     * صفحة العقود جوه الـ block بتاع isCashManagement وبتترسم في الـ tests
     * من غير بيانات (نفس الصفحة اللي AllContractsTabTest بيفتحها).
     */
    private function cashVeroPageUrl(): string
    {
        return route('contracts.index', ['company' => $this->company->id, 'type' => 'Customer']);
    }

    private function lastLogRow(): object
    {
        $row = DB::table('logs')->where('user_id', $this->user->id)->orderByDesc('id')->first();
        $this->assertNotNull($row, 'No logs row was written for the test user.');

        return $row;
    }

    private function companyLogCount(): int
    {
        return DB::table('logs')
            ->where('user_id', $this->user->id)
            ->where('company_id', $this->company->id)
            ->count();
    }
}
