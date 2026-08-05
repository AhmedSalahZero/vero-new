<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * PaginatesStatementQueries
 * ------------------------------------------------------------------
 * Real, database-level pagination for the Statement reports, replacing
 * PaginatesRawCollections on every report whose rows come from a single
 * query.
 *
 * The difference matters: PaginatesRawCollections fetches every matching
 * row into PHP memory and then slices it, so a 50-row page still costs a
 * full table read. The helpers here keep the row set in the database —
 * the page comes back via LIMIT/OFFSET, and the KPI totals are computed
 * with SQL aggregates over the same WHERE clause instead of by summing a
 * hydrated collection.
 *
 * ── The contract callers must honour ────────────────────────────────
 * Every helper takes a `$freshQuery` **factory**, not a builder. Each
 * call must return a brand-new builder carrying the same joins, wheres,
 * select and order. This is deliberate: `paginate()`, `first()` and
 * `sum()` all mutate the builder they run on, so handing the same
 * instance to two helpers silently corrupts the second one. A factory
 * makes that mistake impossible.
 *
 * ── What did NOT change ─────────────────────────────────────────────
 * KPI totals still describe the FULL filtered range, never the current
 * page — same guarantee PaginatesRawCollections documented. Only the
 * mechanism moved from PHP to SQL. Excel exports keep calling the same
 * factory without pagination, so the workbook is still the whole range.
 *
 * ملحوظة: أي تقرير بيحسب الرصيد الجاري صف بصف في PHP ما ينفعش يستخدم
 * الـ trait ده (الصفحة ٢ مش هتعرف الرصيد الختامي للصفحة ١). كشف الشركاء
 * كمان له مسار ترقيم خاص بيه على مستوى الشريك مش الصفوف.
 */
trait PaginatesStatementQueries
{
    /**
     * The page itself. Filters ride along on the page links so Next/Prev
     * never silently widen the date range.
     *
     * @param  callable():\Illuminate\Database\Query\Builder  $freshQuery
     */
    protected function paginateStatement(callable $freshQuery, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $freshQuery()->paginate($perPage)->withQueryString();
    }

    /**
     * SUM() over the full filtered set, one query for all columns.
     *
     * @param  callable():\Illuminate\Database\Query\Builder  $freshQuery
     * @param  array<string,string>  $columns  alias => fully-qualified column.
     *                                          Qualify every column: these
     *                                          queries join tables that repeat
     *                                          names like `debit` or `limit`.
     * @return array<string,float>
     */
    protected function statementSums(callable $freshQuery, array $columns): array
    {
        $selects = [];
        foreach ($columns as $alias => $column) {
            $selects[] = "COALESCE(SUM({$column}), 0) AS {$alias}";
        }

        $row = $freshQuery()->reorder()->select(DB::raw(implode(', ', $selects)))->first();

        $totals = [];
        foreach ($columns as $alias => $column) {
            $totals[$alias] = (float) ($row->{$alias} ?? 0);
        }

        return $totals;
    }

    /**
     * The earliest row in the range — the one carrying the opening
     * balance. The factory is ordered newest-first for display, so this
     * flips the order rather than reading the tail of a full fetch.
     *
     * @param  callable():\Illuminate\Database\Query\Builder  $freshQuery
     */
    protected function statementOldestRow(callable $freshQuery, string $table, string $dateColumn = 'date'): ?object
    {
        return $freshQuery()->reorder()
            ->orderBy("{$table}.{$dateColumn}")
            ->orderBy("{$table}.id")
            ->first();
    }

    /**
     * The latest row in the range — the one carrying the closing balance.
     *
     * @param  callable():\Illuminate\Database\Query\Builder  $freshQuery
     */
    protected function statementNewestRow(callable $freshQuery, string $table, string $dateColumn = 'date'): ?object
    {
        return $freshQuery()->reorder()
            ->orderByDesc("{$table}.{$dateColumn}")
            ->orderByDesc("{$table}.id")
            ->first();
    }

    /**
     * The five KPIs every ledger-style statement shows. Debit/credit are
     * summed in SQL; the opening and closing balances are read off the
     * boundary rows, because they are stored per row rather than derived.
     *
     * ⚠️ لازم $dateColumn يكون نفس العمود اللي التقرير بيرتّب بيه استعلامه.
     * الرصيد الجاري (beginning_balance/end_balance) بيتسلسل بـ
     * 'full_date asc , id asc' — شوف CurrentAccountBankStatement و
     * CashInSafeStatement و RepairStatementBalancesCommand::ORDER. فالتقرير
     * اللي بيعرض بـ full_date وبيقرا صفوف الحدود بعمود التاريخ date (من غير
     * وقت) بيجيب صف مختلف كل ما آخر يوم في الفترة يكون فيه أكتر من حركة
     * وأكبر id مايكونش هو آخر full_date — وده طبيعي لأن القيود اللي بتتسجّل
     * بتاريخ قديم بتاخد id أكبر. ساعتها الكارت بيعرض رصيد صف في نص الكشف.
     * الديفولت فضل 'date' للتقارير اللي بترتّب فعلاً بـ date.
     *
     * @param  callable():\Illuminate\Database\Query\Builder  $freshQuery
     * @return array<string,float|int>
     */
    protected function ledgerStatementKpis(callable $freshQuery, string $table, int $transactionCount, string $dateColumn = 'date'): array
    {
        $sums = $this->statementSums($freshQuery, [
            'total_debit' => "{$table}.debit",
            'total_credit' => "{$table}.credit",
        ]);

        $oldest = $this->statementOldestRow($freshQuery, $table, $dateColumn);
        $newest = $this->statementNewestRow($freshQuery, $table, $dateColumn);

        return [
            'beginningBalance' => (float) ($oldest->beginning_balance ?? 0),
            'endingBalance' => (float) ($newest->end_balance ?? 0),
            'totalDebit' => $sums['total_debit'],
            'totalCredit' => $sums['total_credit'],
            'transactionCount' => $transactionCount,
        ];
    }
}
