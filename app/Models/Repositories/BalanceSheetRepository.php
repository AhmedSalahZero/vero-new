<?php

namespace App\Models\Repositories;

use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Repositories\IBaseRepository;
use App\Models\BalanceSheet;
use App\Models\BalanceSheetItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BalanceSheetRepository implements IBaseRepository
{

	public function all(): Collection
	{
		return BalanceSheet::withAllRelations()->onlyCurrentCompany()->get();
	}

	public function allFormatted(): array
	{
		return BalanceSheet::onlyCurrentCompany()->get()->pluck('name', 'id')->toArray();
	}
	public function allFormattedForSelect()
	{
		$balanceSheets = $this->all();
		return formatOptionsForSelect($balanceSheets, 'getId', 'getName');
	}

	public function getAllExcept($id): ?Collection
	{
		return BalanceSheet::onlyCurrentCompany()->where('id', '!=', $id)->get();
	}

	public function query(): Builder
	{
		return BalanceSheet::onlyCurrentCompany()->query();
	}
	public function Random(): Builder
	{
		return BalanceSheet::onlyCurrentCompany()->inRandomOrder();
	}

	public function find(?int $id): IBaseModel
	{
		return BalanceSheet::onlyCurrentCompany()->find($id);
	}

	public function getLatest($column = 'id'): ?BalanceSheet
	{
		return BalanceSheet::onlyCurrentCompany()->latest($column)->first();
	}
	public function store(Request $request): IBaseModel
	{
		$balanceSheet = App(BalanceSheet::class);

		$balanceSheet = $balanceSheet
			->storeMainSection($request)->storeMainItems($request);
		return $balanceSheet;
	}

	public function storeReport(Request $request): IBaseModel
	{
		$balanceSheet = new BalanceSheet();

		$balanceSheet
			->storeReport($request);

		return $balanceSheet;
	}

	public function update(IBaseModel $balanceSheet, Request $request): void
	{
		// $balanceSheet
		// 	->updateProfitability($request);
	}

	public function paginate(Request $request): array
	{

		$filterData = $this->commonScope($request);

		$allFilterDataCounter = $filterData->count();

		$datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function (BalanceSheet $balanceSheet, $index) {
			$balanceSheet->creator_name = $balanceSheet->getCreatorName();
			$balanceSheet->created_at_formatted = formatDateFromString($balanceSheet->created_at);
			$balanceSheet->updated_at_formatted = formatDateFromString($balanceSheet->updated_at);
			$balanceSheet->order = $index + 1;
		});
		return [
			'data' => $datePerPage,
			"draw" => (int)Request('draw'),
			"recordsTotal" => BalanceSheet::onlyCurrentCompany()->count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}

	public function paginateReport(Request $request, BalanceSheet $balanceSheet): array
	{

		$filterData = $this->commonScopeForReport($request, $balanceSheet);
		$subItemType = $request->get('sub_item_type');
		$allFilterDataCounter = $filterData->count();

		$dataWithRelations = collect([]);

		$datePerPage = $filterData->get()->each(function (BalanceSheetItem $balanceSheetItem, $index) use ($dataWithRelations, $balanceSheet, $subItemType) {
			$balanceSheetItem->creator_name = $balanceSheetItem->getCreatorName();
			$balanceSheetItem->created_at_formatted = formatDateFromString($balanceSheetItem->created_at);
			// $balanceSheetItem->updated_at_formatted = formatDateFromString($balanceSheetItem->updated_at);
			$balanceSheetItem->order = $index + 1;

			$balanceSheetItem['main_rows'] = $balanceSheetItem->getMainRows($balanceSheet->id, $subItemType);
			$dataWithRelations->add($balanceSheetItem);
			$balanceSheetItem->getSubItems($balanceSheet->id, $subItemType)->each(function ($subItem) use ($dataWithRelations, $balanceSheetItem) {
				$subItem->isSubItem = true; // isSubRow
				if ($balanceSheetItem->has_depreciation_or_amortization) {
					$subItem->pivot->can_be_depreciation = true;
				}
				$dataWithRelations->add($subItem);
			});
		});
		return [
			'data' => $dataWithRelations,
			"draw" => (int)Request('draw'),
			"recordsTotal" => BalanceSheetItem::count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}
	public function commonScope(Request $request): builder
	{
		return BalanceSheet::onlyCurrentCompany()->when($request->filled('search_input'), function (Builder $builder) use ($request) {

			$builder
				->where(function (Builder $builder) use ($request) {
					$builder->when($request->filled('search_input'), function (Builder $builder) use ($request) {
						$keyword = "%" . $request->get('search_input') . "%";
						$builder;
					});
				});
		})
			->orderBy('financial_statement_ables.' . getDefaultOrderBy()['column'], getDefaultOrderBy()['direction']);
	}

	public function commonScopeForReport(Request $request, BalanceSheet $balanceSheet): builder
	{
		$subItemType = $request->get('sub_item_type');

		return BalanceSheetItem::with(['subItems' => function ($builder) use ($balanceSheet, $subItemType) {
			$builder
				->wherePivot('financial_statement_able_id', $balanceSheet->id)
				->wherePivot('sub_item_type', $subItemType);
		}])
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($balanceSheet) {
			// 	$builder->where('financial_statement_ables.id', $balanceSheet->id);
			// })
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($balanceSheet) {
			// 	$builder->where('financial_statement_ables.id', $balanceSheet->id);
			// })
			->orderBy('financial_statement_able_items.id', 'asc');

		// return BalanceSheetItem::with(['subItems' => function ($builder) use ($balanceSheet) {
		// 	$builder->where('financial_statement_able_id', $balanceSheet->id);
		// }])->whereHas('financialStatementAbles', function (Builder $builder) use ($balanceSheet) {
		// 	$builder->where('financial_statement_ables.id', $balanceSheet->id);
		// })
		// 	->orderBy('financial_statement_able_items.id', 'asc');
	}
}
