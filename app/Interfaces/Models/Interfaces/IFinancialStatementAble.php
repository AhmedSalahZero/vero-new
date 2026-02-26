<?php

namespace App\Interfaces\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface IFinancialStatementAble
{
	public function getMainItemTableClassName(): string;
	public function mainRows(): BelongsToMany;
	public function subItems(): BelongsToMany;
	public function mainItems(): BelongsToMany;
	public function FinancialStatement(): BelongsTo;
	public function withSubItemsFor(int $financialStatementAbleItemId, string $subItemType = '', string $subItemName = ''): BelongsToMany;
	public function canViewActualReport(): bool;
}
