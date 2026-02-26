<?php

namespace App\Models\Traits\Accessors;

use App\Models\CashFlowStatement;
use Illuminate\Support\Collection;

trait CashFlowStatementItemAccessor
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
	public function getSubItems(int $cashFlowStatementId, string $subItemType, string $subItemName = ''): Collection
	{
		return $this->withSubItemsFor($cashFlowStatementId, $subItemType, $subItemName)->get();
	}
	public function getSubItemsPivot(int $cashFlowStatementId, $subItemType): Collection
	{
		return $this->getSubItems($cashFlowStatementId, $subItemType)->pluck('pivot');
	}



	public function getMainRowsPivot(int $cashFlowStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($cashFlowStatementId, $subItemType)->get()->pluck('pivot');
	}
	public function getMainRows(int $cashFlowStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($cashFlowStatementId, $subItemType)->get();
	}

	public function getParentTableClassName(): string
	{
		return get_class(new CashFlowStatement);
	}
	public function getParentTableName(): string
	{
		return (new CashFlowStatement)->getTable();
	}
}
