<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;

/**
 * The Limit details modal groups facilities by bank. A bank with more
 * than one contract gets a Contracts popup; a bank with one does not.
 * The popup total has to match that bank's row.
 */
class LgLimitContractsModalTest extends TestCase
{
    private function render(array $detailItems): string
    {
        return view('admin.dashboard.lg-lc-details', [
            'detailItems' => $detailItems,
            'modalId' => 'limitlg',
            'title' => 'Details',
            'currency' => 'EGP',
            'lgOrLcType' => 'lg',
        ])->render();
    }

    public function test_two_contracts_for_one_bank_open_a_popup_and_foot_to_the_bank_row(): void
    {
        $html = $this->render([
            [
                'id' => 1,
                'name' => 'QNB Fully Used',
                'contract_start_date' => '01-01-2025',
                'contract_end_date' => '31-12-2026',
                'limit' => 10000000,
                'outstanding_balance' => 10000000,
                'room' => 0,
                'cash_cover' => 0,
                'financial_institution_name' => 'QNB Alahli',
            ],
            [
                'id' => 2,
                'name' => 'QNB With Room',
                'contract_start_date' => '01-06-2026',
                'contract_end_date' => '31-05-2027',
                'limit' => 23000000,
                'outstanding_balance' => 5000000,
                'room' => 18000000,
                'cash_cover' => 1000000,
                'financial_institution_name' => 'QNB Alahli',
            ],
            [
                'id' => 3,
                'name' => 'CIB Only',
                'contract_start_date' => '01-01-2026',
                'contract_end_date' => '31-12-2026',
                'limit' => 5000000,
                'outstanding_balance' => 1000000,
                'room' => 4000000,
                'cash_cover' => 0,
                'financial_institution_name' => 'CIB',
            ],
        ]);

        $this->assertSame(1, substr_count($html, 'data-target="#limitlg-contracts-'));
        $this->assertStringContainsString('id="limitlg-contracts-0-EGP-lg"', $html);
        $this->assertStringNotContainsString('limitlg-contracts-1-EGP-lg', $html);
        $this->assertStringNotContainsString('CIB Only', $html);

        $this->assertStringContainsString('QNB Fully Used', $html);
        $this->assertStringContainsString('QNB With Room', $html);
        $this->assertStringContainsString('01-01-2025', $html);
        $this->assertStringContainsString('31-05-2027', $html);
        $this->assertStringContainsString('value="CIB"', $html);

        $this->assertSame(2, substr_count($html, number_format(33000000)));
        $this->assertStringContainsString(number_format(18000000), $html);
        $this->assertStringContainsString(number_format(38000000), $html);
    }
}
