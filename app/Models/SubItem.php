<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string|null $vat_rate
 * @property int|null $is_deductible
 * @property string|null $is_value_quantity_price
 * @property int $financial_statement_able_id
 * @property int $financial_statement_able_item_id
 * @property string|null $sub_item_name when null it stores the main row data that has no sub rows
 * @property string $sub_item_type
 * @property string|null $receivable_or_payment
 * @property int $ordered
 * @property string $created_from
 * @property string|null $payload
 * @property numeric $total
 * @property string|null $actual_dates
 * @property int|null $is_depreciation_or_amortization
 * @property int $has_collection_policy
 * @property string|null $collection_policy_type
 * @property string|null $collection_policy_value
 * @property int|null $is_quantity
 * @property int $can_be_quantity
 * @property int $can_be_percentage_or_fixed
 * @property int $company_id
 * @property string $percentage_or_fixed
 * @property string|null $is_percentage_of
 * @property string|null $repeating_fixed_value
 * @property int|null $creator_id
 * @property string|null $percentage_value
 * @property string|null $is_cost_of_unit_of
 * @property string|null $cost_of_unit_value
 * @property int|null $is_financial_expense
 * @property string|null $is_financial_income
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereActualDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCanBePercentageOrFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCanBeQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCollectionPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCollectionPolicyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCostOfUnitValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCreatedFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereFinancialStatementAbleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereFinancialStatementAbleItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereHasCollectionPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsCostOfUnitOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsDeductible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsDepreciationOrAmortization($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsFinancialExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsFinancialIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsPercentageOf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereIsValueQuantityPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereOrdered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem wherePercentageOrFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem wherePercentageValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereReceivableOrPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereRepeatingFixedValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereSubItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereSubItemType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SubItem whereVatRate($value)
 * @mixin \Eloquent
 */
class SubItem extends Model
{
	protected $table = 'financial_statement_able_main_item_sub_items';
	

}
