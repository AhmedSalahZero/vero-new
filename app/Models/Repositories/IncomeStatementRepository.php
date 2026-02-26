<?php

namespace App\Models\Repositories;

use App\Helpers\HArr;
use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Repositories\IBaseRepository;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class IncomeStatementRepository implements IBaseRepository
{

	public function all(): Collection
	{
		return IncomeStatement::withAllRelations()->onlyCurrentCompany()->get();
	}

	public function allFormatted(): array
	{
		return IncomeStatement::onlyCurrentCompany()->get()->pluck('name', 'id')->toArray();
	}
	public function allFormattedForSelect()
	{
		$incomeStatements = $this->all();
		return formatOptionsForSelect($incomeStatements, 'getId', 'getName');
	}

	public function getAllExcept($id): ?Collection
	{
		return IncomeStatement::onlyCurrentCompany()->where('id', '!=', $id)->get();
	}

	public function query(): Builder
	{
		return IncomeStatement::onlyCurrentCompany()->query();
	}
	public function Random(): Builder
	{
		return IncomeStatement::onlyCurrentCompany()->inRandomOrder();
	}

	public function find(?int $id): IBaseModel
	{
		return IncomeStatement::onlyCurrentCompany()->find($id);
	}

	public function getLatest($column = 'id'): ?IncomeStatement
	{
		return IncomeStatement::onlyCurrentCompany()->latest($column)->first();
	}
	public function store(Request $request): IBaseModel
	{
		/**
		 * @var IncomeStatement $incomeStatement
		 */
		$incomeStatement = App(IncomeStatement::class);

		$incomeStatement = $incomeStatement->storeMainSection($request)->storeMainItems($request);
		return $incomeStatement;
	}

	public function storeReport(Request $request): IBaseModel
	{
	
		$incomeStatement = new IncomeStatement();
		$incomeStatement = $incomeStatement->storeReport($request);
		
		return $incomeStatement;
	}

	public function update(IBaseModel $incomeStatement, Request $request): void
	{
		// $incomeStatement
		// 	->updateProfitability($request);
	}

	public function paginate(Request $request): array
	{

		$filterData = $this->commonScope($request);

		$allFilterDataCounter = $filterData->count();

		$datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function (IncomeStatement $incomeStatement, $index) {
			$incomeStatement->creator_name = $incomeStatement->getCreatorName();
			$incomeStatement->created_at_formatted = formatDateFromString($incomeStatement->created_at);
			$incomeStatement->updated_at_formatted = formatDateFromString($incomeStatement->updated_at);
			$incomeStatement->order = $index + 1;
		});
		return [
			'data' => $datePerPage,
			"draw" => (int)Request('draw'),
			"recordsTotal" => IncomeStatement::onlyCurrentCompany()->count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}

	public function paginateReport(Request $request, IncomeStatement $incomeStatement): array
	{
		$filterData = $this->commonScopeForReport($request, $incomeStatement);
		$subItemType = $request->get('sub_item_type');
		$allFilterDataCounter = $filterData->count();
		$dataWithRelations = collect([]);
		
		$datePerPage = $filterData->get()->each(function (IncomeStatementItem $incomeStatementItem, $index) use ($dataWithRelations, $incomeStatement, $subItemType) {
			$incomeStatementItem->creator_name = $incomeStatementItem->getCreatorName();
			$incomeStatementItem->created_at_formatted = formatDateFromString($incomeStatementItem->created_at);
			$incomeStatementItem->updated_at_formatted = formatDateFromString($incomeStatementItem->updated_at);
			$incomeStatementItem->order = $index + 1;
			$forecastSubItemNames =$incomeStatementItem->getSubItems($incomeStatement->id, 'forecast')->pluck('pivot.sub_item_name')->toArray(); 
			$incomeStatementItem['main_rows'] = $incomeStatementItem->getMainRows($incomeStatement->id, $subItemType);
			$dataWithRelations->add($incomeStatementItem);
			$quantitiesFor = [];
			$subItems = $incomeStatementItem->getSubItems($incomeStatement->id, $subItemType);
			if($subItemType == 'adjusted' || $subItemType == 'modified'){
				$orderByReferenceArray = $incomeStatementItem->getSubItems($incomeStatement->id, 'actual')->values()->pluck('pivot.sub_item_name')->flip();
				$subItems=$subItems->sortBy(function($value , $key ) use ($orderByReferenceArray){
					return $orderByReferenceArray[$value->pivot->sub_item_name] ?? PHP_INT_MAX;
				});
			}
			$subItems->each(function ($subItem) use ($forecastSubItemNames,$incomeStatement, $subItemType, $dataWithRelations, $incomeStatementItem, &$quantitiesFor) {
				
				$subItem->isSubItem = true; // isSubRow
				if ($incomeStatementItem->has_depreciation_or_amortization) {
					$subItem->pivot->can_be_depreciation = true;
				}
				$isQuantity = $subItem->pivot && $subItem->pivot->is_quantity;
				$subItemName = $subItem->pivot && $subItem->pivot->sub_item_name ? $subItem->pivot->sub_item_name : null;
				$isExistInForecast = in_array($subItemName,$forecastSubItemNames);
				if($isExistInForecast){
					$subItem->pivot->exist_in_forecast = 1 ;
				}else{
					$subItem->pivot->exist_in_forecast = 0 ;
					
				}
				if (!$isQuantity) {
					$quantityRow = $incomeStatementItem->getSubItems($incomeStatement->id, $subItemType, $subItem->pivot->sub_item_name . quantityIdentifier)->first();
					if ($quantityRow) {
						$subItem->pivot->quantityPivot = $quantityRow->pivot->payload ? convertJsonToArray($quantityRow->pivot->payload) : [];
					}
					$dataWithRelations->add($subItem);
				}
			
				
			});
		});
		return [
			'data' => $dataWithRelations,
			"draw" => (int)Request('draw'),
			"recordsTotal" => IncomeStatementItem::count(),
			"recordsFiltered" => $allFilterDataCounter,
	
		];
	}
	public function commonScope(Request $request): builder
	{
		return IncomeStatement::onlyCurrentCompany()->when($request->filled('search_input'), function (Builder $builder) use ($request) {

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

	public function commonScopeForReport(Request $request, IncomeStatement $incomeStatement): builder
	{
		$subItemType = $request->get('sub_item_type');

		return IncomeStatementItem::with(['subItems' => function ($builder) use ($incomeStatement, $subItemType) {
			$builder
				->wherePivot('financial_statement_able_id', $incomeStatement->id)
				->wherePivot('sub_item_type', $subItemType);
		}])
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($incomeStatement) {
			// 	$builder->where('financial_statement_ables.id', $incomeStatement->id);
			// })
			// ->whereHas('financialStatementAbles', function (Builder $builder) use ($incomeStatement) {
			// 	$builder->where('financial_statement_ables.id', $incomeStatement->id);
			// })
			->orderBy('financial_statement_able_items.id', 'asc');

		// return IncomeStatementItem::with(['subItems' => function ($builder) use ($incomeStatement) {
		// 	$builder->where('financial_statement_able_id', $incomeStatement->id);
		// }])->whereHas('financialStatementAbles', function (Builder $builder) use ($incomeStatement) {
		// 	$builder->where('financial_statement_ables.id', $incomeStatement->id);
		// })
		// 	->orderBy('financial_statement_able_items.id', 'asc');
	}
}
