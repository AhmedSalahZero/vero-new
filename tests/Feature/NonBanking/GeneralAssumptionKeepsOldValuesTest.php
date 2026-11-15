<?php

namespace Tests\Feature\NonBanking;

use App\Models\NonBankingService\GeneralAndReserveAssumption;
use App\Models\NonBankingService\Study;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * * شكوى العميل: "لما بعدّل الكوريدور مش بيحتفظ بالداتا القديمة".
 *
 * * صفحة General Assumptions بتعرض شهور التشغيل بس. قبل الإصلاح:
 * * - الحفظ كان بيستبدل كل عمود بالشهور الظاهرة ، فالشهور التانية بتتمسح.
 * * - الصفوف القديمة (هامش واحد لكل التمويل ، الكوريدور الصافي من غير أساس) كانت بتظهر أصفار ، وأول حفظ بيمسحها.
 * * - الشهر اللي مالوش قيمة (بعد تطويل المدة) كان بيوقع الحساب.
 * * - تغيير تاريخ بداية الدراسة كان بيزحلق الأسعار على شهور تانية.
 *
 * * الدراسة المستخدمة: Vero 140 "Maak Outlook" (بداية يناير 2026 ، التشغيل من سبتمبر 2026 ، الكوريدور متسجل لسبتمبر–ديسمبر).
 */
class GeneralAssumptionKeepsOldValuesTest extends NonBankingTestCase
{
	private function admin(Study $study): User
	{
		return $this->makeUser(User::SUPER_ADMIN, [$this->companyOf($study)]);
	}

	private function oldData(Study $study): array
	{
		// the old-data route has no name: it is the store route + "-old-data"
		$url = route('non.banking.store.general.assumption', ['company' => $study->company_id, 'study' => $study->id]).'-old-data';

		return $this->actAs($this->admin($study))
			->getJson($url)
			->assertOk()
			->json();
	}

	/** Posts the page model back exactly as the Vue page does (only the months the page shows). */
	private function saveFromPage(Study $study, array $model): void
	{
		$this->actAs($this->admin($study))
			->postJson(route('non.banking.store.general.assumption', ['company' => $study->company_id, 'study' => $study->id]), $model + ['submit_button' => 'save'])
			->assertOk();
	}

	private function storedGeneralAssumption(Study $study): GeneralAndReserveAssumption
	{
		return GeneralAndReserveAssumption::where('study_id', $study->id)->firstOrFail();
	}

	/** Same steps as StudyController::store after a study is edited. */
	private function rebuildStudyDates(Study $study): Study
	{
		$study = $study->fresh();
		$helpers = $study->datesAndIndexesHelpers($study->getStudyDates());
		$study->updateStudyAndOperationDates($study->getDatesAsStringAndIndex(), $helpers['datesIndexWithYearIndex'], $helpers['yearIndexWithYear'], $helpers['dateIndexWithDate'], $helpers['dateWithMonthNumber']);

		return $study->fresh();
	}

	public function test_saving_the_page_keeps_the_months_it_does_not_show(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->storedGeneralAssumption($study);
		$this->assertEqualsCanonicalizing([8, 9, 10, 11], array_keys($before->cbe_lending_corridor_rates));

		// the page shows November and December only; the user lowers the corridor by 1% there
		$model = $this->oldData($study)['model'];
		$shown = [10 => true, 11 => true];
		foreach (['cbe_base_lending_corridor_rates', 'cbe_corridor_changes_rates', 'cbe_lending_corridor_rates', 'credit_interest_rate_for_surplus_cash', 'min_cash_balances', 'odas_bank_lending_margin_rates'] as $column) {
			$model[$column] = array_intersect_key($model[$column], $shown);
		}
		$model['cbe_base_lending_corridor_rates'] = [10 => 26.25, 11 => 26.25];
		$model['cbe_lending_corridor_rates'] = [10 => 26.25, 11 => 26.25];
		foreach ($model['bank_lending_margin_rates'] as $type => $series) {
			$model['bank_lending_margin_rates'][$type] = array_intersect_key($series, $shown);
		}
		$this->saveFromPage($study, $model);

		$after = $this->storedGeneralAssumption($study);
		$this->assertEquals($before->cbe_lending_corridor_rates[8], $after->cbe_lending_corridor_rates[8]);
		$this->assertEquals($before->cbe_lending_corridor_rates[9], $after->cbe_lending_corridor_rates[9]);
		$this->assertEquals(26.25, $after->cbe_lending_corridor_rates[10]);
		$this->assertEquals($before->min_cash_balances[8], $after->min_cash_balances[8]);
		$this->assertEquals($before->bank_lending_margin_rates['microfinance'][9], $after->bank_lending_margin_rates['microfinance'][9]);
	}

	public function test_moving_the_operation_start_later_and_back_brings_the_old_corridor_back(): void
	{
		$study = $this->loadStudyFixture('study_140');
		// study 140 has a 0% corridor: give September–December a known one first
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_base_lending_corridor_rates' => json_encode(['8' => 27.25, '9' => 27.25, '10' => 26.25, '11' => 26.25]),
			'cbe_corridor_changes_rates' => json_encode(['8' => 0, '9' => 0, '10' => 0, '11' => 0]),
			'cbe_lending_corridor_rates' => json_encode(['8' => 27.25, '9' => 27.25, '10' => 26.25, '11' => 26.25]),
		]);
		$september = $this->storedGeneralAssumption($study)->cbe_lending_corridor_rates[8];
		$this->assertEquals(27.25, $september);

		// operation start moves from September to November: the page shows November and December only, and is saved
		$study->update(['operation_start_date' => '2026-11-01']);
		$study = $this->rebuildStudyDates($study);
		$page = $this->oldData($study);
		$this->assertSame(['10', '11'], array_map('strval', array_keys($page['model']['cbe_lending_corridor_rates'])));
		$this->saveFromPage($study, $page['model']);

		// back to September: September and October still show their saved corridor, not 0
		$study->update(['operation_start_date' => '2026-09-01']);
		$study = $this->rebuildStudyDates($study);
		$page = $this->oldData($study);
		$this->assertEquals($september, $page['model']['cbe_lending_corridor_rates'][8]);
		$this->assertEquals($september, $page['model']['cbe_base_lending_corridor_rates'][8] + $page['model']['cbe_corridor_changes_rates'][8]);
	}

	public function test_an_old_row_shows_its_real_corridor_and_margin_and_saving_keeps_them(): void
	{
		$study = $this->loadStudyFixture('study_140');
		// the shape the old page saved: the net corridor only, one margin for every kind of lending
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_lending_corridor_rates' => json_encode(['8' => 27.25, '9' => 27.25, '10' => 26.25, '11' => 26.25]),
			'cbe_base_lending_corridor_rates' => null,
			'cbe_corridor_changes_rates' => null,
			'bank_lending_margin_rates' => json_encode(['8' => 2.5, '9' => 2.5, '10' => 2.5, '11' => 2.5]),
		]);

		$model = $this->oldData($study)['model'];
		$this->assertEquals(27.25, $model['cbe_base_lending_corridor_rates'][8]);   // not 0: base = net − changes
		$this->assertEquals(2.5, $model['bank_lending_margin_rates']['leasing'][8]);
		$this->assertEquals(2.5, $model['bank_lending_margin_rates']['microfinance'][11]);

		// the page computes net = base + changes and saves it: the loans must not be repriced at a 0% corridor
		$model['cbe_lending_corridor_rates'] = [];
		foreach ($model['cbe_base_lending_corridor_rates'] as $index => $base) {
			$model['cbe_lending_corridor_rates'][$index] = $base + $model['cbe_corridor_changes_rates'][$index];
		}
		$this->saveFromPage($study, $model);

		$after = $this->storedGeneralAssumption($study);
		$this->assertEquals(26.25, $after->cbe_lending_corridor_rates[10]);
		$this->assertEquals(2.5, $after->getBankLendingMarginRates('factoring')[9]);
	}

	public function test_the_legacy_migration_upgrades_old_rows_and_keeps_their_original_values(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$legacyMargins = json_encode(['8' => 2.5, '9' => 2.5, '10' => 2.5, '11' => 2.5]);
		$nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_lending_corridor_rates' => json_encode(['8' => 27.25, '9' => 27.25, '10' => 26.25, '11' => 26.25]),
			'cbe_base_lending_corridor_rates' => null,
			'cbe_corridor_changes_rates' => json_encode(['10' => -1]),
			'bank_lending_margin_rates' => $legacyMargins,
		]);
		$gaId = (int) $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->value('id');

		(require base_path('database/migrations/non_banking_service/2026_11_15_000001_upgrade_legacy_general_assumption_shapes.php'))->up();

		$row = $nb->table('general_and_reserve_assumptions')->where('id', $gaId)->first();
		$margins = json_decode($row->bank_lending_margin_rates, true);
		$this->assertEquals(array_fill_keys(GeneralAndReserveAssumption::getBankLendingMarginTypes(), [8 => 2.5, 9 => 2.5, 10 => 2.5, 11 => 2.5]), $margins);
		$this->assertEquals([8 => 27.25, 9 => 27.25, 10 => 27.25, 11 => 26.25], json_decode($row->cbe_base_lending_corridor_rates, true));
		$this->assertSame($legacyMargins, $nb->table('general_and_reserve_assumptions_legacy_backups')
			->where('general_and_reserve_assumption_id', $gaId)->where('column_name', 'bank_lending_margin_rates')->value('original_value'));

		// running it again changes nothing (the rows are in the current shape now)
		(require base_path('database/migrations/non_banking_service/2026_11_15_000001_upgrade_legacy_general_assumption_shapes.php'))->up();
		$this->assertSame(2, $nb->table('general_and_reserve_assumptions_legacy_backups')->where('general_and_reserve_assumption_id', $gaId)->count());
	}

	public function test_the_migration_adds_the_corridor_changes_column_and_brings_back_the_last_saved_change(): void
	{
		// production has no cbe_corridor_changes_rates column, so the change typed on the page was never saved;
		// the net corridor saved with it still holds it: net 30% − base 20% = change +10%
		$this->assertTrue(\Illuminate\Support\Facades\Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)
			->hasColumn('general_and_reserve_assumptions', 'cbe_corridor_changes_rates'));

		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_base_lending_corridor_rates' => json_encode(['8' => 20, '9' => 20, '10' => 20, '11' => 20]),
			'cbe_lending_corridor_rates' => json_encode(['8' => 20, '9' => 20, '10' => 30, '11' => 30]),
			'cbe_corridor_changes_rates' => null,
		]);

		(require base_path('database/migrations/non_banking_service/2026_11_15_000001_upgrade_legacy_general_assumption_shapes.php'))->up();

		$row = $nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->first();
		$this->assertEquals([8 => 0, 9 => 0, 10 => 10, 11 => 10], json_decode($row->cbe_corridor_changes_rates, true));
		$this->assertEquals([8 => 20, 9 => 20, 10 => 30, 11 => 30], json_decode($row->cbe_lending_corridor_rates, true));

		// and the page shows it again: 20% + 10% = 30% from November
		$model = $this->oldData($study)['model'];
		$this->assertEquals(10, $model['cbe_corridor_changes_rates'][10]);
		$this->assertEquals(30, $model['cbe_base_lending_corridor_rates'][10] + $model['cbe_corridor_changes_rates'][10]);
	}

	public function test_a_corridor_change_moves_the_opening_portfolio_interest_by_one_twelfth_of_the_yearly_change_a_month(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		// the corridor falls 1% from December 2026
		$nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update([
			'cbe_corridor_changes_rates' => json_encode(['8' => 0, '9' => 0, '10' => 0, '11' => -1]),
		]);
		$cashAndBank = $study->fresh()->getCashAndBank();
		$this->assertNotNull($cashAndBank);
		$cashAndBank->save();

		$opening = $cashAndBank->fresh()->statement['monthly']['beginning_balance'][11] ?? null;
		$this->assertNotNull($opening);
		$effect = json_decode($nb->table('income_statement_reports')->where('study_id', $study->id)->value('interest_corridor_changes'), true);
		// 1% a year on the December opening balance, for one month: balance × 1% ÷ 12
		$this->assertEqualsWithDelta(-$opening * 0.01 / 12, $effect[11], 0.01);
	}

	public function test_a_month_with_no_saved_corridor_takes_the_month_before_instead_of_crashing(): void
	{
		$this->assertSame(26.0, GeneralAndReserveAssumption::valueAtOrBefore([8 => 27.25, 9 => '27.25', 10 => 26, 11 => 26], 14));
		$this->assertSame(27.25, GeneralAndReserveAssumption::valueAtOrBefore([8 => 27.25, 10 => 26], 9));
		$this->assertSame(0.0, GeneralAndReserveAssumption::valueAtOrBefore([8 => 27.25], 3));

		$study = $this->loadStudyFixture('study_140');
		// December lost its value (e.g. the study was extended and the page not saved yet)
		$nb = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME);
		$net = json_decode($nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->value('cbe_lending_corridor_rates'), true);
		$november = $net[10];
		unset($net[11]);
		$nb->table('general_and_reserve_assumptions')->where('study_id', $study->id)->update(['cbe_lending_corridor_rates' => json_encode($net)]);

		$rates = $this->storedGeneralAssumption($study)->getBaseRatesPerMonths(GeneralAndReserveAssumption::MICROFINANCE_BANK_LENDING_MARGIN_RATE);
		$rates = is_array($rates) ? $rates : ['2026-12-01' => $rates];
		$this->assertEquals((float) $november, (float) $rates['2026-12-01'] ?? null);
	}

	public function test_moving_the_study_start_keeps_the_corridor_on_its_calendar_months(): void
	{
		$study = $this->loadStudyFixture('study_140');
		$before = $this->storedGeneralAssumption($study);
		$september2026 = $before->cbe_lending_corridor_rates[8];
		$leasingMarginDecember2026 = $before->getBankLendingMarginRates('leasing')[11];

		// the study now starts one year earlier (January 2025) and runs two years; operations still start in September 2026
		$this->actAs($this->admin($study))
			->putJson(route('non.banking.update.study', ['company' => $study->company_id, 'study' => $study->id]), [
				'name' => $study->name,
				'study_start_date' => '2025-01',
				'study_end_date' => '2026-12-01',
				'duration_in_years' => 2,
				'operation_start_date' => '2026-09',
				'has_micro_finance' => 1,
			])
			->assertOk();

		$after = $this->storedGeneralAssumption($study);
		$this->assertEquals($september2026, $after->cbe_lending_corridor_rates[20]);   // September 2026 = index 20 now
		$this->assertEquals($leasingMarginDecember2026, $after->getBankLendingMarginRates('leasing')[23]);
		$this->assertSame('2025-01-01', app('dateIndexWithDate')[0]);   // the loans were recalculated on the new dates
	}
}
