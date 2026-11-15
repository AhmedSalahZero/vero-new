<?php

namespace App\Services\NonBanking;

use App\Models\NonBankingService\ConsumerfinanceProductSalesProject;
use App\Models\NonBankingService\DirectFactoringBreakdown;
use App\Models\NonBankingService\DirectFactoringRevenueProjectionByCategory;
use App\Models\NonBankingService\EclAndNewPortfolioFundingRate;
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\IjaraMortgageBreakdown;
use App\Models\NonBankingService\IjaraMortgageRevenueProjectionByCategory;
use App\Models\NonBankingService\LeasingRevenueStreamBreakdown;
use App\Models\NonBankingService\MicrofinanceByBranchProductMix;
use App\Models\NonBankingService\MicrofinanceLoanOfficerCasesProjection;
use App\Models\NonBankingService\MicrofinanceProductSalesProject;
use App\Models\NonBankingService\ReverseFactoringBreakdown;
use App\Models\NonBankingService\ReverseFactoringRevenueProjectionByCategory;
use App\Models\NonBankingService\Study;
use Illuminate\Database\Eloquent\Model;

/**
 * * الدراسة اتطوّلت (سنة → 3 سنين ، زي دراسة 154): الشهور والسنين الجديدة مالهاش مدخلات محفوظة ، فكانت بتتحسب صفر.
 * * قرار اليوزر: النسب والأسعار والسياسات وحجم الشغل الشهري بتاخد آخر قيمة محفوظة. الأحداث مابتتكررش وبتفضل صفر:
 * * التعيينات ، فتح الفروع ، شراء الأصول ، المصروف اللي بيتدفع مرة ، ضخ رأس المال ، شراء المحافظ والتوريق.
 * * مصروفات الفروع اللي كانت لآخر الدراسة القديمة بتكمّل لآخرها الجديد ، وإيجار الفروع بيغطي الدراسة كلها.
 * * صفوف صفحة Expenses بتفضل عند تاريخ نهايتها (قرار اليوزر في دراسة 154).
 * * بعد كده الدراسة كلها بتتحسب تاني (StudyRecalculation).
 */
final class StudyExtension
{
	/** month series of each model that carry their last value (the user's "levels", plus the monthly business volume) */
	private const MONTH_SERIES = [
		MicrofinanceProductSalesProject::class => ['product_mixes', 'flat_rates', 'fees_rates', 'setup_fees_durations'],
		MicrofinanceByBranchProductMix::class => ['flat_rates'],
		ConsumerfinanceProductSalesProject::class => ['flat_rates', 'loan_amounts'],
		LeasingRevenueStreamBreakdown::class => ['loan_amounts'],
		DirectFactoringRevenueProjectionByCategory::class => ['direct_factoring_transactions_projections'],
		DirectFactoringBreakdown::class => ['percentage_payload', 'loan_amounts'],
		ReverseFactoringRevenueProjectionByCategory::class => ['reverse_factoring_transactions_projections'],
		ReverseFactoringBreakdown::class => ['percentage_payload', 'loan_amounts'],
		IjaraMortgageRevenueProjectionByCategory::class => ['ijara_mortgage_transactions_projections'],
		IjaraMortgageBreakdown::class => ['percentage_payload', 'loan_amounts'],
		// the funding values are the month's loans × its funding rate (useNewLoanFundingCalculator.js): with the loans and
		// the rate repeated, the value repeats too
		EclAndNewPortfolioFundingRate::class => ['admin_fees_rates', 'ecl_rates', 'equity_funding_rates', 'new_loans_funding_rates', 'equity_funding_values', 'new_loans_funding_values'],
	];

	/** year series (keyed by the study's year index) of each model */
	private const YEAR_SERIES = [
		MicrofinanceProductSalesProject::class => ['increase_rates'],
		MicrofinanceByBranchProductMix::class => ['increase_rates'],
	];

	private const GENERAL_ASSUMPTION_MONTH_SERIES = [
		'cbe_base_lending_corridor_rates', 'cbe_corridor_changes_rates', 'cbe_lending_corridor_rates', 'bank_lending_margin_rates',
		'odas_bank_lending_margin_rates', 'credit_interest_rate_for_surplus_cash', 'from_dispersement_of_rates', 'to_cover_cost_rates',
		'min_cash_balances',
	];

	private const GENERAL_ASSUMPTION_YEAR_SERIES = [
		'employee_profit_share_rates', 'border_of_directors_profit_share_rates', 'shareholders_first_dividend_portions',
		'shareholders_dividend_payout_ratios', 'shareholders_dividend_in_cash_or_shares',
	];

	/**
	 * @param  Study  $study  the study with its new dates (same start, a later end), dates bound
	 * @param  int  $oldEndIndex  the month index of the study end before the change
	 */
	public static function carryInputsForward(Study $study, int $oldEndIndex): void
	{
		$newEndIndex = $study->getViewStudyEndDateAsIndex();
		if ($newEndIndex <= $oldEndIndex) {
			return;
		}
		$lastYearIndex = (int) ($study->getDatesIndexWithYearIndex()[$newEndIndex] ?? 0);

		foreach (self::MONTH_SERIES as $modelClass => $columns) {
			foreach ($modelClass::where('study_id', $study->id)->get() as $row) {
				// the models' saving hooks rebuild what they derive from these (monthly mixes, decreasing rates …)
				self::carryColumns($row, $columns, $newEndIndex);
				self::carryColumns($row, self::YEAR_SERIES[$modelClass] ?? [], $lastYearIndex);
				$row->save();
			}
		}
		// the officers' cases: their saving hook repeats the cases to the study end and rebuilds the totals; the hiring stays
		foreach (MicrofinanceLoanOfficerCasesProjection::where('study_id', $study->id)->get() as $row) {
			$row->save();
		}

		$generalAssumption = $study->generalAndReserveAssumption;
		if ($generalAssumption) {
			self::carryColumns($generalAssumption, self::GENERAL_ASSUMPTION_MONTH_SERIES, $newEndIndex);
			self::carryColumns($generalAssumption, self::GENERAL_ASSUMPTION_YEAR_SERIES, $lastYearIndex);
			$generalAssumption->saveQuietly();   // its hook would recalculate the loans now: the whole study is recalculated next
		}

		foreach (Expense::where('study_id', $study->id)->where('model_name', 'Study')->where('model_id', $study->id)->get() as $expense) {
			// the increase of year n is kept at n − 1 (Expense::getIncreaseRateAtYearIndex)
			self::carryColumns($expense, ['increase_rates'], $lastYearIndex - 1);
			if (in_array($expense->expense_type, Study::BRANCH_EXPENSE_TYPES, true) && (int) $expense->end_date === $oldEndIndex) {
				$expense->end_date = $newEndIndex;
			}
			$expense->save();
		}

		self::rentCoversTheStudy($study);
	}

	/**
	 * The series with every key after its last saved key, up to $toKey, set to the last value that is not empty.
	 * Saved keys are never changed. A nested series (e.g. a margin per business type) is carried per sub-series.
	 * Example: [8 => 10, 11 => 40] to 13 → [8 => 10, 11 => 40, 12 => 40, 13 => 40].
	 */
	public static function carryForward(?array $series, int $toKey): ?array
	{
		if (! $series) {
			return $series;
		}
		$values = array_values($series);
		if (count(array_filter($values, 'is_array')) === count($values)) {
			return array_map(fn (array $subSeries) => self::carryForward($subSeries, $toKey), $series);
		}
		$keys = array_map('intval', array_filter(array_keys($series), 'is_numeric'));
		if (! $keys) {
			return $series;
		}
		$lastValue = null;
		$sortedKeys = $keys;
		rsort($sortedKeys);
		foreach ($sortedKeys as $key) {
			if ($series[$key] !== null && $series[$key] !== '') {
				$lastValue = $series[$key];
				break;
			}
		}
		if ($lastValue === null) {
			return $series;
		}
		for ($key = max($keys) + 1; $key <= $toKey; $key++) {
			$series[$key] = $lastValue;
		}

		return $series;
	}

	private static function carryColumns(Model $row, array $columns, int $toKey): void
	{
		foreach ($columns as $column) {
			$series = $row->{$column};
			if (is_array($series) && $series) {
				$row->{$column} = self::carryForward($series, $toKey);
			}
		}
	}

	/** the new branches' rent runs at least as long as the study (the New Branches page asks the same) */
	private static function rentCoversTheStudy(Study $study): void
	{
		$rent = $study->right_of_use_rent;
		if (! is_array($rent) || ! isset($rent['duration'])) {
			return;
		}
		$studyMonths = (int) $study->duration_in_years * 12;
		if ((int) $rent['duration'] < $studyMonths) {
			$rent['duration'] = (string) $studyMonths;
			$study->right_of_use_rent = $rent;
			$study->saveQuietly();
		}
	}
}
