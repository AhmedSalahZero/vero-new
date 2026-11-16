<?php

namespace Tests\Feature\LetterOfGuarantee;

use Tests\TestCase;

/**
 * * زرار "Add New" في فورمز خطاب الضمان كان بيعمل partner محلي من غير
 * * odoo_id (AddNewCustomerController) ، و ده اللي خلّى خطاب الضمان 204
 * * يتحفظ بمستفيد أودو مايعرفهوش
 *
 * * المستفيد الجديد بيتضاف من شاشة Customers أو من أودو ثم
 * * "Read Odoo Partners" — مش من جوه فورم الخطاب
 */
class LgFormsHaveNoAddNewPartnerTest extends TestCase
{
    private const FORMS = [
        'lg-facility-form',
        'against-cd-form',
        'against-td-form',
        'hundred-percentage-cash-cover-form',
    ];

    private const REMOVED_MARKERS = [
        "route('add.new.partner'",
        'add-new-customer-modal',
        'js-add-new-customer-if-not-exist',
        'new_customer_name',
    ];

    public function test_no_lg_form_can_create_a_partner(): void
    {
        $offenders = [];

        foreach (self::FORMS as $form) {
            $blade = $this->formSource($form);

            foreach (self::REMOVED_MARKERS as $marker) {
                if (str_contains($blade, $marker)) {
                    $offenders[] = "{$form}: {$marker}";
                }
            }
        }

        $this->assertSame([], $offenders, "LG forms must not create partners locally:\n  ".implode("\n  ", $offenders));
    }

    public function test_every_lg_form_still_lets_the_user_pick_a_beneficiary(): void
    {
        foreach (self::FORMS as $form) {
            $blade = $this->formSource($form);

            $this->assertStringContainsString('id="customer_name" name="partner_id"', $blade, $form);
            $this->assertStringContainsString("$('select#customer_name').empty().append(customerOptions)", $blade, $form);
        }
    }

    private function formSource(string $form): string
    {
        return file_get_contents(resource_path("views/reports/LetterOfGuaranteeIssuance/{$form}.blade.php"));
    }
}
