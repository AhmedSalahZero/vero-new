<?php

namespace App\Models\Traits\Accessors;

use App\Models\BalanceSheet;
use Illuminate\Support\Collection;

trait BalanceSheetItemAccessor
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
	public function getSubItems(int $balanceSheetId, string $subItemType, string $subItemName = ''): Collection
	{
		return $this->withSubItemsFor($balanceSheetId, $subItemType, $subItemName)->get();
	}
	public function getSubItemsPivot(int $balanceSheetId, $subItemType): Collection
	{
		return $this->getSubItems($balanceSheetId, $subItemType)->pluck('pivot');
	}



	public function getMainRowsPivot(int $balanceSheetId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($balanceSheetId, $subItemType)->get()->pluck('pivot');
	}
	public function getMainRows(int $balanceSheetId, string $subItemType): Collection
	{
		return $this->withMainRowsPivotFor($balanceSheetId, $subItemType)->get();
	}

	public function getParentTableClassName(): string
	{
		return get_class(new BalanceSheet);
	}
	public function getParentTableName(): string
	{
		return (new BalanceSheet)->getTable();
	}
}
