<?php

namespace Tests\Feature\Navigation;

use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * تابات الـ nav bar لازم تتبني على شركة اليوزر نفسه وصلاحياته.
 *
 * الباج: الصفحات اللي مافيهاش رقم شركة في اللينك (زي /en/profile) كانت
 * بتخلي الـ singleton بتاع Company يقع على Company::first() ، يعني شركة تانية
 * خالص ، فا يوزر عنده cash-vero بس كان بيشوف
 * Dashboard / Data Gathering / Analysis Report / Labeling Items
 * بتاعة شركة مالهاش اي علاقة بيه.
 *
 * والباج التاني: البارنتس اللي 'show' بتاعها متكتب true على طول (زي
 * Cash & Bank Accounts و Customer Sections) كانت بتفضل ظاهرة حتى لو كل
 * اللينكات اللي جواها متخفية بالصلاحيات.
 */
class HeaderMenuVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $actor;

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

        parent::tearDown();
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

        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ]);

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Development database not reachable: '.$e->getMessage());
        }

        /*
         * شركة cash-vero بس. المهم انها مش أول شركة في الجدول ، علشان
         * الفرق بين شركة اليوزر و Company::first() يبان.
         */
        $this->company = Company::create(['name' => ['en' => 'Nav Menu Test Co', 'ar' => 'Nav Menu Test Co']]);
        CompanySystem::create([
            'company_id' => $this->company->id,
            'system_name' => CASH_VERO,
        ]);

        $first = Company::query()->orderBy('id')->first();
        if (! $first || $first->id === $this->company->id) {
            $this->markTestSkipped('Development database has no other company to fall back to.');
        }
        if (! in_array(VERO, $first->getSystemsNames(), true)) {
            $this->markTestSkipped('The fallback company carries no vero system, nothing to leak.');
        }

        /*
         * زي اليوزرات الحقيقية بالظبط: الصلاحيات مش متقصّة على انظمة الشركة ،
         * فا اليوزر ماسك صلاحيات vero / labeling برضه. اللي بيقفل التابات دي
         * هو ان شركته مافيهاش الانظمة دي — مش الصلاحية.
         */
        $this->actor = $this->makeUser([
            'view home',
            'view sales dashboard',
            'view breakdown dashboard',
            'view income statement dashboard',
            'upload sales gathering data',
            'view sales gathering data',
            'view sales report',
            'view labeling items',
            'view income statement planning',
        ]);
    }

    /** @param string[] $permissions */
    private function makeUser(array $permissions): User
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Nav Menu Test '.uniqid(),
            'email' => 'nav-menu-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $this->company->id,
        ]);

        // من غير رول الـ User::isSuperAdmin() بيولع ، ولازم يكون مش super-admin
        // علشان الصلاحيات تتفحص فعلا.
        $user->assignRole('user');
        $user->givePermissionTo($permissions);
        $user->companies()->attach($this->company->id);
        $user->load('roles', 'permissions', 'companies');

        return $user;
    }

    /** @return string[] عناوين التابات الرئيسية في الـ nav bar */
    private function topLevelTabs(string $url): array
    {
        $response = $this->actingAs($this->actor)->get($url);
        $response->assertOk();

        $html = $response->getContent();
        $start = strpos($html, 'id="kt_header_menu"');
        $this->assertNotFalse($start, "No header menu rendered on {$url}");

        $menu = substr($html, $start, 400000);
        preg_match_all(
            '#kt-menu__item--rel"[^>]*>.*?kt-menu__link-text">(.*?)(?:<span|<i|</span>)#s',
            $menu,
            $matches
        );

        return array_map(
            fn ($title) => html_entity_decode(trim(preg_replace('/\s+/', ' ', strip_tags($title)))),
            $matches[1]
        );
    }

    /** @return string[] كل عناوين الـ nav bar على كل المستويات */
    private function allMenuTitles(string $url): array
    {
        $response = $this->actingAs($this->actor)->get($url);
        $response->assertOk();

        $html = $response->getContent();
        $start = strpos($html, 'id="kt_header_menu"');
        $this->assertNotFalse($start, "No header menu rendered on {$url}");

        $menu = substr($html, $start, 400000);
        preg_match_all('#kt-menu__link-text">(.*?)</span>#s', $menu, $matches);

        return array_map(
            fn ($title) => html_entity_decode(trim(preg_replace('/\s+/', ' ', strip_tags($title)))),
            $matches[1]
        );
    }

    /** @test */
    public function it_does_not_leak_another_companys_tabs_on_pages_without_a_company_in_the_url(): void
    {
        $tabs = $this->topLevelTabs(route('profile.edit'));

        $this->assertNotContains('Dashboard', $tabs);
        $this->assertNotContains('Data Gathering', $tabs);
        $this->assertNotContains('Analysis Report', $tabs);
        $this->assertNotContains('Income Statement Planning', $tabs);
        $this->assertNotContains('Labeling Items', $tabs);
    }

    /** @test */
    public function it_renders_the_same_tabs_on_the_home_page_and_on_a_company_less_page(): void
    {
        $this->assertSame(
            $this->topLevelTabs(route('home')),
            $this->topLevelTabs(route('profile.edit'))
        );
    }

    /** @test */
    public function it_hides_section_headers_whose_links_are_all_denied(): void
    {
        /*
         * يوزر مسموح له بلينك واحد بس جوه Cash Management. القسم اللي فيه
         * اللينك ده لازم يفضل ظاهر ، وباقي الاقسام (اللي 'show' بتاعها متكتب
         * true على طول) لازم تختفي بدل ما تفتح دروب داون فاضية.
         */
        $this->actor = $this->makeUser(['view home', 'view financial institutions']);

        $titles = $this->allMenuTitles(route('home'));

        $this->assertContains('Home', $titles);
        $this->assertContains('Cash & Bank Accounts', $titles);
        $this->assertContains('Financial Institutions', $titles);

        $this->assertNotContains('Customer Sections', $titles);
        $this->assertNotContains('Supplier Sections', $titles);
        $this->assertNotContains('Money Transactions', $titles);
        $this->assertNotContains('LG & LC Issuance', $titles);
    }
}
