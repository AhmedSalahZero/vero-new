<?php

namespace App\Models\Traits\Accessors;

use App\Models\IncomeStatement;
use Illuminate\Support\Collection;

trait IncomeStatementItemAccessor
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
	public function getSubItems(int $incomeStatementId, string $subItemType, string $subItemName = ''): Collection
	{
		return $this->withSubItemsFor($incomeStatementId, $subItemType, $subItemName)->get();
	}
	public function getSubItemsPivot(int $incomeStatementId, $subItemType): Collection
	{
		return $this->getSubItems($incomeStatementId, $subItemType)->pluck('pivot');
	}



	public function getMainRowsPivot(int $incomeStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($incomeStatementId, $subItemType)->get()->pluck('pivot');
	}
	public function getMainRows(int $incomeStatementId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($incomeStatementId, $subItemType)->get();
	}

	public function getParentTableClassName(): string
	{
		return get_class(new IncomeStatement);
	}
	public function getParentTableName(): string
	{
		return (new IncomeStatement)->getTable();
	}
}
