<?php

namespace Tests\Feature\LetterOfGuarantee;

use App\Models\Company;
use App\Models\LetterOfGuaranteeIssuance;
use App\Models\User;
use App\Rules\BeneficiaryLinkedToOdooRule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * * خطاب الضمان 204 (الشركة 92) اتحفظ بمستفيد اتعمل من زرار Add New
 * * من غير odoo_id ، و بعد الـ commit أودو ضرب TypeError و قيود الـ
 * * cash cover و المصاريف و العمولة ماراحتش
 *
 * * الـ rule لازم توقف الحالة دي قبل الحفظ ، و بس لما الخطاب فعلا
 * * هيتبعت لأودو (نفس شروط handleIssuanceAndCommissionFeesForOdoo)
 */
class BeneficiaryLinkedToOdooRuleTest extends TestCase
{
    use DatabaseTransactions;

    private const INTEGRATION_START_DATE = '2025-11-01';

    protected function setUpTraits()
    {
        config(['database.connections.mysql.database' => env('SMOKE_DB', 'veroanalysis')]);
        DB::purge('mysql');

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userWithOdooCredentials());
    }

    /* ───────────── الحالة اللي حصلت على البرودكشن ───────────── */

    public function test_an_unlinked_beneficiary_is_rejected_when_the_lg_goes_to_odoo(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Offshore Shukeir Oil Company', null);

        $validator = $this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10');

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('Offshore Shukeir Oil Company', $validator->errors()->first('partner_id'));
        $this->assertStringContainsString('not linked to Odoo', $validator->errors()->first('partner_id'));
    }

    /* ───────────── الحالات اللي لازم تفضل شغالة زي ما هي ───────────── */

    public function test_a_linked_beneficiary_passes(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Linked', 555);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10')->fails());
    }

    public function test_an_opening_balance_lg_is_not_sent_to_odoo_so_it_passes(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::OPENING_BALANCE, '2026-11-10')->fails());
    }

    public function test_an_lg_issued_before_the_integration_start_date_passes(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2025-10-31')->fails());
    }

    public function test_the_integration_start_date_itself_is_inside_the_integration(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertTrue($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, self::INTEGRATION_START_DATE)->fails());
    }

    public function test_a_company_without_odoo_passes(): void
    {
        $company = $this->company(withOdoo: false);
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10')->fails());
    }

    public function test_a_user_without_odoo_credentials_is_not_sent_to_odoo_so_it_passes(): void
    {
        $this->actingAs(new User);
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10')->fails());
    }

    public function test_a_missing_issuance_date_is_left_to_its_own_rule(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, null)->fails());
    }

    public function test_an_unparseable_issuance_date_does_not_crash_the_rule(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Unlinked', null);

        $this->assertFalse($this->validate($company, $partnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, 'not-a-date')->fails());
    }

    /* ───────────── مستفيد مش تبع الشركة ───────────── */

    public function test_a_beneficiary_from_another_company_is_rejected(): void
    {
        $company = $this->company(withOdoo: false);
        $otherCompany = $this->company(withOdoo: false);
        $foreignPartnerId = $this->partner($otherCompany, 'Someone Else', 777);

        $validator = $this->validate($company, $foreignPartnerId, LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10');

        $this->assertTrue($validator->fails());
        $this->assertSame(__('The selected beneficiary is invalid.'), $validator->errors()->first('partner_id'));
    }

    public function test_an_array_instead_of_an_id_is_rejected_instead_of_crashing(): void
    {
        $company = $this->integratedCompany();
        $partnerId = $this->partner($company, 'Linked', 555);

        $validator = $this->validate($company, [$partnerId], LetterOfGuaranteeIssuance::NEW_ISSUANCE, '2026-11-10');

        $this->assertTrue($validator->fails());
        $this->assertSame(__('The selected beneficiary is invalid.'), $validator->errors()->first('partner_id'));
    }

    /* ───────────── helpers ───────────── */

    private function validate(Company $company, int|array $partnerId, string $categoryName, ?string $issuanceDate): \Illuminate\Validation\Validator
    {
        return Validator::make(
            ['partner_id' => $partnerId],
            ['partner_id' => [new BeneficiaryLinkedToOdooRule($company, $categoryName, $issuanceDate)]]
        );
    }

    private function userWithOdooCredentials(): User
    {
        $user = new User;
        $user->forceFill([
            'odoo_username' => 'lg-tester@example.test',
            'odoo_db_password' => 'secret-for-tests',
        ]);

        return $user;
    }

    private function integratedCompany(): Company
    {
        return $this->company(withOdoo: true);
    }

    private function company(bool $withOdoo): Company
    {
        $company = Company::create(['name' => ['en' => 'LG Beneficiary Odoo Co', 'ar' => 'LG Beneficiary Odoo Co']]);

        if ($withOdoo) {
            $company->forceFill([
                'odoo_db_url' => 'http://127.0.0.1:9',
                'odoo_db_name' => 'no_such_db',
                'odoo_integration_start_date' => self::INTEGRATION_START_DATE,
            ])->save();
        }

        return $company;
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
}
