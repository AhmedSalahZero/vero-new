<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Services\Api\OdooService;
use Illuminate\Console\Command;
use Throwable;

/**
 * OdooProbeSoPoLinkCommand
 * ------------------------------------------------------------------
 * بيجاوب على سؤال واحد بس: هل الـ Purchase Orders عندنا في أودو
 * مربوطة فعلاً بالـ Sales Orders ، وبأي طريقة ، وهل عليها فواتير.
 *
 * الأمر ده **قراءة فقط** — مش بيكتب ولا بيعدّل أي حاجة لا في أودو ولا
 * عندنا. الهدف إننا نتأكد 100% قبل ما نعدّل OdooService::getContracts.
 *
 * في أودو 18 فيه 3 طرق ممكن الـ PO تكون مربوطة بيها بالـ SO ، وكلها
 * حقول قياسية موجودة في السورس ، بس اللي بيتملي منها بيعتمد على طريقة
 * شغل المشتريات عندهم:
 *
 *   A) purchase.order.line.sale_order_id  (related على sale_line_id.order_id
 *      ومخزّن في الداتابيز) — بيتملي لما أودو نفسه يولّد الـ PO من الـ SO
 *      (منتج خدمة بسياسة شراء ، أو MTO / Dropship).
 *   B) purchase.order.origin (Source Document) — بيتملي من الـ procurement
 *      أو المشتري بيكتبه بإيده.
 *   C) analytic_distribution على سطور الـ PO فيها الحساب التحليلي بتاع
 *      المشروع (project.project.account_id) — دي أقوى طريقة لو شغالين
 *      بالمشاريع.
 *
 * وناحية الفواتير: purchase.order.invoice_ids حقل مخزّن (Many2many على
 * account.move) ، وكمان account.move.line.purchase_line_id مخزّن ،
 * فالربط PO ← فواتير مضمون في الحالتين.
 *
 * USAGE
 *   php artisan odoo:probe-so-po --company=92 --user=64
 *   php artisan odoo:probe-so-po --company=92 --user=64 --start=2025-01-01 --end=2026-08-11 --limit=10
 */
class OdooProbeSoPoLinkCommand extends Command
{
    protected $signature = 'odoo:probe-so-po
        {--company= : Company id (required)}
        {--user= : User id whose Odoo credentials we use (required)}
        {--start= : write_date from — default: a year back}
        {--end= : write_date to — default: today}
        {--limit=5 : how many projects to inspect}';

    protected $description = 'Read-only probe: how are Odoo purchase orders linked to sales orders, and do they carry bills';

    private OdooService $odoo;

    public function handle(): int
    {
        $company = Company::find($this->option('company'));
        $user = User::find($this->option('user'));

        if (! $company || ! $user) {
            $this->error('--company و --user لازم يكونوا موجودين وصحيحين.');

            return self::FAILURE;
        }

        try {
            $this->odoo = new OdooService($company, $user);
        } catch (Throwable $e) {
            $this->error('فشل الاتصال بأودو: '.$e->getMessage());

            return self::FAILURE;
        }

        if (is_null($this->odoo->getUid())) {
            $this->error('المصادقة مع أودو فشلت — جرّب php artisan odoo:check الأول.');

            return self::FAILURE;
        }

        $this->line('');
        $this->info('الاتصال تمام — uid = '.$this->odoo->getUid());

        $this->probeFields();
        $this->probeData();

        return self::SUCCESS;
    }

    /**
     * * الخطوة ١: هل الحقول اللي هنعتمد عليها موجودة أصلاً في الأودو ده؟
     * * (وجود purchase.order.line.sale_order_id معناه إن موديول
     * * sale_purchase متسطّب ، وهو auto_install لما sale و purchase
     * * يكونوا متسطّبين)
     */
    private function probeFields(): void
    {
        $this->line('');
        $this->info('═══ الحقول ═══');

        $expected = [
            'purchase.order.line' => ['order_id', 'sale_line_id', 'sale_order_id', 'analytic_distribution', 'product_id'],
            'purchase.order' => ['name', 'origin', 'partner_id', 'currency_id', 'amount_total', 'date_order', 'state', 'invoice_ids', 'invoice_count'],
            'account.move.line' => ['purchase_line_id'],
            'account.move' => ['invoice_origin'],
            'project.project' => ['account_id', 'partner_id'],
            'sale.order' => ['project_id', 'name', 'amount_total', 'currency_id'],
        ];

        foreach ($expected as $model => $fields) {
            $definition = $this->odooCall($model, 'fields_get', [[], ['type', 'relation', 'store']]);

            if (! is_array($definition)) {
                $this->error("  ✗ {$model}: مقدرناش نقرا الحقول");

                continue;
            }

            $this->line('  '.$model);

            foreach ($fields as $field) {
                if (! isset($definition[$field])) {
                    $this->line(sprintf('    ✗ %-24s غير موجود', $field));

                    continue;
                }

                $meta = $definition[$field];
                $stored = ($meta['store'] ?? false) ? 'stored' : 'not stored';
                $this->line(sprintf('    ✓ %-24s %s %s %s', $field, $meta['type'] ?? '?', $meta['relation'] ?? '', '('.$stored.')'));
            }
        }
    }

    /**
     * * الخطوة ٢: الداتا الحقيقية — لكل مشروع ، الـ SOs بتاعته ، وكل طريقة
     * * ربط بتطلّع كام PO ، والـ POs دي عليها كام فاتورة مورّد.
     */
    private function probeData(): void
    {
        $start = $this->option('start') ?: now()->subYear()->format('Y-m-d');
        $end = $this->option('end') ?: now()->format('Y-m-d');
        $limit = (int) $this->option('limit');

        $this->line('');
        $this->info("═══ الداتا ({$start} → {$end}) ═══");

        $projects = $this->odooCall('project.project', 'search_read', [
            [['write_date', '>=', $start], ['write_date', '<=', $end]],
            ['id', 'name', 'account_id', 'partner_id'],
        ], ['limit' => $limit]);

        if (! is_array($projects) || ! count($projects)) {
            $this->warn('  مفيش مشاريع في الفترة دي.');

            return;
        }

        $totals = ['projects' => 0, 'sos' => 0, 'a' => 0, 'b' => 0, 'c' => 0, 'union' => 0, 'with_bills' => 0, 'bills' => 0];

        foreach ($projects as $project) {
            $totals['projects']++;
            $projectId = $project['id'];
            $analyticAccountId = $project['account_id'][0] ?? null;

            $this->line('');
            $this->line('  ── مشروع #'.$projectId.' — '.$project['name'].' | analytic account: '.($analyticAccountId ?: 'مفيش'));

            $salesOrders = $this->odooCall('sale.order', 'search_read', [
                [['project_id', '=', $projectId]],
                ['id', 'name', 'amount_total', 'currency_id', 'state'],
            ]);

            if (! is_array($salesOrders) || ! count($salesOrders)) {
                $this->line('     مفيش sales orders على المشروع ده.');

                continue;
            }

            $soIds = array_column($salesOrders, 'id');
            $soNames = array_column($salesOrders, 'name');
            $totals['sos'] += count($soIds);
            $this->line('     SOs: '.count($soIds).' → '.implode(', ', $soNames));

            $viaSaleOrderId = $this->poIdsViaSaleOrderId($soIds);
            $viaOrigin = $this->poIdsViaOrigin($soNames);
            $viaAnalytic = $analyticAccountId ? $this->poIdsViaAnalytic($analyticAccountId) : [];

            $totals['a'] += count($viaSaleOrderId);
            $totals['b'] += count($viaOrigin);
            $totals['c'] += count($viaAnalytic);

            $this->line('     A) purchase.order.line.sale_order_id : '.count($viaSaleOrderId).' PO');
            $this->line('     B) purchase.order.origin = SO name   : '.count($viaOrigin).' PO');
            $this->line('     C) analytic_distribution = المشروع    : '.count($viaAnalytic).' PO');

            $poIds = array_values(array_unique(array_merge($viaSaleOrderId, $viaOrigin, $viaAnalytic)));

            if (! count($poIds)) {
                $this->warn('     مفيش أي PO مربوط بالمشروع ده بأي طريقة.');

                continue;
            }

            $totals['union'] += count($poIds);
            $this->reportPurchaseOrders($poIds, $viaSaleOrderId, $viaOrigin, $viaAnalytic, $totals);
        }

        $this->line('');
        $this->info('═══ الخلاصة ═══');
        $this->line('  مشاريع: '.$totals['projects'].' | sales orders: '.$totals['sos']);
        $this->line('  POs عن طريق A (sale_order_id): '.$totals['a']);
        $this->line('  POs عن طريق B (origin): '.$totals['b']);
        $this->line('  POs عن طريق C (analytic): '.$totals['c']);
        $this->line('  إجمالي POs مميزة: '.$totals['union'].' — منها '.$totals['with_bills'].' عليها فواتير ('.$totals['bills'].' فاتورة)');
    }

    /**
     * @param  array<int>  $poIds
     * @param  array<int>  $viaSaleOrderId
     * @param  array<int>  $viaOrigin
     * @param  array<int>  $viaAnalytic
     * @param  array<string,int>  $totals
     */
    private function reportPurchaseOrders(array $poIds, array $viaSaleOrderId, array $viaOrigin, array $viaAnalytic, array &$totals): void
    {
        $purchaseOrders = $this->odooCall('purchase.order', 'read', [$poIds], [
            'fields' => ['id', 'name', 'origin', 'partner_id', 'currency_id', 'amount_total', 'date_order', 'state', 'invoice_count', 'invoice_ids'],
        ]);

        if (! is_array($purchaseOrders)) {
            $this->error('     مقدرناش نقرا الـ POs.');

            return;
        }

        foreach ($purchaseOrders as $purchaseOrder) {
            $methods = [];
            if (in_array($purchaseOrder['id'], $viaSaleOrderId)) {
                $methods[] = 'A';
            }
            if (in_array($purchaseOrder['id'], $viaOrigin)) {
                $methods[] = 'B';
            }
            if (in_array($purchaseOrder['id'], $viaAnalytic)) {
                $methods[] = 'C';
            }

            $billIds = $purchaseOrder['invoice_ids'] ?? [];
            $totals['bills'] += count($billIds);
            if (count($billIds)) {
                $totals['with_bills']++;
            }

            $this->line(sprintf(
                '     • PO %s [%s] مورّد: %s | %s %s | state=%s | origin=%s | فواتير: %d',
                $purchaseOrder['name'],
                implode('+', $methods),
                $purchaseOrder['partner_id'][1] ?? '?',
                $purchaseOrder['amount_total'] ?? 0,
                $purchaseOrder['currency_id'][1] ?? '?',
                $purchaseOrder['state'] ?? '?',
                $purchaseOrder['origin'] ?: '(فاضي)',
                count($billIds)
            ));

            if (! count($billIds)) {
                continue;
            }

            $bills = $this->odooCall('account.move', 'read', [$billIds], [
                'fields' => ['id', 'name', 'move_type', 'state', 'invoice_origin', 'amount_total'],
            ]);

            foreach (is_array($bills) ? $bills : [] as $bill) {
                $this->line(sprintf(
                    '         ↳ %s | %s | %s | invoice_origin=%s | %s',
                    $bill['name'] ?? '?',
                    $bill['move_type'] ?? '?',
                    $bill['state'] ?? '?',
                    $bill['invoice_origin'] ?: '(فاضي)',
                    $bill['amount_total'] ?? 0
                ));
            }
        }
    }

    /**
     * @param  array<int>  $soIds
     * @return array<int>
     */
    private function poIdsViaSaleOrderId(array $soIds): array
    {
        $lines = $this->odooCall('purchase.order.line', 'search_read', [
            [['sale_order_id', 'in', $soIds]],
            ['order_id'],
        ]);

        if (! is_array($lines)) {
            return [];
        }

        return array_values(array_unique(array_map(fn ($line) => $line['order_id'][0], $lines)));
    }

    /**
     * @param  array<string>  $soNames
     * @return array<int>
     */
    private function poIdsViaOrigin(array $soNames): array
    {
        /**
         * * origin ممكن يكون فيه أكتر من مستند مفصولين بفاصلة ، عشان كده
         * * بنستخدم ilike لكل اسم مش مقارنة بالتساوي
         */
        $domain = [];
        foreach ($soNames as $index => $soName) {
            if ($index > 0) {
                array_unshift($domain, '|');
            }
            $domain[] = ['origin', 'ilike', $soName];
        }

        if (! count($domain)) {
            return [];
        }

        $purchaseOrders = $this->odooCall('purchase.order', 'search_read', [$domain, ['id']]);

        return is_array($purchaseOrders) ? array_column($purchaseOrders, 'id') : [];
    }

    /**
     * @return array<int>
     */
    private function poIdsViaAnalytic(int $analyticAccountId): array
    {
        $lines = $this->odooCall('purchase.order.line', 'search_read', [
            [['analytic_distribution', 'in', [$analyticAccountId]]],
            ['order_id'],
        ]);

        if (! is_array($lines)) {
            return [];
        }

        return array_values(array_unique(array_map(fn ($line) => $line['order_id'][0], $lines)));
    }

    /**
     * * أي fault من أودو بيتطبع ومش بيوقّف الـ probe ، عشان نشوف باقي النتايج
     */
    private function odooCall(string $model, string $method, array $args, array $kwargs = [])
    {
        try {
            $result = $this->odoo->execute($model, $method, $args, $kwargs);
        } catch (Throwable $e) {
            $this->error("    ✗ {$model}.{$method}: ".$e->getMessage());

            return null;
        }

        if (is_array($result) && isset($result['faultCode'])) {
            $this->error("    ✗ {$model}.{$method}: ".($result['faultString'] ?? 'fault'));

            return null;
        }

        return $result;
    }
}
