<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\IncomeStatementSubItem
 *
 * @property int $id
 * @property string|null $vat_rate
 * @property int|null $is_deductible
 * @property string|null $is_value_quantity_price
 * @property int $financial_statement_able_id
 * @property int $financial_statement_able_item_id
 * @property string|null $sub_item_name
 * @property string $sub_item_type
 * @property string|null $receivable_or_payment
 * @property int $ordered
 * @property string $created_from
 * @property string|null $payload
 * @property float $total
 * @property string|null $actual_dates
 * @property bool|null $is_depreciation_or_amortization
 * @property int $has_collection_policy
 * @property string|null $collection_policy_type
 * @property string|null $collection_policy_value
 * @property bool|null $is_quantity
 * @property bool $can_be_quantity
 * @property int $can_be_percentage_or_fixed
 * @property int $company_id
 * @property string $percentage_or_fixed
 * @property string|null $is_percentage_of
 * @property string|null $repeating_fixed_value
 * @property int|null $creator_id
 * @property string|null $percentage_value
 * @property string|null $is_cost_of_unit_of
 * @property string|null $cost_of_unit_value
 * @property bool|null $is_financial_expense
 * @property string|null $is_financial_income
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereVatRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsDeductible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsValueQuantityPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereFinancialStatementAbleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereFinancialStatementAbleItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereSubItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereSubItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereReceivableOrPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereOrdered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCreatedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereActualDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsDepreciationOrAmortization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereHasCollectionPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCollectionPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCollectionPolicyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCanBeQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCanBePercentageOrFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem wherePercentageOrFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsPercentageOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereRepeatingFixedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem wherePercentageValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsCostOfUnitOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCostOfUnitValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsFinancialExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereIsFinancialIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatementSubItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeStatementSubItem extends Pivot
{
    protected $table = 'financial_statement_able_main_item_sub_items';
  
	public $incrementing = true;
}
