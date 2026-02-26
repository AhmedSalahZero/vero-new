<?php

namespace App\Models\Traits\Accessors;

use Illuminate\Support\Collection;

trait FinancialStatementItemAccessor
{
	public function getId(): int
	{
		return $this->id;
	}
	public function getName(): string
	{
		return $this->name;
	}
	public function getCompanyId(): int
	{
		return $this->company->id ?? 0;
	}
	public function getCompanyName(): string
	{
		return $this->company->getName();
	}
	public function getCreatorName(): string
	{
		return $this->creator->name ?? __('N/A');
	}
	public function getSubItems(int $financialStatementId, string $subItemType, string $subItemName = ''): Collection
	{
		return $this->withSubItemsFor($financialStatementId, $subItemType, $subItemName)->wherePivot('financial_statement_id', $financialStatementId)->get();
	}
	public function getSubItemsPivot(int $financialStatementId, string $subItemType, string $subItemName = ''): Collection
	{
		return $this->getSubItems($financialStatementId, $subItemType, $subItemName)->pluck('pivot');
	}
	
	public function getMainRowsPivot(int $financialStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($financialStatementId, $subItemType)->get()->pluck('pivot');
	}
	public function getMainRows(int $financialStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($financialStatementId, $subItemType)->get();
	}
}
