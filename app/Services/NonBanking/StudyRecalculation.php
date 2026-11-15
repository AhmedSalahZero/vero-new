<?php

namespace App\Services\NonBanking;

use App\Http\Controllers\NonBankingServices\IncomeStatementController;
use App\Models\Company;
use App\Models\NonBankingService\FixedAsset;
use App\Models\NonBankingService\Study;
use Illuminate\Http\Request;

/**
 * * إعادة حساب دراسة non-banking كأن اليوزر حفظ كل صفحاتها بالترتيب (الجولة 2: أمر non-banking:recalculate وأداة المقارنة
 * * بيستخدموا نفس الكود). كل خطوة هي اللي الـ store بتاع الصفحة بيعملها بعد ما يحفظ المدخلات:
 * *   1. الأرصدة الافتتاحية: الـ 13 جدول بتوع OpeningBalancesController::store (الـ saving hooks بتعيد بناء جداولهم) ،
 * *      وبعدين تحصيلاتها ومدفوعاتها بالشهر (OpeningBalancesStatements)
 * *   2. عدد حالات التمويل متناهي الصغر وعقوده لكل نوع فروع (صفحات All Branches و By Branch و New Branches) ، قبل القروض
 * *   3. القروض والإيرادات (recalculateAllRevenuesLoans) ، 4. الأصول الثابتة ، 5. المرتبات
 * *   6. مصروفات صفحة Expenses كلها (recalculateStudyExpenses ، بعد المرتبات عشان "لكل موظف" ، وبعد الإيرادات عشان "% من")
 * *      ومصروفات صفحات الفروع (recalculateBranchExpenses)
 * *   7. إيجار الفروع الجديدة وحق الانتفاع (صفحة New Branches) ، 8. قائمة الدخل والتدفقات والـ ODA ، والميزانية بتتقري منهم
 * * مثال: دراسة الأرصدة الافتتاحية فيها قرض طويل الأجل ، قبل كده الأمر مكانش بيعيد جدوله (4 جداول بس من 13).
 */
final class StudyRecalculation
{
	/** OpeningBalancesController::store, in its order */
	public const OPENING_BALANCE_RELATIONS = [
		'fixedAssetOpeningBalances', 'cashAndBankOpeningBalances', 'otherDebtorsOpeningBalances', 'vatAndCreditWithholdTaxesOpeningBalances',
		'supplierPayableOpeningBalances', 'rightOfUseAssetOpeningBalances', 'leaseRentLiabilityOpeningBalances', 'otherCreditorsOpeningBalances',
		'otherLongTermAssetsOpeningBalances', 'longTermInvestmentsOpeningBalances', 'otherLongTermLiabilitiesOpeningBalances', 'equityOpeningBalances',
		'longTermLoanOpeningBalances',
	];

	public static function run(int $studyId, Company $company): void
	{
		// 1. the opening balances, as their page's Save does: every row saved again (the model hooks rebuild their statements)
		$study = Study::findOrFail($studyId);
		foreach (self::OPENING_BALANCE_RELATIONS as $relation) {
			foreach ($study->{$relation}()->get() as $openingBalance) {
				$openingBalance->save();
			}
		}
		OpeningBalancesStatements::refresh(Study::findOrFail($studyId));

		// 2. the microfinance cases and contracts of each branch type with products, as the microfinance pages' Save does
		$study = Study::findOrFail($studyId);
		foreach ($study->microfinanceProductSalesProjects()->distinct()->pluck('type') as $branchType) {
			Study::findOrFail($studyId)->recalculateMicrofinanceTotalCasesCounts((string) $branchType);
		}

		// 3. the loans, revenues and the expenses that follow them
		Study::findOrFail($studyId)->recalculateAllRevenuesLoans(new Request());
		// 4. the fixed assets and 5. the manpower
		foreach ([FixedAsset::FFE, FixedAsset::PER_EMPLOYEE, FixedAsset::NEW_BRANCH] as $type) {
			Study::findOrFail($studyId)->recalculateFixedAssets($type);
		}
		Study::findOrFail($studyId)->recalculateManpower();
		// 6. every Expenses-page row from its own dates (the loans step only redoes % of sales and cost per unit): a
		// study that was extended kept the amounts of its old dates (study 154: an expense ending 12/2027 had 2028)
		Study::findOrFail($studyId)->recalculateStudyExpenses();
		// 6b. the microfinance branch pages' expenses (All / By / New Branches), from their saved rows: the command never
		// redid them, and after a study is extended they run to its new end
		Study::findOrFail($studyId)->recalculateBranchExpenses();

		// 7. the new branches' rent and right of use, as the New Branches page's Save does (only a study with new branches)
		$study = Study::findOrFail($studyId);
		if ($study->newBranchMicrofinanceOpeningProjections()->exists() && $study->incomeStatementReport && $study->cashflowStatementReport) {
			$study->calculateRightOfUseRentAndStatement();
		}

		// 8. income statement (it runs the cash flow first), cash flow and ODA; the balance sheet is read from them
		(new IncomeStatementController())->index($company, Study::findOrFail($studyId), true);
		$study = Study::findOrFail($studyId);
		$study->cashFlowForOdas($study->getCashInOutFlowViewVars()['netCashBeforeWorking']);
	}
}
