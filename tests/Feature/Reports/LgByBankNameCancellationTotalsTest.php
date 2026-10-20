<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;

/**
 * Cancelled rows on LG By Bank Name keep their renewal date and show the
 * cancellation date under it. The total row is two rows, summed across
 * the whole result, not the page on screen.
 */
class LgByBankNameCancellationTotalsTest extends TestCase
{
    public function test_a_cancelled_row_keeps_the_renewal_date_and_shows_the_cancellation_date(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/LgByBankNameReportController.php'));
        $blade = file_get_contents(resource_path('views/lg_by_bank_name_result.blade.php'));

        $this->assertStringNotContainsString("then 'cancelled' else", $controller);
        $this->assertStringContainsString("DATE_FORMAT(letter_of_guarantee_issuances.renewal_date,\\'%d-%m-%Y\\') as renewal_date", $controller);
        $this->assertStringContainsString('letter_of_guarantee_issuances.cancellation_date', $controller);
        $this->assertStringContainsString("type = \\'for-cancellation\\'", $controller);
        $this->assertStringContainsString("{{ \$modelAsStdClass->renewal_date }}", $blade);
        $this->assertStringContainsString("\$modelAsStdClass->status === 'cancelled'", $blade);
        $this->assertStringContainsString("__('cancelled (:date)'", $blade);
    }

    public function test_the_two_totals_are_summed_before_the_page_is_cut(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/LgByBankNameReportController.php'));
        $blade = file_get_contents(resource_path('views/lg_by_bank_name_result.blade.php'));

        $without = strpos($controller, 'lgAmountWithoutCancellation');
        $paginate = strpos($controller, '$this->paginate($results,50)');

        $this->assertNotFalse($without);
        $this->assertNotFalse($paginate);
        $this->assertLessThan($paginate, $without);
        $this->assertStringContainsString("where('status', '!=', 'cancelled')", $controller);
        $this->assertStringContainsString("__('Total (without cancellation)')", $blade);
        $this->assertStringContainsString("__('Total (with cancellation)')", $blade);
        $withoutRow = strpos($blade, "__('Total (without cancellation)')");
        $withRow = strpos($blade, "__('Total (with cancellation)')");
        $this->assertLessThan($withRow, $withoutRow);
    }
}
