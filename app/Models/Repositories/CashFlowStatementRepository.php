<?php

namespace App\Models\Repositories;

use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Repositories\IBaseRepository;
use App\Models\CashFlowStatement;
use App\Models\CashFlowStatementItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CashFlowStatementRepository implements IBaseRepository
{

	public function all(): Collection
	{
		return CashFlowStatement::withAllRelations()->onlyCurrentCompany()->get();
	}

	public function allFormatted(): array
	{
		return CashFlowStatement::onlyCurrentCompany()->get()->pluck('name', 'id')->toArray();
	}
	public function allFormattedForSelect()
	{
		$cashFlowStatements = $this->all();
		return formatOptionsForSelect($cashFlowStatements, 'getId', 'getName');
	}

	public function getAllExcept($id): ?Collection
	{
		return CashFlowStatement::onlyCurrentCompany()->where('id', '!=', $id)->get();
	}

	public function query(): Builder
	{
		return CashFlowStatement::onlyCurrentCompany()->query();
	}
	public function Random(): Builder
	{
		return CashFlowStatement::onlyCurrentCompany()->inRandomOrder();
	}

	public function find(?int $id): IBaseModel
	{
		return CashFlowStatement::onlyCurrentCompany()->find($id);
	}

	public function getLatest($column = 'id'): ?CashFlowStatement
	{
		return CashFlowStatement::onlyCurrentCompany()->latest($column)->first();
	}
	public function store(Request $request): IBaseModel
	{
		$cashFlowStatement = App(CashFlowStatement::class);

		$cashFlowStatement = $cashFlowStatement
			->storeMainSection($request)->storeMainItems($request);
		return $cashFlowStatement;
	}

	public function storeReport(Request $request): IBaseModel
	{
		$cashFlowStatement = new CashFlowStatement();
		$cashFlowStatement = $cashFlowStatement->storeReport($request);
		return $cashFlowStatement;
	}

	public function update(IBaseModel $cashFlowStatement, Request $request): void
	{
		// $cashFlowStatement
		// 	->updateProfitability($request);
	}

	public function paginate(Request $request): array
	{

		$filterData = $this->commonScope($request);

		$allFilterDataCounter = $filterData->count();

		$datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function (CashFlowStatement $cashFlowStatement, $index) {
			$cashFlowStatement->creator_name = $cashFlowStatement->getCreatorName();
			$cashFlowStatement->created_at_formatted = formatDateFromString($cashFlowStatement->created_at);
			$cashFlowStatement->updated_at_formatted = formatDateFromString($cashFlowStatement->updated_at);
			$cashFlowStatement->order = $index + 1;
		});
		return [
			'data' => $datePerPage,
			"draw" => (int)Request('draw'),
			"recordsTotal" => CashFlowStatement::onlyCurrentCompany()->count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}

	public function paginateReport(Request $request, CashFlowStatement $cashFlowStatement): array
	{

		$filterData = $this->commonScopeForReport($request, $cashFlowStatement);
		$subItemType = $request->get('sub_item_type');
		$allFilterDataCounter = $filterData->count();

		$dataWithRelations = collect([]);

		$datePerPage = $filterData->get()->each(function (CashFlowStatementItem $cashFlowStatementItem, $index) use ($dataWithRelations, $cashFlowStatement, $subItemType) {
			$cashFlowStatementItem->creator_name = $cashFlowStatementItem->getCreatorName();
			$cashFlowStatementItem->created_at_formatted = formatDateFromString($cashFlowStatementItem->created_at);
			$cashFlowStatementItem->updated_at_formatted = formatDateFromString($cashFlowStatementItem->updated_at);
			$cashFlowStatementItem->order = $index + 1;
			$cashFlowStatementItem['main_rows'] = $cashFlowStatementItem->getMainRows($cashFlowStatement->id, $subItemType);
			$dataWithRelations->add($cashFlowStatementItem);
			$cashFlowStatementItem->getSubItems($cashFlowStatement->id, $subItemType)->each(function ($subItem) use ($dataWithRelations, $cashFlowStatementItem) {
				$subItem->isSubItem = true; // isSubRow
				if ($cashFlowStatementItem->has_depreciation_or_amortization) {
					$subItem->pivot->can_be_depreciation = true;
				}
				$dataWithRelations->add($subItem);
			});
		});
		return [
			'data' => $dataWithRelations,
			"draw" => (int)Request('draw'),
			"recordsTotal" => CashFlowStatementItem::count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}
	public function commonScope(Request $request): builder
	{
		return CashFlowStatement::onlyCurrentCompany()->when($request->filled('search_input'), function (Builder $builder) use ($request) {

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

	public function commonScopeForReport(Request $request, CashFlowStatement $cashFlowStatement): builder
	{
		$subItemType = $request->get('sub_item_type');

		return CashFlowStatementItem::with(['subItems' => function ($builder) use ($cashFlowStatement, $subItemType) {
			$builder
				->wherePivot('financial_statement_able_id', $cashFlowStatement->id)
				->wherePivot('sub_item_type', $subItemType);
		}])
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($cashFlowStatement) {
			// 	$builder->where('financial_statement_ables.id', $cashFlowStatement->id);
			// })
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($cashFlowStatement) {
			// 	$builder->where('financial_statement_ables.id', $cashFlowStatement->id);
			// })
			->orderBy('financial_statement_able_items.id', 'asc');

		// return CashFlowStatementItem::with(['subItems' => function ($builder) use ($cashFlowStatement) {
		// 	$builder->where('financial_statement_able_id', $cashFlowStatement->id);
		// }])->whereHas('financialStatementAbles', function (Builder $builder) use ($cashFlowStatement) {
		// 	$builder->where('financial_statement_ables.id', $cashFlowStatement->id);
		// })
		// 	->orderBy('financial_statement_able_items.id', 'asc');
	}
}
