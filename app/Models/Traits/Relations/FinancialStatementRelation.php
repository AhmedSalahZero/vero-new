<?php

namespace App\Models\Traits\Relations;

use App\Models\BalanceSheet;
use App\Models\CashFlowStatement;
use App\Models\FinancialStatementItem;
use App\Models\IncomeStatement;
use App\Models\SharingLink;
use App\Models\Traits\Relations\Commons\CommonRelations;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait FinancialStatementRelation
{
	use CommonRelations;

	public function sharingLinks()
	{
		return $this->morphMany(SharingLink::class, 'shareable');
	}
	public function mainItems(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatementItem::class,
			'financial_statement_item_main_item',
			'financial_statement_id',
			'financial_statement_item_id'
		);
	}

	public function incomeStatement()
	{
		return $this->hasOne(IncomeStatement::class, 'financial_statement_id', 'id');
	}
	public function cashFlowStatement()
	{
		return $this->hasOne(CashFlowStatement::class, 'financial_statement_id', 'id');
	}
	public function balanceSheet()
	{
		return $this->hasOne(BalanceSheet::class, 'financial_statement_id', 'id');
	}

	public function subItems(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatementItem::class,
			'financial_statement_main_item_sub_items',
			'financial_statement_id',
			'financial_statement_item_id'
		)->withPivot(['sub_item_name', 'payload', 'is_depreciation_or_amortization','total']);
	}
	public function withSubItemsFor(int $financialStatementItemId, string $subItemName = ''): BelongsToMany
	{
		$subItemOperator = $subItemName ? '=' : '!=';
		return $this->subItems()->wherePivot('sub_item_name', $subItemOperator, $subItemName)->where('financial_statement_item_id', $financialStatementItemId);
	}

	public function mainRows(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatementItem::class,
			'financial_statement_main_item_calculations',
			'financial_statement_id',
			'financial_statement_item_id'
		)

			->withPivot(['payload', 'company_id', 'creator_id']);
	}
	public function withMainRowsFor($financialStatementItemId): BelongsToMany
	{
		return $this->mainRows()->wherePivot('financial_statement_item_id', $financialStatementItemId);
	}
}
