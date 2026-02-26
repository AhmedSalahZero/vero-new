<?php

namespace App\Models\Traits\Relations;

use App\Models\FinancialStatement;
use App\Models\SharingLink;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait FinancialStatementItemRelation
{
	//    use CommonRelations  ;

	public function financialStatements(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatement::class,
			'financial_statement_item_main_item',
			'financial_statement_item_id',
			'financial_statement_id'
		);
	}
	public function subItems(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatement::class,
			'financial_statement_main_item_sub_items',
			'financial_statement_item_id',
			'financial_statement_id'
		);
	}
	public function withSubItemsFor(int $financialStatementId, string $subItemName = ''): BelongsToMany
	{
		$operator = $subItemName ? '=' : '!=';
		return $this->subItems()->wherePivot('financial_statement_id', $financialStatementId)
			->wherePivot('sub_item_name', $operator, $subItemName);
	}
	public function mainRowsPivot(): BelongsToMany
	{
		return $this->belongsToMany(
			FinancialStatement::class,
			'financial_statement_main_item_calculations',
			'financial_statement_item_id',
			'financial_statement_id'
		)
			->withPivot(['payload','total', 'company_id', 'creator_id']);
	}
	public function withMainRowsPivot(int $financialStatementId): BelongsToMany
	{
		return $this->mainRowsPivot()->wherePivot('financial_statement_id', $financialStatementId);
	}
}
