<?php

namespace Tests\Unit;

use App\Support\SectionLabel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * اسم القسم اللي بيتسجل في logs بيتعمل من اسم الـ route.
 */
class SectionLabelTest extends TestCase
{
    public static function routes(): array
    {
        return [
            'view prefix is dropped' => ['view.financial.institutions', [], 'Financial Institutions'],
            'index suffix is dropped' => ['customers.index', [], 'Customers'],
            'create becomes a suffix' => ['customers.create', [], 'Customers - Create'],
            'edit prefix becomes a suffix' => ['edit.financial.institutions.account', [], 'Financial Institutions Account - Edit'],
            'camel case route name' => ['viewHomePage', [], 'Home Page'],
            'no action word' => ['financial.institution.add.account', [], 'Financial Institution Add Account'],
            'word parameters are kept' => ['view.balances', ['company' => '92', 'modelType' => 'CustomerInvoice'], 'Balances (Customer Invoice)'],
            'ids are ignored' => ['view.uploading', ['company' => '92', 'model' => 'SupplierInvoice', 'loanId' => '15'], 'Uploading (Supplier Invoice)'],
            'dates are ignored' => ['view.bank.statement', ['date' => '2026-01-01'], 'Bank Statement'],
            'parameter and action together' => ['contracts.create', ['type' => 'Customer'], 'Contracts (Customer) - Create'],
        ];
    }

    #[DataProvider('routes')]
    public function test_it_builds_a_readable_label(string $routeName, array $parameters, string $expected): void
    {
        $this->assertSame($expected, SectionLabel::fromName($routeName, $parameters));
    }

    public function test_bound_models_are_ignored(): void
    {
        $this->assertSame(
            'Letter Of Guarantee Issuance - Edit',
            SectionLabel::fromName('edit.letter.of.guarantee.issuance', ['letterOfGuaranteeIssuance' => new \stdClass])
        );
    }

    public function test_the_label_fits_the_activity_column(): void
    {
        $this->assertLessThanOrEqual(200, strlen(SectionLabel::fromName(str_repeat('very.long.', 60).'index')));
    }
}
