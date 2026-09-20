<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\Study;
use App\Models\PropertyManagement\Study as PropertyManagementStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * * صفحة الميزانية العمومية للدراسة بتفتح فعلا.
 *
 * * النسخة القديمة كانت بتنده route('non_banking_services.balance_sheet.view')
 * * و الاسم ده مش مسجّل — الاسم الحقيقي non.banking.balance.sheet.result —
 * * و كانت بتثبّت رقم دراسة (109) بالإيد. فالتست كان بيفشل من غير ما يوصل
 * * للكونترولر أصلا ، يعني مكانش بيغطي حاجة.
 *
 * * و ده كان الراوت الوحيد اللي السويت كلها بتحاول تنفّذه.
 */
class BalanceSheetTest extends TestCase
{
    use DatabaseTransactions;

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
        config(['database.connections.mysql.database' => env('SMOKE_DB', 'veroanalysisb_dev')]);
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
    }

    public function test_the_balance_sheet_page_renders(): void
    {
        /** أي دراسة موجودة فعلا ، مش رقم متثبّت بالإيد */
        $study = Study::query()->whereNotNull('company_id')->first();

        if (! $study) {
            $this->markTestSkipped('Development database has no non-banking study to render.');
        }

        $company = $study->company;

        $actor = User::query()
            ->whereNull('deleted_at')
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->first();

        if (! $actor) {
            $this->markTestSkipped('No user belongs to the study company.');
        }

        $response = $this->actingAs($actor)->get(route('non.banking.balance.sheet.result', [
            'company' => $company->id,
            'study' => $study->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('non_banking_services.income-statement.cash-flow');
    }

    /**
     * * نفس الباج بالظبط كان في PropertyManagements\BalanceSheetController —
     * * نفس الفيو ، نفس المتغير الناقص.
     *
     * * مفيش في قاعدة التطوير شركة عندها دراسة إدارة أملاك **و** النظام
     * * مفعّل عندها ، فالراوت بيرجّع 403 عن حق. بنجرّب لو لقينا توليفة ،
     * * و بنتخطّى بسبب واضح لو لأ.
     */
    public function test_the_property_management_balance_sheet_page_renders(): void
    {
        $study = PropertyManagementStudy::query()
            ->whereNotNull('company_id')
            ->whereIn('company_id', DB::table('company_systems')
                ->where('system_name', 'property-management')
                ->pluck('company_id'))
            ->first();

        if (! $study) {
            $this->markTestSkipped('No company has both a property-management study and the property-management system.');
        }

        $company = $study->company;

        $actor = User::query()
            ->whereNull('deleted_at')
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->first();

        if (! $actor) {
            $this->markTestSkipped('No user belongs to the study company.');
        }

        $response = $this->actingAs($actor)->get(route('property.management.balance.sheet.result', [
            'company' => $company->id,
            'study' => $study->id,
        ]));

        $response->assertOk();
        $response->assertViewIs('property_managements.income-statement.cash-flow');
    }

    /**
     * * الفيو المشترك بيستخدم $company ، فأي كونترولر بيعرضه لازم يبعتها.
     * * حارس بنيوي لأن نسخة إدارة الأملاك مالهاش بيانات نقدر ننفّذها.
     */
    public function test_both_balance_sheet_controllers_pass_the_company_to_the_view(): void
    {
        foreach ([
            'app/Http/Controllers/NonBankingServices/BalanceSheetController.php',
            'app/Http/Controllers/PropertyManagements/BalanceSheetController.php',
        ] as $path) {
            $source = file_get_contents(base_path($path));

            $this->assertStringContainsString(
                "array_merge(\$study->getBalanceSheetViewVars(), ['company' => \$company])",
                $source,
                $path.' renders a view that uses $company on its export link — without it the page is a 500.'
            );
        }
    }
}
