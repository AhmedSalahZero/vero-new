<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CustomerInvoice;
use App\Models\MoneyPayment;
use App\Models\MoneyReceived;
use App\Models\PaymentSettlement;
use App\Models\Settlement;
use App\Models\SupplierInvoice;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * AssignMoneyToInvoiceCommand
 * ------------------------------------------------------------------
 * توجيه مبلغ من money received / money payment على فاتورة معينة.
 *
 * المشكلة اللي بيحلها
 * -------------------
 * لما ينزل تحصيل في النظام من غير ما يتوجّه على فاتورة (يعني
 * money_received من غير settlements) بيحصل الآتي:
 *
 *   1. التعليق في كشف الحساب بيطلع
 *      "Incoming Transfer <العميل> Settled Invoices [ ]"  ← قوسين فاضيين،
 *      لأن HasBalances::appendBalances بتبني الأرقام من
 *      $moneyModel->settlements->pluck('invoice.invoice_number').
 *
 *   2. customer_invoices.collected_amount بتفضل صفر، لأن الترايجر
 *      insert_total_collected_amount على جدول settlements هو اللي
 *      بيحدّثها من مجموع التسويات.
 *
 *   3. وبالتالي عند المزامنة مع أودو، IsInvoice::createForOdoo بتحسب
 *      odoo_collected_amount = (المحصّل من أودو) - collected_amount
 *      فتطلع بالمبلغ كله، ويظهر سطر Collection زيادة في كشف الحساب
 *      فوق سطر التحصيل نفسه = ازدواج في الكريدت.
 *
 * الحل
 * ----
 * نعمل صف settlement يربط الـ money row بالفاتورة، فالترايجرات تتكفّل
 * بالباقي (collected_amount ثم total_collected_amount / net_balance /
 * invoice_status من ترايجر customer_invoices). وبعدها الأمر بيعيد حساب
 * odoo_collected_amount بنفس معادلة createForOdoo:
 *
 *      المحصّل الكلي من أودو = odoo_collected_amount + collected_amount (قبل)
 *      odoo_collected_amount (بعد) = max(0, الكلي - collected_amount (بعد))
 *
 * يعني في مثال الـ 440 دولار: أودو بتقول 440، النظام بقى عنده 440،
 * فـ 440 - 440 = صفر، ويختفي سطر الـ Collection المكرر.
 *
 * ملاحظات أمان
 * ------------
 * - المزامنة مع أودو مقفولة افتراضيًا (--sync-odoo لو عايزها). الفلوس
 *   أصلًا موجودة في أودو، وإنشاء Payment تاني هيعمل ازدواج هناك.
 * - لو الـ money row عنده تسويات "يتيمة" (invoice_id بيشاور على فاتورة
 *   مش موجودة، بيحصل لما أودو يعيد إنشاء الفاتورة بـ id جديد) الأمر
 *   بيقف ويطلب قرار صريح: --repoint=<settlement id> أو --ignore-orphans.
 *   ده متعمد: إضافة تسوية جديدة فوق يتيمة بنفس المبلغ بتضاعف المحصّل.
 * - الحذف مش خيار هنا: Settlement::deleting بتنادي
 *   OdooPayment::cancelPayments لو فيه odoo_id، فإعادة التوجيه (update)
 *   أأمن من delete + create.
 *
 * الاستخدام
 * ---------
 *   php artisan money:assign-to-invoice --company=92 --money-received=316 \
 *       --invoice=INV/2025/00024 --dry-run
 *
 *   php artisan money:assign-to-invoice --company=92 --money-received=316 \
 *       --invoice=INV/2025/00024 --amount=440 --currency=USD
 *
 *   php artisan money:assign-to-invoice --company=92 --money-received=316 \
 *       --invoice=INV/2025/00024 --repoint=256
 *
 *   php artisan money:assign-to-invoice --company=92 --money-payment=81 \
 *       --invoice=BILL/2025/0007 --amount=1200
 */
class AssignMoneyToInvoiceCommand extends Command
{
    protected $signature = 'money:assign-to-invoice
        {--company= : Company id}
        {--money-received= : money_received row id (customer side)}
        {--money-payment= : money_payments row id (supplier side)}
        {--invoice= : Target invoice number or invoice id}
        {--amount= : Amount in the invoice currency (default: the money row unsettled remainder)}
        {--withhold=0 : Withhold amount in the invoice currency}
        {--currency= : Assert the invoice currency before touching anything}
        {--repoint= : Re-point this existing settlement id onto the target invoice instead of creating a new row}
        {--ignore-orphans : Create a new settlement even though the money row already has orphan settlements}
        {--keep-odoo-amount : Do not recompute odoo_collected_amount / odoo_paid_amount}
        {--sync-odoo : Also create the payment in Odoo (off by default)}
        {--dry-run : Show what would happen without writing anything}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Assign an amount from a money received / money payment row onto a specific invoice, and refresh the invoice collected/paid amounts';

    /**
     * كل جانب وبياناته: الموديل، موديل التسوية، عمود الربط، وموديل الفاتورة.
     */
    private const SIDES = [
        'received' => [
            'money_class' => MoneyReceived::class,
            'settlement_class' => Settlement::class,
            'foreign_key' => 'money_received_id',
            'invoice_class' => CustomerInvoice::class,
            'partner_type' => 'is_customer',
        ],
        'payment' => [
            'money_class' => MoneyPayment::class,
            'settlement_class' => PaymentSettlement::class,
            'foreign_key' => 'money_payment_id',
            'invoice_class' => SupplierInvoice::class,
            'partner_type' => 'is_supplier',
        ],
    ];

    public function handle(): int
    {
        try {
            $side = $this->resolveSide();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $companyId = (int) $this->option('company');
        if (!$companyId) {
            $this->error('--company is required.');

            return self::FAILURE;
        }

        /** @var Company|null $company */
        $company = Company::find($companyId);
        if (!$company) {
            $this->error("Company {$companyId} not found.");

            return self::FAILURE;
        }

        $moneyClass = $side['money_class'];
        $moneyId = (int) ($this->option('money-received') ?: $this->option('money-payment'));

        /** @var MoneyReceived|MoneyPayment|null $money */
        $money = $moneyClass::where('company_id', $companyId)->find($moneyId);
        if (!$money) {
            $this->error(class_basename($moneyClass)." {$moneyId} not found for company {$companyId}.");

            return self::FAILURE;
        }

        $invoice = $this->resolveInvoice($side['invoice_class'], $companyId);
        if (!$invoice) {
            return self::FAILURE;
        }

        $invoiceClass = $side['invoice_class'];
        $clientIdColumn = $invoiceClass::CLIENT_ID_COLUMN_NAME;

        if ((int) $invoice->{$clientIdColumn} !== (int) $money->partner_id) {
            $this->error(sprintf(
                'Partner mismatch: the money row belongs to partner %d but invoice %s belongs to partner %s.',
                $money->partner_id,
                $invoice->getInvoiceNumber(),
                $invoice->{$clientIdColumn}
            ));

            return self::FAILURE;
        }

        $moneyInvoiceCurrency = $money->getInvoiceCurrency();
        if ($moneyInvoiceCurrency && $invoice->currency !== $moneyInvoiceCurrency) {
            $this->error(sprintf(
                'Currency mismatch: the money row is settled in %s but invoice %s is in %s.',
                $moneyInvoiceCurrency,
                $invoice->getInvoiceNumber(),
                $invoice->currency
            ));

            return self::FAILURE;
        }

        $assertedCurrency = $this->option('currency');
        if ($assertedCurrency && strtoupper($assertedCurrency) !== strtoupper((string) $invoice->currency)) {
            $this->error(sprintf(
                'Currency assertion failed: --currency=%s but invoice %s is in %s.',
                $assertedCurrency,
                $invoice->getInvoiceNumber(),
                $invoice->currency
            ));

            return self::FAILURE;
        }

        $settlements = $side['settlement_class']::where($side['foreign_key'], $money->getKey())->get();
        $orphans = $settlements->filter(fn ($settlement) => is_null($settlement->invoice));

        $this->printMoneyRow($money, $invoice, $settlements, $orphans);

        $repointId = $this->option('repoint');
        $repointed = null;
        if ($repointId) {
            $repointed = $settlements->firstWhere('id', (int) $repointId);
            if (!$repointed) {
                $this->error("Settlement {$repointId} does not belong to this money row.");

                return self::FAILURE;
            }
        } elseif ($orphans->isNotEmpty() && !$this->option('ignore-orphans')) {
            $this->error('This money row already has orphan settlement(s): '.$orphans->pluck('id')->implode(', '));
            $this->line('  Their invoice_id points at a row that no longer exists, so their amount is already');
            $this->line('  counted against the money row but against no invoice. Creating a new settlement on top');
            $this->line('  would double the collected amount.');
            $this->line('  Re-point one of them with  --repoint=<settlement id>');
            $this->line('  or add  --ignore-orphans  if a brand new settlement really is what you want.');

            return self::FAILURE;
        }

        $settledElsewhere = $settlements
            ->when($repointed, fn ($all) => $all->where('id', '!=', $repointed->id))
            ->sum(fn ($settlement) => (float) $settlement->settlement_amount);
        $moneyAmount = (float) $money->getAmountInInvoiceCurrency();
        $remaining = round($moneyAmount - $settledElsewhere, 5);

        $amount = $this->option('amount') !== null
            ? (float) unformat_number($this->option('amount'))
            : ($repointed ? (float) $repointed->settlement_amount : $remaining);
        $withhold = (float) unformat_number((string) $this->option('withhold'));

        if ($amount <= 0) {
            $this->error("Nothing to assign: computed amount is {$amount}.");

            return self::FAILURE;
        }

        if ($amount > $remaining + 0.01) {
            $this->error(sprintf(
                'Amount %s exceeds the unsettled remainder %s of this money row (total %s %s, already settled elsewhere %s).',
                number_format($amount, 2),
                number_format($remaining, 2),
                number_format($moneyAmount, 2),
                $moneyInvoiceCurrency,
                number_format($settledElsewhere, 2)
            ));

            return self::FAILURE;
        }

        $action = $repointed
            ? sprintf('Re-point settlement #%d (%s) onto invoice %s with amount %s %s', $repointed->id, $repointed->invoice_id ?? 'null', $invoice->getInvoiceNumber(), number_format($amount, 2), $invoice->currency)
            : sprintf('Create a new settlement of %s %s on invoice %s', number_format($amount, 2), $invoice->currency, $invoice->getInvoiceNumber());

        $this->newLine();
        $this->info('Planned action:');
        $this->line('  '.$action);
        if ($withhold > 0) {
            $this->line('  Withhold: '.number_format($withhold, 2).' '.$invoice->currency);
        }
        $this->line('  Odoo payment sync: '.($this->option('sync-odoo') ? 'ON' : 'off'));
        $this->line('  Recompute odoo amount: '.($this->option('keep-odoo-amount') ? 'off' : 'ON'));

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('--dry-run: nothing was written.');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Apply?', false)) {
            $this->comment('Aborted.');

            return self::SUCCESS;
        }

        try {
            $this->apply($side, $company, $money, $invoice, $repointed, $amount, $withhold);
        } catch (Throwable $e) {
            $this->error('Failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Done.');
        $this->printInvoiceAmounts($invoice->fresh());

        return self::SUCCESS;
    }

    /**
     * @param  array<string,mixed>  $side
     */
    private function apply(array $side, Company $company, MoneyReceived|MoneyPayment $money, CustomerInvoice|SupplierInvoice $invoice, Settlement|PaymentSettlement|null $repointed, float $amount, float $withhold): void
    {
        $invoiceClass = $side['invoice_class'];
        $collectedColumn = $invoiceClass::COLLETED_OR_PAID_AMOUNT;
        $collectedMainColumn = $invoiceClass::COLLETED_OR_PAID_AMOUNT_IN_MAIN_CURRENCY;
        $odooColumn = $invoiceClass::ODOO_COLLETED_OR_PAID_AMOUNT;
        $odooMainColumn = $invoiceClass::ODOO_COLLETED_OR_PAID_AMOUNT_IN_MAIN_CURRENCY;

        /**
         * * المحصّل الكلي اللي أودو شايفه = اللي أودو مسجّله عندنا + اللي النظام مسجّله
         * * دي نفس معادلة IsInvoice::createForOdoo بالعكس، فبنطلع بنفس الرقم
         * * اللي المزامنة الجاية هتحسبه بالظبط.
         */
        $odooTotal = (float) $invoice->{$odooColumn} + (float) $invoice->{$collectedColumn};
        $odooTotalInMainCurrency = (float) $invoice->{$odooMainColumn} + (float) $invoice->{$collectedMainColumn};

        /**
         * * ترايجر update_total_collected_amount بيعيد حساب new.invoice_id بس،
         * * فالفاتورة القديمة بتفضل بقيمة قديمة لو أعدنا التوجيه — بنصلّحها بإيدينا.
         * * وبناخد صورتها قبل التعديل عشان نعيد حساب رقم أودو بتاعها هي كمان.
         */
        $previousInvoiceId = $repointed ? $repointed->invoice_id : null;
        $previousInvoice = $previousInvoiceId && (int) $previousInvoiceId !== (int) $invoice->getKey()
            ? $invoiceClass::find($previousInvoiceId)
            : null;
        $previousOdooTotal = $previousInvoice
            ? (float) $previousInvoice->{$odooColumn} + (float) $previousInvoice->{$collectedColumn}
            : 0.0;
        $previousOdooTotalInMainCurrency = $previousInvoice
            ? (float) $previousInvoice->{$odooMainColumn} + (float) $previousInvoice->{$collectedMainColumn}
            : 0.0;

        DB::transaction(function () use ($company, $money, $invoice, $repointed, $amount, $withhold) {
            if ($repointed) {
                $repointed->update([
                    'invoice_id' => $invoice->getKey(),
                    'settlement_amount' => $amount,
                    'withhold_amount' => $withhold,
                ]);

                return;
            }

            $money->storeNewSettlement(
                [[
                    'invoice_id' => $invoice->getKey(),
                    'settlement_amount' => $amount,
                    'withhold_amount' => $withhold,
                ]],
                (int) $money->partner_id,
                $company,
                false,
                (bool) $this->option('sync-odoo')
            );
        });

        if ($previousInvoice) {
            $this->recalculateCollectedFromSettlements($side, $previousInvoice);
            $this->recalculateOdooAmount($side, $previousInvoice, $previousOdooTotal, $previousOdooTotalInMainCurrency);
        }

        $money->refresh();
        $this->refreshMoneyComment($money);

        $this->recalculateOdooAmount($side, $invoice, $odooTotal, $odooTotalInMainCurrency);
    }

    /**
     * إعادة حساب رقم أودو بنفس معادلة IsInvoice::createForOdoo، عشان الرقم
     * اللي يظهر دلوقتي يبقى هو نفسه اللي المزامنة الجاية هتوصل له.
     *
     * @param  array<string,mixed>  $side
     */
    private function recalculateOdooAmount(array $side, CustomerInvoice|SupplierInvoice $invoice, float $odooTotal, float $odooTotalInMainCurrency): void
    {
        if ($this->option('keep-odoo-amount')) {
            return;
        }

        $invoiceClass = $side['invoice_class'];
        $invoice->refresh();
        $invoice->update([
            $invoiceClass::ODOO_COLLETED_OR_PAID_AMOUNT => max(0, round($odooTotal - (float) $invoice->{$invoiceClass::COLLETED_OR_PAID_AMOUNT}, 5)),
            $invoiceClass::ODOO_COLLETED_OR_PAID_AMOUNT_IN_MAIN_CURRENCY => max(0, round($odooTotalInMainCurrency - (float) $invoice->{$invoiceClass::COLLETED_OR_PAID_AMOUNT_IN_MAIN_CURRENCY}, 5)),
        ]);
    }

    /**
     * إعادة حساب المحصّل للفاتورة اللي التسوية اتشالت من عليها،
     * لأن ترايجر الـ UPDATE بيشتغل على new.invoice_id بس.
     *
     * @param  array<string,mixed>  $side
     */
    private function recalculateCollectedFromSettlements(array $side, CustomerInvoice|SupplierInvoice $invoice): void
    {
        $invoiceClass = $side['invoice_class'];

        $totals = $side['settlement_class']::where('invoice_id', $invoice->getKey())
            ->selectRaw('ifnull(sum(settlement_amount),0) as settled, ifnull(sum(withhold_amount),0) as withheld')
            ->first();

        $invoice->update([
            $invoiceClass::COLLETED_OR_PAID_AMOUNT => (float) $totals->settled,
            'withhold_amount' => (float) $totals->withheld,
        ]);

        $this->line('  Recalculated invoice #'.$invoice->getKey().' after the settlement moved away.');
    }

    /**
     * التعليق المخزَّن بيتولد وقت الإنشاء بأرقام الفواتير اللي كانت في الريكوست،
     * فبعد ما نغيّر التسويات بنعيد توليده من الأرقام الحالية.
     */
    private function refreshMoneyComment(MoneyReceived|MoneyPayment $money): void
    {
        $moneyClass = get_class($money);
        if (!method_exists($moneyClass, 'generateComment')) {
            return;
        }

        $invoiceNumbers = implode('/', $money->settlements->pluck('invoice.invoice_number')->filter()->toArray());

        $money->update([
            'comment_en' => $moneyClass::generateComment($money, 'en', $invoiceNumbers, ''),
            'comment_ar' => $moneyClass::generateComment($money, 'ar', $invoiceNumbers, ''),
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function resolveSide(): array
    {
        $received = $this->option('money-received');
        $payment = $this->option('money-payment');

        if ($received && $payment) {
            throw new \InvalidArgumentException('Pass either --money-received or --money-payment, not both.');
        }
        if (!$received && !$payment) {
            throw new \InvalidArgumentException('Pass --money-received=<id> or --money-payment=<id>.');
        }

        return $received ? self::SIDES['received'] : self::SIDES['payment'];
    }

    private function resolveInvoice(string $invoiceClass, int $companyId): CustomerInvoice|SupplierInvoice|null
    {
        $reference = $this->option('invoice');
        if (!$reference) {
            $this->error('--invoice is required (invoice number or invoice id).');

            return null;
        }

        $query = $invoiceClass::where('company_id', $companyId);
        $matches = is_numeric($reference)
            ? $query->where(fn ($q) => $q->where('id', (int) $reference)->orWhere('invoice_number', $reference))->get()
            : $query->where('invoice_number', $reference)->get();

        if ($matches->isEmpty()) {
            $this->error("Invoice {$reference} not found for company {$companyId}.");

            return null;
        }

        if ($matches->count() > 1) {
            $this->error("Invoice {$reference} is ambiguous — matched ids: ".$matches->pluck('id')->implode(', ').'. Pass the id instead.');

            return null;
        }

        return $matches->first();
    }

    /**
     * @param  Collection<int,Settlement|PaymentSettlement>  $settlements
     * @param  Collection<int,Settlement|PaymentSettlement>  $orphans
     */
    private function printMoneyRow(MoneyReceived|MoneyPayment $money, CustomerInvoice|SupplierInvoice $invoice, Collection $settlements, Collection $orphans): void
    {
        $this->info(class_basename($money).' #'.$money->getKey());
        $this->table(['field', 'value'], [
            ['type', $money->getType()],
            ['date', $money->getDate()],
            ['partner', $money->partner_id.' - '.($money->partner?->getName() ?? '<missing>')],
            ['received/paid', number_format((float) $money->getAmount(), 2).' '.$money->getReceivingOrPaymentCurrency()],
            ['in invoice currency', number_format((float) $money->getAmountInInvoiceCurrency(), 2).' '.$money->getInvoiceCurrency()],
            ['settlements', $settlements->count().($orphans->isNotEmpty() ? ' ('.$orphans->count().' orphan)' : '')],
        ]);

        if ($settlements->isNotEmpty()) {
            $this->line('Existing settlements:');
            $this->table(
                ['id', 'invoice_id', 'invoice_number', 'amount', 'withhold', 'odoo_id'],
                $settlements->map(fn ($settlement) => [
                    $settlement->id,
                    $settlement->invoice_id,
                    $settlement->invoice?->getInvoiceNumber() ?? '<missing>',
                    $settlement->settlement_amount,
                    $settlement->withhold_amount,
                    $settlement->odoo_id,
                ])->toArray()
            );
        }

        $this->line('Target invoice:');
        $this->printInvoiceAmounts($invoice);
    }

    private function printInvoiceAmounts(CustomerInvoice|SupplierInvoice $invoice): void
    {
        $invoiceClass = get_class($invoice);

        $this->table(
            ['id', 'number', 'currency', 'net', 'system', 'odoo', 'total', 'net balance', 'status'],
            [[
                $invoice->getKey(),
                $invoice->getInvoiceNumber(),
                $invoice->currency,
                $invoice->net_invoice_amount,
                $invoice->{$invoiceClass::COLLETED_OR_PAID_AMOUNT},
                $invoice->{$invoiceClass::ODOO_COLLETED_OR_PAID_AMOUNT},
                $invoice->getTotalCollectedOrPaid(),
                $invoice->net_balance,
                $invoice->invoice_status,
            ]]
        );
    }
}
