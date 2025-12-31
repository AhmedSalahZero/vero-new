<?php

namespace App\Interfaces\Models\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


interface IFinancialStatementAbleItem
{
	public function financialStatementAbles(): BelongsToMany;
	public function subItems(): BelongsToMany;
	public function mainRowsPivot(): BelongsToMany;
	public function getParentTableClassName(): string;
	public function getParentTableName(): string;
	public function withSubItemsFor(int $financialStatementAbleId, string $subItemType = '', string $subItemName = ''): BelongsToMany;
}
