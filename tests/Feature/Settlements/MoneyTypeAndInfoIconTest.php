<?php

namespace Tests\Feature\Settlements;

use App\Models\MoneyPayment;
use App\Models\MoneyReceived;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * * حاجتين في صفحة القوائم :
 *
 * * ١ - عمود الـ Type كان بيقول "استلام من [ موظف ]" بس ، من غير ما يقول
 * *     استلام ايه — رد عهدة ولا سداد قرض . النوع متخزن في transaction_type
 * *     و كان متسيب من غير عرض
 *
 * * ٢ - زرار الـ i كان بيظهر على كل صف ، حتى لما البوب اب يفتح فاضي
 */
class MoneyTypeAndInfoIconTest extends TestCase
{
    private ?string $originalDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDatabase = config('database.connections.mysql.database');

        config(['database.connections.mysql.database' => env('SMOKE_DB', 'veroanalysis')]);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Development database not reachable.');
        }
    }

    protected function tearDown(): void
    {
        config(['database.connections.mysql.database' => $this->originalDatabase]);
        DB::purge('mysql');

        parent::tearDown();
    }

    private function stub(string $class, array $attributes, array $settlements = []): object
    {
        $money = new $class;
        $money->forceFill($attributes);
        $money->setRelation('settlements', collect($settlements));
        $money->setRelation('downPaymentSettlements', collect([]));
        $money->setRelation('contract', null);

        return $money;
    }

    /* ───────────── عمود الـ Type ───────────── */

    public function test_the_type_names_the_transaction_next_to_the_partner(): void
    {
        $money = $this->stub(MoneyReceived::class, [
            'money_type' => 'money-received',
            'partner_type' => 'is_employee',
            'transaction_type' => 'refund-custody',
        ]);

        $this->assertSame(
            __('Money Received From [ :partnerType ]', ['partnerType' => $money->getPartnerTypeFormatted()])
                .' [ '.__('Refund Custody').' ]',
            $money->getMoneyTypeFormatted()
        );
    }

    public function test_money_payment_names_its_transaction_too(): void
    {
        $money = $this->stub(MoneyPayment::class, [
            'money_type' => 'money-payment',
            'partner_type' => 'is_employee',
            'transaction_type' => 'custody',
        ]);

        $this->assertStringContainsString('[ '.__('Custody').' ]', $money->getMoneyTypeFormatted());
    }

    /**
     * * من غير نوع عملية الوصف لازم يفضل زي ما هو بالظبط — من غير قوسين
     * * فاضيين
     */
    public function test_without_a_transaction_type_the_label_is_unchanged(): void
    {
        $money = $this->stub(MoneyReceived::class, [
            'money_type' => 'money-received',
            'partner_type' => 'is_employee',
            'transaction_type' => null,
        ]);

        $this->assertSame(
            __('Money Received From [ :partnerType ]', ['partnerType' => $money->getPartnerTypeFormatted()]),
            $money->getMoneyTypeFormatted()
        );
        $this->assertStringNotContainsString('[  ]', $money->getMoneyTypeFormatted());
    }

    /** * كل أنواع العمليات ليها ترجمة — مفيش واحد بيطلع بالمفتاح الخام */
    public function test_every_transaction_type_has_a_readable_label(): void
    {
        $types = ['refund-custody', 'pay-loan', 'funding-from', 'insurance-from',
            'custody', 'loan', 'funding-to', 'dividend-payment',
            'investment-in-subsidiary-company', 'insurance-to', 'pay-to'];

        foreach ($types as $type) {
            $money = $this->stub(MoneyReceived::class, ['transaction_type' => $type]);
            $label = $money->getTransactionTypeFormatted();

            $this->assertNotSame('', $label, $type.' must render a label');
            $this->assertStringNotContainsString('-', $label, $type.' must not leak the raw key');
        }
    }

    /**
     * * السجل الحقيقي اللي العميل اتكلم عنه
     */
    public function test_the_reported_record_reads_as_expected(): void
    {
        $money = MoneyReceived::find(1537);

        if (! $money || $money->getTransactionType() !== 'refund-custody') {
            $this->markTestSkipped('Record 1537 is not the refund-custody row on this database.');
        }

        $this->assertSame(
            'Money Received From [ Employee ] [ Refund Custody ]',
            $money->getMoneyTypeFormatted()
        );
    }

    /* ───────────── زرار الـ i ───────────── */

    public function test_the_icon_is_hidden_when_there_is_nothing_to_show(): void
    {
        $money = $this->stub(MoneyReceived::class, [
            'money_type' => 'money-received',
            'partner_type' => 'is_employee',
            'transaction_type' => 'refund-custody',
            'received_amount' => 5000,
        ]);

        $this->assertFalse($money->hasSettlementDetailsToShow(),
            'مفيش تسويات و لا دفعة مقدمة — الزرار ما يظهرش');
    }

    public function test_the_icon_shows_when_there_are_settlements(): void
    {
        $settlement = new Settlement;
        $settlement->forceFill(['settlement_amount' => 100, 'withhold_amount' => 0, 'is_from_down_payment' => 0]);
        $settlement->setRelation('invoice', null);

        $money = $this->stub(MoneyReceived::class, [
            'money_type' => 'money-received',
            'received_amount' => 100,
        ], [$settlement]);

        $this->assertTrue($money->hasSettlementDetailsToShow());
    }

    /** * الدفعة المقدمة الصافية ملهاش فواتير ، و مع ذلك عندها تفاصيل تستحق العرض */
    public function test_the_icon_shows_for_a_plain_down_payment(): void
    {
        $money = $this->stub(MoneyReceived::class, [
            'money_type' => MoneyReceived::DOWN_PAYMENT,
            'down_payment_type' => MoneyReceived::DOWN_PAYMENT_GENERAL,
            'received_amount' => 4000,
        ]);

        $this->assertTrue($money->hasSettlementDetailsToShow(),
            'الدفعة المقدمة الصافية لازم يبان لها الزرار');
    }

    /**
     * * الواجهة هنا بليد ، فالزرار نفسه هو اللي متلفوف بالشرط — كل مرة
     * * الزرار بيتحط فيها لازم تكون متغطية
     */
    public function test_every_icon_include_is_gated(): void
    {
        foreach ([
            resource_path('views/reports/moneyReceived/index.blade.php'),
            resource_path('views/reports/moneyPayments/index.blade.php'),
        ] as $path) {
            $source = file_get_contents($path);

            $includes = substr_count($source, "_settlements_info_button");
            $gates = substr_count($source, 'hasSettlementDetailsToShow()');

            $this->assertGreaterThan(0, $includes, basename(dirname($path)).' should render the icon somewhere.');
            $this->assertSame($includes, $gates,
                basename(dirname($path)).': every icon include must be wrapped in the check ('
                .$includes.' includes vs '.$gates.' gates).');
        }
    }

}
