<?php

namespace Tests\Feature\Balances;

use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use Tests\TestCase;

/**
 * * صفحة Invoice Report كانت بتعرض المبالغ من غير كسور عشرية —
 * * number_format() من غير البارامتر التاني بيقرّب لأقرب واحد صحيح ،
 * * فـ 4,349.75 كانت بتبان 4,350 و الفرق بيتوه
 *
 * * الاختبارات هنا مالهاش علاقة بالداتا اللي في القاعدة : بتبني الموديل
 * * في الميموري و بتقرا الـ accessors على طول ، عشان تفضل صحيحة حتى لو
 * * الداتا اتغيرت
 *
 * * و الأعمدة دي مشتركة بين فواتير العملاء و الموردين (تريت IsInvoice) ،
 * * فالاتنين متغطيين هنا
 */
class InvoiceReportMoneyFormatTest extends TestCase
{
    /** كل عمود فلوس في الصفحة : الخاصية في القاعدة => الميثود اللي بتعرضها */
    private const MONEY_COLUMNS = [
        'invoice_amount' => 'getInvoiceAmountFormatted',
        'total_withhold_amount' => 'getTotalWithholdAmountFormatted',
        'withhold_amount' => 'getWithholdAmountFormatted',
        'vat_amount' => 'getVatAmountFormatted',
        'total_deductions' => 'getTotalDeductionFormatted',
        'net_invoice_amount' => 'getNetInvoiceAmountFormatted',
        'net_balance' => 'getNetBalanceFormatted',
    ];

    public static function invoiceTypes(): array
    {
        return [
            'customer' => [CustomerInvoice::class, 'total_collected_amount'],
            'supplier' => [SupplierInvoice::class, 'total_paid_amount'],
        ];
    }

    /** @dataProvider invoiceTypes */
    public function test_every_money_column_keeps_two_decimals(string $class, string $collectedColumn): void
    {
        $columns = self::MONEY_COLUMNS + [$collectedColumn => 'getTotalCollectedOrPaidFormatted'];

        foreach ($columns as $column => $method) {
            $invoice = new $class;
            $invoice->forceFill([$column => 4349.75]);

            $this->assertSame('4,349.75', $invoice->{$method}(),
                class_basename($class)."::{$method}() لازم يعرض كسرين");
        }
    }

    /**
     * * التقريب لأقرب واحد صحيح كان بيخفي نص جنيه — ده الشكل اللي
     * * العميل شافه في الصفحة (4,350 بدل 4,349.50)
     *
     * @dataProvider invoiceTypes
     */
    public function test_a_half_unit_is_no_longer_rounded_away(string $class, string $collectedColumn): void
    {
        $invoice = new $class;
        $invoice->forceFill(['invoice_amount' => 4349.5]);

        $this->assertNotSame('4,350', $invoice->getInvoiceAmountFormatted());
        $this->assertSame('4,349.50', $invoice->getInvoiceAmountFormatted());
    }

    /** * الأصفار بتتكتب ، مش بتتشال */
    public function test_whole_numbers_still_show_both_decimals(): void
    {
        $invoice = new CustomerInvoice;
        $invoice->forceFill(['invoice_amount' => 313680]);

        $this->assertSame('313,680.00', $invoice->getInvoiceAmountFormatted());
    }

    /** * سعر الصرف مش عمود فلوس ، بيفضل بأربع خانات */
    public function test_the_exchange_rate_keeps_four_decimals(): void
    {
        $partial = file_get_contents(resource_path('views/admin/reports/invoice-report-td.blade.php'));

        $this->assertStringContainsString('number_format($invoice->getExchangeRate(),4)', $partial);
    }

    /**
     * * زرار الخصم لازم يبقى ظاهر على كل صف — الفاتورة المسددة لسه
     * * عندها خصومات متسجلة لازم تتعرض و تتعدل
     */
    public function test_the_deduct_button_is_not_hidden_for_settled_invoices(): void
    {
        $template = file_get_contents(resource_path('views/admin/reports/invoice-report.blade.php'));

        $this->assertStringContainsString("data-target=\"#add-new-customer-modal-{{ \$invoice->id }}\"", $template);
        $this->assertStringNotContainsString(
            "@if(!\$invoice->\$isCollectedOrPaid())\n"
            ."                                            <button type=\"button\" class=\"add-new btn btn-primary d-block\"",
            $template,
            'الزرار مالوش يتخبى على الفواتير المسددة');
    }

    /** * و الرابط اللي الزرار بيبعت عليه بيتبعت لكل صف ، مش للمفتوحة بس */
    public function test_the_controller_always_sends_the_deductions_url(): void
    {
        $template = file_get_contents(resource_path('views/admin/reports/invoice-report.blade.php'));

        $this->assertStringContainsString("route('update.invoice.deductions'", $template,
            'الفورمة لازم تتبني لكل صف ، مش للفواتير المفتوحة بس');
    }
}
