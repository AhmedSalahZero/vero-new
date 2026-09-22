<?php

namespace Tests\Feature\Reports;

use App\Enums\LgTypes;
use Tests\TestCase;

/**
 * * الجزء المرئي من بوب اب التفاصيل: الايقونة و الـ modal
 *
 * * الشرط في البليد كان متثبّت على اسم صف واحد ، فالصفين التانيين
 * * ("Issued LG Cash Cover" و "LGs Commission & Fees") ما كانش ليهم
 * * ايقونة اصلا حتى لما البيانات بتاعتهم موجودة.
 *
 * * و لما التلاتة بقوا بيستخدموا نفس البوب اب ، الـ id بقى لازم يشيل
 * * اسم الصف الأب : نفس نوع الخطاب بيتكرر تحت التلاتة ، و بوتستراب
 * * بيفتح اول عنصر بالـ id اللي في data-target.
 */
class LgBreakdownPopupViewTest extends TestCase
{
    private const WEEK_KEY = '09-2026';

    /** @return array<string, mixed> */
    private function render(string $rowName, float $cellValue = 1500, bool $withBreakdown = true): string
    {
        $lgTypeLabel = LgTypes::getAll()[LgTypes::FINAL_LGS];

        $letterOfGuaranteeModelData = $withBreakdown ? [
            $rowName => [
                $lgTypeLabel => [
                    'weeks' => [
                        self::WEEK_KEY => [
                            ['amount' => 1000.0, 'lg_code' => 'LG-A', 'name' => 'Customer A'],
                            ['amount' => 500.0, 'lg_code' => 'LG-B', 'name' => 'Customer B'],
                        ],
                    ],
                ],
            ],
        ] : [];

        return view('admin.reports.cash-flow-sub-row', [
            'result' => [
                'cash_expenses' => [
                    $rowName => [
                        $lgTypeLabel => ['weeks' => [self::WEEK_KEY => $cellValue]],
                    ],
                ],
            ],
            'mainReportKey' => 'cash_expenses',
            'parentKeyName' => $rowName,
            'currentSubRowKeyName' => $lgTypeLabel,
            'customerName' => $rowName,
            'weeks' => [self::WEEK_KEY => 9],
            'dates' => [self::WEEK_KEY => ['start_date' => '2026-09-01', 'end_date' => '2026-09-30']],
            'rowIndex' => 1,
            'customerDueInvoices' => [],
            'supplierDueInvoices' => [],
            'pastDueLoanInstallments' => [],
            'letterOfGuaranteeModelData' => $letterOfGuaranteeModelData,
            'incomingTransferModelData' => [],
        ])->render();
    }

    /** @return array<int, string> */
    private function modalIds(string $html): array
    {
        preg_match_all('/id="lg-breakdown-modal-([^"]+)"/', $html, $matches);

        return $matches[1];
    }

    public function test_the_three_guarantee_rows_all_show_the_info_icon(): void
    {
        foreach ([
            __('Cancelled LGs Cash Cover'),
            __('Issued LG Cash Cover'),
            __('LGs Commission & Fees'),
        ] as $rowName) {
            $html = $this->render($rowName);

            $this->assertStringContainsString('flaticon2-information', $html, $rowName.' مالهاش ايقونة تفاصيل');
            $this->assertCount(1, $this->modalIds($html), $rowName.' مالهاش بوب اب');
        }
    }

    public function test_the_popup_lists_every_guarantee_with_its_total(): void
    {
        $html = $this->render(__('LGs Commission & Fees'));

        $this->assertStringContainsString('LG-A', $html);
        $this->assertStringContainsString('Customer A', $html);
        $this->assertStringContainsString('LG-B', $html);
        $this->assertStringContainsString('Customer B', $html);
        $this->assertStringContainsString('1,500.00', $html, 'سطر الاجمالي في البوب اب');
    }

    /**
     * * نفس نوع الخطاب و نفس الشهر تحت تلات صفوف مختلفين — لازم تلات
     * * id مختلفين ، و الا ايقونة الفيز هتفتح بوب اب الملغي
     */
    public function test_each_row_gets_its_own_modal_id(): void
    {
        $ids = [];
        foreach ([
            __('Cancelled LGs Cash Cover'),
            __('Issued LG Cash Cover'),
            __('LGs Commission & Fees'),
        ] as $rowName) {
            $ids[] = $this->modalIds($this->render($rowName))[0];
        }

        $this->assertCount(3, array_unique($ids), 'الـ id بيتكرر بين الصفوف فبوتستراب هيفتح الغلط');
    }

    /**
     * * "&" في "LGs Commission & Fees" كانت هتعدي للـ id و تكسر الـ CSS
     * * selector اللي data-target بيدوّر بيه
     */
    public function test_the_modal_id_is_a_safe_css_selector(): void
    {
        $id = $this->modalIds($this->render(__('LGs Commission & Fees')))[0];

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $id, 'الـ id فيه حروف بتكسر السيليكتور');
    }

    public function test_a_zero_cell_has_no_icon(): void
    {
        $html = $this->render(__('LGs Commission & Fees'), 0);

        $this->assertStringNotContainsString('flaticon2-information', $html, 'خلية بصفر مالهاش تفاصيل تتعرض');
    }

    public function test_the_other_rows_are_left_alone(): void
    {
        $html = $this->render(__('Bank Deposits'));

        $this->assertStringNotContainsString('lg-breakdown-modal', $html);
    }
}
