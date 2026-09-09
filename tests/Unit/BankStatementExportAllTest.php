<?php

namespace Tests\Unit;

use App\Exports\Statements\AbstractStatementExport;
use App\Exports\Statements\BankStatementExport;
use App\Http\Controllers\BankStatementController;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * * جدول كشف الحساب مقسّم صفحات من السيرفر (50 صف) ، فأزرار التصدير
 * * بتاعة DataTables كانت بتصدّر اللي على الشاشة بس. زرار Export All
 * * بيعيد نفس الاستعلام من غير تقسيم و بيطلّع الملف كامل
 */
class BankStatementExportAllTest extends TestCase
{
    private function controllerSource(): string
    {
        $source = file_get_contents((new ReflectionClass(BankStatementController::class))->getFileName());
        $source = preg_replace('#/\*.*?\*/#s', '', $source);

        return preg_replace('#\{\{--.*?--\}\}#s', '', $source);
    }

    private function viewSource(): string
    {
        return file_get_contents(resource_path('views/bank_statement_result.blade.php'));
    }

    private function invoke(string $method, array $args)
    {
        $reflection = new ReflectionMethod(BankStatementController::class, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs(new BankStatementController, $args);
    }

    /**
     * * getBankStatementReviewed بتقرا الأعمدة دي من الصف مباشرة ، و كل
     * * جداول الكشوف فيها فعلاً — فالصف الوهمي لازم يشيلها زي الحقيقي
     */
    private function statementRow(array $overrides = []): object
    {
        return (object) array_merge([
            'money_received_id' => 0,
            'money_payment_id' => 0,
            'cash_expense_id' => 0,
            'buy_or_sell_currency_id' => 0,
            'internal_money_transfer_id' => 0,
        ], $overrides);
    }

    /* ───────────── التوصيلات ───────────── */

    public function test_the_export_route_is_registered_as_a_read_only_get(): void
    {
        $route = Route::getRoutes()->getByName('export.bank.statement');

        $this->assertNotNull($route, 'الراوت مش متسجل');
        $this->assertSame(['GET', 'HEAD'], $route->methods());
        $this->assertStringContainsString('bank-statement-export', $route->uri());
    }

    public function test_the_controller_exposes_the_export_action(): void
    {
        $this->assertTrue(method_exists(BankStatementController::class, 'exportAll'));
    }

    public function test_the_button_is_on_the_screen(): void
    {
        $view = $this->viewSource();

        $this->assertStringContainsString("route('export.bank.statement'", $view, 'الزرار مش موجود');
        $this->assertStringContainsString("__('Export All')", $view);
    }

    /**
     * * الزرار لازم يبعت نفس الفلتر الحالي ، من غير page (عشان يجيب كل
     * * الصفحات) و من غير _token
     */
    public function test_the_button_keeps_the_current_filter_but_drops_the_page(): void
    {
        $view = $this->viewSource();

        $this->assertMatchesRegularExpression(
            "#route\('export\.bank\.statement',\s*array_merge\(request\(\)->except\(\['page','_token'\]\)#",
            $view,
            'لازم يمرّر الفلتر كله ما عدا page و _token'
        );
    }

    /* ───────────── مصدر واحد للصفوف ───────────── */

    /**
     * * أهم تست هنا : لو العرض و التصدير بنوا الاستعلام كل واحد لوحده
     * * ممكن يختلفوا في الترشيح أو الترتيب و الملف يطلع مش زي الشاشة
     */
    public function test_the_screen_and_the_export_read_from_the_same_builder(): void
    {
        $source = $this->controllerSource();

        $this->assertSame(
            2,
            substr_count($source, '$this->buildStatementRows($company, $request)'),
            'result() و exportAll() لازم يستخدموا نفس البنّاء'
        );
    }

    public function test_only_the_screen_paginates(): void
    {
        $source = $this->controllerSource();

        preg_match('/public function exportAll\(.*?\n    \}/s', $source, $matches);
        $this->assertNotEmpty($matches, 'مش لاقي exportAll');

        $this->assertStringNotContainsString('paginate', $matches[0], 'التصدير مالوش يقسّم صفحات');
    }

    public function test_an_empty_result_is_handled_not_exported(): void
    {
        $source = $this->controllerSource();

        foreach (['result', 'exportAll'] as $method) {
            preg_match('/public function '.$method.'\(.*?\n    \}/s', $source, $matches);

            $this->assertNotEmpty($matches, 'مش لاقي '.$method);
            $this->assertStringContainsString(
                "with('fail', __('No Data Found'))",
                $matches[0],
                $method.' لازم يتعامل مع نتيجة فاضية'
            );
        }
    }

    /* ───────────── الأعمدة ───────────── */

    public function test_the_headings_match_the_screen_for_a_current_account(): void
    {
        $headings = $this->invoke('exportHeadings', [[
            'isCurrentAccount' => true,
            'isAgainstCommercialPaper' => false,
            'isAgainstAssignmentOfContract' => false,
        ]]);

        $this->assertSame(
            [__('Date'), __('Beginning Balance'), __('Debit'), __('Credit'), __('End Balance'), __('Reviewed'), __('Comment')],
            $headings
        );
    }

    /**
     * * الحساب غير الجاري بيزود Limit و Room و Calculated Interest
     */
    public function test_a_non_current_account_gets_its_extra_columns(): void
    {
        $headings = $this->invoke('exportHeadings', [[
            'isCurrentAccount' => false,
            'isAgainstCommercialPaper' => false,
            'isAgainstAssignmentOfContract' => false,
        ]]);

        $this->assertContains(__('Limit'), $headings);
        $this->assertContains(__('Room'), $headings);
        $this->assertContains(__('Calculated Interest'), $headings);
        $this->assertNotContains(__('Actual Limit'), $headings, 'دي للأوراق التجارية و التنازل بس');
    }

    public function test_actual_limit_only_shows_for_the_two_account_types_that_have_it(): void
    {
        foreach ([['isAgainstCommercialPaper' => true, 'isAgainstAssignmentOfContract' => false],
                  ['isAgainstCommercialPaper' => false, 'isAgainstAssignmentOfContract' => true]] as $flags) {
            $headings = $this->invoke('exportHeadings', [array_merge(['isCurrentAccount' => false], $flags)]);

            $this->assertContains(__('Actual Limit'), $headings);
        }
    }

    public function test_the_actions_column_is_not_exported(): void
    {
        $headings = $this->invoke('exportHeadings', [[
            'isCurrentAccount' => true,
            'isAgainstCommercialPaper' => false,
            'isAgainstAssignmentOfContract' => false,
        ]]);

        $this->assertNotContains(__('Actions'), $headings, 'عمود الإجراءات مالوش معنى في ملف');
    }

    /* ───────────── الصفوف ───────────── */

    public function test_a_row_lines_up_with_its_headings(): void
    {
        $meta = ['isCurrentAccount' => true, 'isAgainstCommercialPaper' => false, 'isAgainstAssignmentOfContract' => false];

        $row = $this->statementRow([
            'date' => '2025-11-04',
            'beginning_balance' => '19905.82',
            'debit' => '943.25',
            'credit' => '0.00',
            'end_balance' => '20849.07',
            'comment_en' => 'Time Of Deposit 1930001000006119',
        ]);

        $rows = $this->invoke('exportRows', [collect([$row]), $meta]);
        $headings = $this->invoke('exportHeadings', [$meta]);

        $this->assertCount(1, $rows);
        $this->assertCount(count($headings), $rows[0], 'عدد الخلايا لازم يساوي عدد الأعمدة');

        $this->assertSame('04-11-2025', $rows[0][0], 'التاريخ بصيغة الشاشة');
        $this->assertSame(19905.82, $rows[0][1]);
        $this->assertSame(943.25, $rows[0][2]);
        $this->assertSame(0.0, $rows[0][3]);
        $this->assertSame(20849.07, $rows[0][4]);
        $this->assertSame('Time Of Deposit 1930001000006119', $rows[0][6]);
    }

    /**
     * * الأرقام لازم تتكتب كأرقام مش نصوص ، عشان صف الإجماليات في
     * * AbstractStatementExport يقدر يعمل =SUM() عليها
     */
    public function test_amounts_are_written_as_numbers(): void
    {
        $rows = $this->invoke('exportRows', [
            collect([$this->statementRow(['date' => '2026-07-02', 'beginning_balance' => '1', 'debit' => '2', 'credit' => '3', 'end_balance' => '4'])]),
            ['isCurrentAccount' => true, 'isAgainstCommercialPaper' => false, 'isAgainstAssignmentOfContract' => false],
        ]);

        foreach ([1, 2, 3, 4] as $index) {
            $this->assertIsFloat($rows[0][$index], 'العمود '.$index.' لازم يكون رقم');
        }
    }

    public function test_a_row_with_missing_amounts_does_not_break(): void
    {
        $rows = $this->invoke('exportRows', [
            collect([$this->statementRow(['date' => '2026-07-02'])]),
            ['isCurrentAccount' => true, 'isAgainstCommercialPaper' => false, 'isAgainstAssignmentOfContract' => false],
        ]);

        $this->assertSame(0.0, $rows[0][1]);
        $this->assertSame(0.0, $rows[0][2]);
    }

    /* ───────────── ملف الإكسل ───────────── */

    public function test_the_export_class_reuses_the_shared_statement_styling(): void
    {
        $this->assertInstanceOf(AbstractStatementExport::class, new BankStatementExport([], []));
    }

    public function test_the_export_carries_the_headings_and_rows_it_was_given(): void
    {
        $export = new BankStatementExport(['Date', 'Debit'], [['01-01-2026', 5.0]]);

        $this->assertSame(['Date', 'Debit'], $export->headings());
        $this->assertCount(1, $export->collection());
    }
}
