<?php

namespace App\Http\Controllers;

use App\Enums\LcTypes;
use App\Enums\LgTypes;
use App\Exports\Statements\LgLcStatementExport;
use App\Models\Company;
use App\Models\FinancialInstitution;
use App\Models\LcOverdraftBankStatement;
use App\Models\LetterOfCreditFacility;
use App\Models\LetterOfCreditIssuance;
use App\Models\LetterOfGuaranteeIssuance;
use App\Traits\GeneralFunctions;
use App\Traits\PaginatesStatementQueries;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LGLCSBanktatementController
 * ------------------------------------------------------------------
 * تقرير "LG & LC Statement" — كشف رصيد جاري زي كشف البنك، بس بيقرا من
 * تلات جداول مختلفة حسب Report Type:
 *   - LetterOfCreditIssuance    → letter_of_credit_statements    (فلترة بالعملة + البنك)
 *   - LetterOfGuaranteeIssuance → letter_of_guarantee_statements (نفس الفلترة)
 *   - LCOverdraft               → lc_overdraft_bank_statements   (فلترة بتسهيل LC
 *     بدل العملة/البنك — أوفردرافت التسهيل مش مربوط بعملة أو بنك معيّن)
 *
 * كل الأعمدة محسوبة ومخزّنة في الداتابيز عن طريق التريجرات، الكنترولر
 * ده بيقرا ويعرض بس.
 *
 * المنطق منقول من cashvero.evoqas.com مع الإبقاء على Blade (من غير Vue).
 * اللي اتنقل:
 *   1. حارس whitelist لـ report_type — قبل كده كان بياخد المفتاح على طول،
 *      فأي قيمة مش معروفة بتدي warning وتوصل DB::table(null) وترمي خطأ.
 *   2. أمان الـ null على $financialInstitution — في تقرير LCOverdraft
 *      البنك مش مطلوب، وكان ->getName() بيرمي fatal لو مالقاش بنك.
 *   3. استخراج fetchStatementData() عشان العرض والتصدير يشتغلوا على نفس
 *      الاستعلام، فمستحيل يفترقوا.
 *   4. ترقيم من السيرفر (التقرير كان بيحمّل كل الصفوف مرة واحدة).
 *   5. إجماليات KPI من SQL على المدى الكامل مش على الصفحة المعروضة.
 *   6. تصدير Excel من السيرفر للمدى الكامل.
 *
 * ⚠️ ليه التصدير من السيرفر لازم مع الترقيم: القالب كان بيعتمد على أزرار
 *    DataTables اللي بتصدّر من المتصفح. مع الترقيم دي كانت هتصدّر الصفحة
 *    المعروضة بس (٥٠ صف) من غير ما حد ياخد باله — فاتشال زر التصدير
 *    القديم واتحط لينك تصدير من السيرفر بيغطي كل المدى.
 *
 * ⚠️ كود ميت اتشال: index() كانت بتبني
 *    $accountTypes = AccountType::onlyCashAccounts()->get() و
 *    $selectedAccountTypeName وتبعتهم للقالب، لكن
 *    lg_lc_statement_form.blade.php ما فيهوش أي حقل Account Type
 *    (اتأكدت: صفر استخدام). اتشالوا من غير أي تغيير في السلوك.
 */
class LGLCSBanktatementController
{
    use GeneralFunctions;
    use PaginatesStatementQueries;

    private const ROWS_PER_PAGE = 50;

    private const STATEMENT_TABLE_BY_TYPE = [
        'LetterOfCreditIssuance' => 'letter_of_credit_statements',
        'LetterOfGuaranteeIssuance' => 'letter_of_guarantee_statements',
        'LCOverdraft' => 'lc_overdraft_bank_statements',
    ];

    private const TYPE_COLUMN_BY_REPORT_TYPE = [
        'LetterOfCreditIssuance' => 'lc_type',
        'LetterOfGuaranteeIssuance' => 'lg_type',
    ];

    /**
     * ⚠️ عمود الترتيب لازم يبقى نفس العمود اللي التريجر بيبني بيه سلسلة الأرصدة،
     * وإلا الصفوف تتعرض بترتيب مخالف للـ beginning/end balance المخزّنة.
     * letter_of_credit_statements و letter_of_guarantee_statements بيربطوا بالصف
     * السابق بـ full_date، أما lc_overdraft_bank_statements فبـ date.
     */
    private const ORDER_COLUMN_BY_TABLE = [
        'letter_of_credit_statements' => 'full_date',
        'letter_of_guarantee_statements' => 'full_date',
        'lc_overdraft_bank_statements' => 'date',
    ];

    public function index(Company $company, Request $request)
    {
        $lcSources = LetterOfCreditIssuance::lcSources();
        $selectedCurrency = $request->get('currency');
        $financialInstitutionBanks = FinancialInstitution::onlyForCompany($company->id)->onlyBanks()->get();

        return view('lg_lc_statement_form', [
            'company' => $company,
            'financialInstitutionBanks' => $financialInstitutionBanks,
            'selectedCurrency' => $selectedCurrency,
            'lcSources' => $lcSources,
        ]);
    }

    /**
     * نقطة ajax للـ cascade بتاع Type/Source — من غير تغيير.
     */
    public function getLgOrLcType(Request $request, Company $company)
    {
        $modelName = $request->get('lcOrLg');

        $types = [
            'LetterOfCreditIssuance' => LcTypes::getAll(),
            'LetterOfGuaranteeIssuance' => LgTypes::getAll(),
            'LCOverdraft' => [],
        ][$modelName] ?? [];

        $sources = [
            'LetterOfCreditIssuance' => LetterOfCreditIssuance::lcSources(),
            'LetterOfGuaranteeIssuance' => LetterOfGuaranteeIssuance::lgSources(),
            'LCOverdraft' => [],
        ][$modelName] ?? [];

        return response()->json([
            'types' => $types,
            'sources' => $sources,
        ]);
    }

    /**
     * بيبني الاستعلام حسب الفلاتر الحالية. اختيار الجدول والفلاتر اللي
     * بتتطبق متوقفين بالكامل على Report Type — نفس التفريع الأصلي بالظبط.
     *
     * بيرجع closure مش نتيجة، عشان result() و exportExcel() يشتغلوا على
     * نفس الاستعلام من غير ما نسخة تفرق عن التانية.
     *
     * بيرجع null لو مفيش صفوف أو الـ report_type مش معروف.
     *
     * @return array<string, mixed>|null
     */
    private function fetchStatementData(Company $company, Request $request): ?array
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $financialInstitutionId = $request->get('financial_institution_id');
        $financialInstitution = FinancialInstitution::find($financialInstitutionId);
        $lcFacilityId = $request->get('lc_facility_id');
        // في تقرير LCOverdraft البنك مش مطلوب، فلازم null-safe هنا
        $financialInstitutionName = $financialInstitution ? $financialInstitution->getName() : null;

        $letterOfCreditFacility = LetterOfCreditFacility::find($lcFacilityId);
        $letterOfCreditFacilityName = $letterOfCreditFacility ? $letterOfCreditFacility->getName() : null;
        $currencyName = $request->get('currency');
        $reportType = $request->get('report_type');

        // whitelist — اسم الجدول لازم يتحدد من القائمة الثابتة بس
        $statementTableName = self::STATEMENT_TABLE_BY_TYPE[$reportType] ?? null;
        if (! $statementTableName) {
            return null;
        }

        $isLcOverdraftBankStatement = $statementTableName == 'lc_overdraft_bank_statements';
        $lcTypeOrLgTypeColumnName = self::TYPE_COLUMN_BY_REPORT_TYPE[$reportType] ?? null;
        $orderColumnName = self::ORDER_COLUMN_BY_TABLE[$statementTableName];

        $source = $request->get('source');
        $type = $request->get('type');

        $freshQuery = fn () => DB::table($statementTableName)
            ->where($statementTableName.'.company_id', $company->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->when(! $isLcOverdraftBankStatement, function ($q) use ($currencyName) {
                $q->where('currency', $currencyName);
            })
            ->when(! $isLcOverdraftBankStatement, function ($q) use ($financialInstitutionId) {
                $q->where('financial_institution_id', $financialInstitutionId);
            })
            ->when($source, function ($q) use ($source) {
                $q->where('source', $source);
            })
            ->when($isLcOverdraftBankStatement, function ($q) use ($lcFacilityId) {
                $q->where('lc_facility_id', $lcFacilityId);
            })
            ->when($lcTypeOrLgTypeColumnName, function ($q) use ($lcTypeOrLgTypeColumnName, $type) {
                $q->where($lcTypeOrLgTypeColumnName, $type);
            })
            ->orderByRaw($statementTableName.'.'.$orderColumnName.' desc , '.$statementTableName.'.id desc');

        if (! $freshQuery()->exists()) {
            return null;
        }

        $sourceLabel = [
            'LetterOfCreditIssuance' => LetterOfCreditIssuance::lcSources(),
            'LetterOfGuaranteeIssuance' => LetterOfGuaranteeIssuance::lgSources(),
            'LCOverdraft' => LcOverdraftBankStatement::getSources(),
        ][$reportType][$source] ?? null;

        $typeLabel = [
            'LetterOfCreditIssuance' => LcTypes::getAll(),
            'LetterOfGuaranteeIssuance' => LgTypes::getAll(),
        ][$reportType][$type] ?? null;

        return [
            'query' => $freshQuery,
            'statementTable' => $statementTableName,
            'isLcOverdraftBankStatement' => $isLcOverdraftBankStatement,
            'financialInstitutionName' => $financialInstitutionName,
            'letterOfCreditFacilityName' => $letterOfCreditFacilityName,
            'currencyName' => $currencyName,
            'sourceLabel' => $sourceLabel,
            'typeLabel' => $typeLabel,
        ];
    }

    /**
     * التقرير نفسه. منطق الاستعلام كله في fetchStatementData().
     * الترقيم من السيرفر جديد — الإجماليات فوق بتغطي المدى الكامل عن طريق
     * SUM في SQL، مش الصفحة المعروضة.
     */
    public function result(Company $company, Request $request)
    {
        $data = $this->fetchStatementData($company, $request);
        if (is_null($data)) {
            return redirect()->back()->with('fail', __('No Data Found'));
        }

        $results = $this->paginateStatement($data['query'], self::ROWS_PER_PAGE);
        $kpis = $this->ledgerStatementKpis($data['query'], $data['statementTable'], $results->total());

        return view('lc_lg_bank_statement_result', [
            'results' => $results,
            'kpis' => $kpis,
            'currency' => $data['currencyName'],
            'financialInstitutionName' => $data['financialInstitutionName'],
            'type' => $data['typeLabel'],
            'source' => $data['sourceLabel'],
            'isLcOverdraftBankStatement' => $data['isLcOverdraftBankStatement'],
            'letterOfCreditFacilityName' => $data['letterOfCreditFacilityName'],
            'exportUrl' => route('export.lg.lc.bank.statement', array_merge(['company' => $company->id], $request->only([
                'start_date', 'end_date', 'currency', 'financial_institution_id', 'report_type', 'source', 'type', 'lc_facility_id',
            ]))),
        ]);
    }

    /**
     * تصدير Excel — نفس فلاتر result() ونفس الاستعلام، بس من غير ترقيم
     * فالملف بيغطي المدى الكامل مش الصفحة المعروضة.
     */
    public function exportExcel(Company $company, Request $request)
    {
        $data = $this->fetchStatementData($company, $request);
        if (is_null($data)) {
            return redirect()->back()->with('fail', __('No Data Found'));
        }

        $isLcOverdraftBankStatement = $data['isLcOverdraftBankStatement'];
        $lang = app()->getLocale();

        $headings = $isLcOverdraftBankStatement
            ? ['#', 'Date', 'Limit', 'Beginning Balance', 'Debit', 'Credit', 'Room', 'End Balance', 'Comment']
            : ['#', 'Date', 'Beginning Balance', 'Debit', 'Credit', 'End Balance', 'Comment'];

        $rows = $data['query']()->get()->values()->map(function ($row, $index) use ($lang, $isLcOverdraftBankStatement) {
            $mapped = [
                '#' => $index + 1,
                'Date' => Carbon::make($row->date)->format('d-m-Y'),
            ];
            if ($isLcOverdraftBankStatement) {
                $mapped['Limit'] = (float) ($row->limit ?? 0);
            }
            $mapped['Beginning Balance'] = (float) ($row->beginning_balance ?? 0);
            $mapped['Debit'] = (float) ($row->debit ?? 0);
            $mapped['Credit'] = (float) ($row->credit ?? 0);
            if ($isLcOverdraftBankStatement) {
                $mapped['Room'] = (float) ($row->room ?? 0);
            }
            $mapped['End Balance'] = (float) ($row->end_balance ?? 0);
            $mapped['Comment'] = isset($row->{'comment_'.$lang}) ? $row->{'comment_'.$lang} : '-';

            return $mapped;
        });

        $fileNameParts = [
            'LG-LC-Statement',
            $data['financialInstitutionName'],
            $data['letterOfCreditFacilityName'],
            strtoupper((string) $data['currencyName']),
        ];
        $fileName = preg_replace('/[^A-Za-z0-9\-]+/', '-', implode('-', array_filter($fileNameParts))).'.xlsx';

        return (new LgLcStatementExport($headings, $rows))->download($fileName);
    }
}
