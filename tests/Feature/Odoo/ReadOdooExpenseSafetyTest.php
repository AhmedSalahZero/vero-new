<?php

namespace Tests\Feature\Odoo;

use App\Http\Controllers\ReadOdooExpense;
use App\Models\CashExpense;
use App\Models\Company;
use App\Models\OdooExpense;
use App\Models\User;
use App\Services\Api\ExpensePayment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * * استيراد مصروفات أودو مايمسحش مصروفاتنا على رد ناقص.
 *
 * * الكود القديم :
 * *
 * *     $odooExpenses = fetchData('hr.expense.sheet', ..., فلتر بالفترة);
 * *     $oldIds       = كل صفوفنا (من غير أي فلتر تاريخ);
 * *     $idsToRemove  = array_diff($oldIds, array_column($odooExpenses,'id'));
 * *     foreach ($idsToRemove as $odooId) { destroy($cashExpense); ...delete(); }
 * *
 * * فيه مشكلتين مستقلتين هنا ، و الاتنين بيمسحوا فعلا :
 * *
 * *  ١) أودو واقع → fetchData بترجّع fault array (truthy) → array_column
 * *     بترجّع [] → idsToRemove = كل حاجة عندنا.
 * *
 * *  ٢) المستخدم اختار فترة ضيقة → النتيجة مافيهاش الصفوف القديمة → نفس
 * *     النتيجة : المسح. حتى لو أودو ردّ صح تماما.
 * *
 * * دي نفس فئة الباج بتاعة syncDeletedInvoices — رد جزئي اتعامل معاه على
 * * إنه الحقيقة الكاملة.
 */
class ReadOdooExpenseSafetyTest extends TestCase
{
    use DatabaseTransactions;

    private Company $company;

    private User $actor;

    private static ?object $fake = null;

    public static function fake(): object
    {
        return self::$fake;
    }

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
        self::$fake = null;

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

        $companyId = DB::table('company_systems')->where('system_name', CASH_VERO)->value('company_id');

        if (! $companyId) {
            $this->markTestSkipped('No company in this database has the cash-vero system.');
        }

        $this->company = Company::findOrFail($companyId);
        $this->actor = $this->makeUser();

        self::$fake = $this->makeFakeExpenseService();

        $this->app->bind(ReadOdooExpense::class, fn () => new class extends ReadOdooExpense
        {
            protected function expenseService(Company $company): ExpensePayment
            {
                return ReadOdooExpenseSafetyTest::fake();
            }
        });
    }

    private function makeUser(): User
    {
        /** @var User $user */
        $user = User::create([
            'name' => 'Read Odoo Expense Safety '.uniqid(),
            'email' => 'read-odoo-expense-'.uniqid().'@example.test',
            'password' => bcrypt('secret-for-tests'),
            'company_id' => $this->company->id,
        ]);

        $user->assignRole('company-admin');

        foreach (['odoo_integration.sync', 'odoo_integration.update', 'odoo_integration.view'] as $key) {
            $permission = Permission::where('name', $key)->first();

            if ($permission) {
                $user->givePermissionTo($permission);
            }
        }

        $user->companies()->attach($this->company->id);
        $user->load('roles', 'permissions', 'companies');

        return $user;
    }

    private function url(): string
    {
        return route('read-odoo-expenses', ['company' => $this->company->id]);
    }

    private function seedExpense(int $odooId): void
    {
        OdooExpense::create([
            'odoo_id' => $odooId,
            'company_id' => $this->company->id,
            'name' => 'Seeded '.$odooId,
            'odoo_currency_id' => '1',
            'state' => 'approve',
            'payment_state' => 'not_paid',
            'odoo_employee_id' => 1,
            'total_amount' => 100,
            'account_move_ids' => 1,
            'journal_id' => 1,
            'payment_method_line_id' => 1,
            'payment_mode' => 'own_account',
        ]);
    }

    /** @return list<int> */
    private function storedOdooIds(): array
    {
        $ids = OdooExpense::where('company_id', $this->company->id)
            ->whereNotNull('odoo_id')
            ->pluck('odoo_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        sort($ids);

        return $ids;
    }

    private function submit(array $payload = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->actor)->post($this->url(), $payload + [
            'odoo_start_date' => '2026-09-01',
            'odoo_end_date' => '2026-09-30',
        ]);
    }

    // ────────────────────────────────────────────────────────────

    /** المشكلة الأولى : أودو رجّع fault */
    public function test_a_ripcord_fault_deletes_nothing(): void
    {
        $this->seedExpense(910001);
        $this->seedExpense(910002);
        $before = $this->storedOdooIds();

        self::$fake->sheets = ['faultCode' => 2, 'faultString' => 'AccessDenied'];

        $response = $this->submit();

        $response->assertRedirect();
        $response->assertSessionHas('fail');
        $this->assertSame($before, $this->storedOdooIds(), 'رد خطأ من أودو مش سبب إننا نمسح');
    }

    /** أودو رجّع null (انقطاع نقل) */
    public function test_a_null_answer_deletes_nothing(): void
    {
        $this->seedExpense(910003);
        $before = $this->storedOdooIds();

        self::$fake->sheets = null;

        $this->submit()->assertRedirect();

        $this->assertSame($before, $this->storedOdooIds());
    }

    /**
     * * المشكلة التانية : أودو ردّ صح تماما ، بس الفترة اللي المستخدم كتبها
     * * مافيهاش الصفوف القديمة. القديم كان بيمسحهم — والصح إننا نسأل أودو
     * * عنهم بالتحديد و نلاقيهم لسه approve / not_paid.
     */
    public function test_a_narrow_date_window_does_not_delete_rows_outside_it(): void
    {
        $this->seedExpense(910004);
        $this->seedExpense(910005);
        $before = $this->storedOdooIds();

        /** مفيش مصروفات في الفترة المطلوبة */
        self::$fake->sheets = [];
        /** بس الاتنين بتوعنا لسه معلّقين في أودو */
        self::$fake->stillPending = [910004, 910005];

        $this->submit()->assertRedirect();

        $this->assertSame($before, $this->storedOdooIds(),
            'الصفوف بره الفترة لسه معلّقة في أودو — مايتمسحوش عشان الفترة ضيقة');
    }

    /** الميزة نفسها لازم تفضل شغالة : اللي اتدفع فعلا بيتشال */
    public function test_an_expense_that_is_no_longer_pending_is_removed(): void
    {
        $this->seedExpense(910006);
        $this->seedExpense(910007);

        self::$fake->sheets = [];
        /** 910006 بس لسه معلّق ، يعني 910007 اتدفع */
        self::$fake->stillPending = [910006];

        $this->submit()->assertRedirect();

        $this->assertSame([910006], $this->storedOdooIds(),
            'اللي أودو قال إنه خلاص مش معلّق لازم يتشال — الميزة مش مقفولة');
    }

    /** لو سؤال الوجود نفسه فشل ، مانمسحش */
    public function test_a_failed_existence_check_deletes_nothing(): void
    {
        $this->seedExpense(910008);
        $before = $this->storedOdooIds();

        self::$fake->sheets = [];
        self::$fake->stillPendingRaw = ['faultCode' => 2, 'faultString' => 'timeout'];

        $response = $this->submit();

        $response->assertRedirect();
        $response->assertSessionHas('fail');
        $this->assertSame($before, $this->storedOdooIds());
    }

    /**
     * * قراءة اليومية بتحصل جوه الحلقة ، بعد المسح. لو ردّت فاضي الكود
     * * القديم كان بيعمل [0] عليها و يقع بـ Undefined array key 0 — و
     * * الاستيراد يقف في نُصّه.
     */
    public function test_an_unreadable_journal_skips_that_expense_instead_of_crashing(): void
    {
        self::$fake->sheets = [[
            'id' => 910009,
            'write_date' => '2026-09-15',
            'currency_id' => [1, 'EGP'],
            'expense_line_ids' => [],
            'name' => 'EXP/2026/0009',
            'state' => 'approve',
            'payment_state' => 'not_paid',
            'employee_id' => [55, 'Test Employee'],
            'total_amount' => 250,
            'account_move_ids' => [77],
            'journal_id' => [3, 'Bank'],
            'payment_method_line_id' => [9],
            'payment_mode' => [1, 'own_account'],
        ]];
        self::$fake->journal = [];

        $response = $this->submit();

        $response->assertRedirect();
        $this->assertNotContains(910009, $this->storedOdooIds(),
            'اليومية ماتقريتش ، فالصف اتخطّى — من غير ما الطلب يقع');
    }

    /**
     * * بديل الخدمة : مابيتصلش بأودو ، و بيرد حسب سيناريو التست
     */
    private function makeFakeExpenseService(): object
    {
        return new class extends ExpensePayment
        {
            public $sheets = [];

            /** @var list<int>|null */
            public $stillPending = [];

            /** رد خام لسؤال الوجود ، بيسبق $stillPending */
            public $stillPendingRaw = null;

            public $journal = [['id' => 3, 'type' => 'bank', 'default_account_id' => [1, '1010']]];

            public array $calls = [];

            public function __construct() {}

            public function fetchData(string $modelName, array $fields = [], array $filters = [[]])
            {
                $this->calls[] = $modelName;
                $domain = $filters[0] ?? [];
                $isExistenceCheck = collect($domain)->contains(fn ($c) => ($c[0] ?? null) === 'id' && ($c[1] ?? null) === 'in');

                if ($modelName === 'account.journal') {
                    return $this->journal;
                }

                if ($isExistenceCheck) {
                    if (! is_null($this->stillPendingRaw)) {
                        return $this->stillPendingRaw;
                    }

                    return array_map(fn ($id) => ['id' => $id], $this->stillPending ?? []);
                }

                return $this->sheets;
            }
        };
    }
}
