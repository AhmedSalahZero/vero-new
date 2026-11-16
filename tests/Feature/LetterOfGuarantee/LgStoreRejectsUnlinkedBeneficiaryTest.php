<?php

namespace Tests\Feature\LetterOfGuarantee;

use App\Http\Requests\StoreLetterOfGuaranteeIssuanceRequest;
use App\Http\Requests\UpdateLetterOfGuaranteeIssuanceRequest;
use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\LetterOfGuaranteeIssuance;
use App\Models\User;
use App\Rules\BeneficiaryLinkedToOdooRule;
use App\Services\Api\OdooSync;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

/**
 * * قبل كده خطاب الضمان كان بيتحفظ بمستفيد مش مربوط بأودو ، و الفشل
 * * بيبان بعد الـ commit بس (OdooSync بيأجل أودو لبعد الحفظ)
 *
 * * دلوقتي الطلب لازم يترفض في الـ validation: مفيش خطاب بيتحفظ و مفيش
 * * أي نداء لأودو بيتسجل
 */
class LgStoreRejectsUnlinkedBeneficiaryTest extends TestCase
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
        OdooSync::discard();
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

    public function test_storing_an_lg_with_an_unlinked_beneficiary_is_rejected_before_anything_is_saved(): void
    {
        [$company, $actor] = $this->integratedCompanyAndUser();
        $partnerId = $this->partner($company, 'Offshore Shukeir Oil Company', null);

        $response = $this->actingAs($actor)->postJson(
            route('store.letter.of.guarantee.issuance', ['company' => $company->id, 'source' => LetterOfGuaranteeIssuance::LG_FACILITY]),
            $this->payload($partnerId)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['partner_id']);
        $this->assertStringContainsString('Offshore Shukeir Oil Company', $response->json('errors.partner_id.0'));

        $this->assertSame(0, DB::table('letter_of_guarantee_issuances')->where('company_id', $company->id)->count(), 'مفيش خطاب يتحفظ');
        $this->assertFalse(OdooSync::hasPending(), 'مفيش نداء لأودو يتسجل');
    }

    public function test_a_linked_beneficiary_is_not_blocked_by_the_new_rule(): void
    {
        [$company, $actor] = $this->integratedCompanyAndUser();
        $partnerId = $this->partner($company, 'Linked Customer', 555);

        /**
         * * lg_amount ناقص عمدا عشان الطلب يقف في الـ validation و
         * * مايدخلش الكنترولر — المهم إن partner_id مايبقاش فيه خطأ
         */
        $payload = $this->payload($partnerId);
        unset($payload['lg_amount']);

        $response = $this->actingAs($actor)->postJson(
            route('store.letter.of.guarantee.issuance', ['company' => $company->id, 'source' => LetterOfGuaranteeIssuance::LG_FACILITY]),
            $payload
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lg_amount']);
        $response->assertJsonMissingValidationErrors(['partner_id']);
    }

    public function test_the_update_request_inherits_the_rule(): void
    {
        foreach ([StoreLetterOfGuaranteeIssuanceRequest::class, UpdateLetterOfGuaranteeIssuanceRequest::class] as $requestClass) {
            $rules = (new $requestClass)->rules();

            $this->assertArrayHasKey('partner_id', $rules, $requestClass);
            $this->assertContains('required', $rules['partner_id'], $requestClass);
            $this->assertNotEmpty(
                array_filter($rules['partner_id'], fn ($rule) => $rule instanceof BeneficiaryLinkedToOdooRule),
                $requestClass.' لازم يفحص إن المستفيد مربوط بأودو'
            );
        }
    }

    /* ───────────── helpers ───────────── */

    private function payload(int $partnerId): array
    {
        return [
            'transaction_name' => 'Tender # - MAT - 030 /2026',
            'partner_id' => $partnerId,
            'lg_type' => 'bid-bond',
            'category_name' => LetterOfGuaranteeIssuance::NEW_ISSUANCE,
            'lg_fees_and_commission_account_id' => 1,
            'issuance_date' => '2026-11-10',
            'lg_amount' => 151800,
            'issuance_fees' => 82.90,
            'lg_commission_amount' => 303.60,
            'cash_cover_amount' => 7590,
        ];
    }

    /**
     * @return array{0: Company, 1: User}
     */
    private function integratedCompanyAndUser(): array
    {
        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LaravelLocalizationRedirectFilter::class,
            LaravelLocalizationViewPath::class,
        ]);

        $this->connectToSmokeDatabase();

        $company = Company::create(['name' => ['en' => 'LG Unlinked Beneficiary Co', 'ar' => 'LG Unlinked Beneficiary Co']]);
        $company->forceFill([
            'odoo_db_url' => 'http://127.0.0.1:9',
            'odoo_db_name' => 'no_such_db',
            'odoo_integration_start_date' => '2025-11-01',
        ])->save();
        CompanySystem::create([
            'company_id' => $company->id,
            'system_name' => CASH_VERO,
        ]);

        /** @var User $actor */
        $actor = User::create([
            'name' => 'LG Beneficiary Tester '.uniqid(),
            'email' => 'lg-beneficiary-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $company->id,
            'odoo_username' => 'lg-tester@example.test',
            'odoo_db_password' => 'secret-for-tests',
        ]);
        $actor->assignRole('user');
        $actor->companies()->attach($company->id);
        $actor->load('roles', 'permissions', 'companies');

        return [$company, $actor];
    }

    private function partner(Company $company, string $name, ?int $odooId): int
    {
        return (int) DB::table('partners')->insertGetId([
            'company_id' => $company->id,
            'name' => $name,
            'odoo_id' => $odooId,
            'is_customer' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
