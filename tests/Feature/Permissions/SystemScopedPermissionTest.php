<?php

namespace Tests\Feature\Permissions;

use App\Models\Company;
use App\Models\CompanySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * can() لازم يبص على انظمة الشركة كمان ، مش على صف الصلاحية بس.
 *
 * الصلاحيات متخزنة في model_has_permissions من غير اي ربط بالانظمة ، والربط
 * موجود بس في HAuth::getPermissions وبيتطبق وقت الاسناد. فا يوزر شركته
 * cash-vero بس ، وواخد الكتالوج كله bulk ، كان
 * can('upload sales gathering data')
 * بترجع له true.
 */
class SystemScopedPermissionTest extends TestCase
{
    use DatabaseTransactions;

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
    }

    /** @param string[] $systems */
    private function makeCompany(array $systems): Company
    {
        $company = Company::create(['name' => ['en' => 'Systems Test Co', 'ar' => 'Systems Test Co']]);
        foreach ($systems as $system) {
            CompanySystem::create(['company_id' => $company->id, 'system_name' => $system]);
        }

        return $company->fresh('systems');
    }

    /** @param string[] $permissions */
    private function makeUser(Company $company, array $permissions, string $role = 'user'): User
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Systems Test '.uniqid(),
            'email' => 'systems-test-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $company->id,
        ]);
        $user->assignRole($role);
        $user->givePermissionTo($permissions);
        $user->companies()->attach($company->id);
        $user->load('roles', 'permissions', 'companies');

        // الـ singleton بيتحفظ لكل ريكوست ، وفي التست جوه نفس الاب.
        app()->forgetInstance(Company::class);
        $this->actingAs($user);

        return $user;
    }

    /** @test */
    public function it_denies_a_permission_that_belongs_to_a_system_the_company_does_not_have(): void
    {
        $company = $this->makeCompany([CASH_VERO]);
        $user = $this->makeUser($company, [
            'view home',
            'view financial institutions',   // cash-vero
            'view sales dashboard',          // vero
            'upload sales gathering data',   // vero
        ]);

        $this->assertTrue($user->can('view home'));
        $this->assertTrue($user->can('view financial institutions'));

        $this->assertFalse($user->can('view sales dashboard'));
        $this->assertFalse($user->can('upload sales gathering data'));
    }

    /** @test */
    public function it_allows_the_same_permission_once_the_company_carries_the_system(): void
    {
        $company = $this->makeCompany([CASH_VERO, VERO]);
        $user = $this->makeUser($company, ['view home', 'view sales dashboard']);

        $this->assertTrue($user->can('view sales dashboard'));
    }

    /** @test */
    public function it_leaves_super_admins_alone(): void
    {
        $company = $this->makeCompany([CASH_VERO]);
        $user = $this->makeUser($company, ['view home', 'view sales dashboard'], 'super-admin');

        $this->assertTrue($user->can('view sales dashboard'));
    }

    /**
     * @test
     *
     * non-banking-service و property-management مالهمش ولا صلاحية في
     * HAuth::getPermissions ، فا القاعدة دي لو اتطبقت عليهم هتقفلهم بالكامل.
     */
    public function it_does_not_touch_companies_whose_systems_are_not_in_the_permission_catalogue(): void
    {
        $company = $this->makeCompany([NON_BANKING_SERVICE]);
        $user = $this->makeUser($company, ['view home', 'view sales dashboard', 'view financial institutions']);

        $this->assertTrue($user->can('view home'));
        $this->assertTrue($user->can('view sales dashboard'));
        $this->assertTrue($user->can('view financial institutions'));
    }
}
