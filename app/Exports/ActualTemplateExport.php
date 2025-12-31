<?php

namespace App\Exports;

use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Excel;

class ActualTemplateExport implements
	FromCollection,
	Responsable,
	WithHeadings,
	WithMapping,
	ShouldAutoSize,
	WithEvents,
	WithTitle

{
	use Exportable, RegistersEventListeners;
	private Collection $exportData;
	private IncomeStatement $incomeStatement;

	/**
	 * @param Collection $products
	 */


	public function __construct(IncomeStatement $incomeStatement)
	{
		$this->incomeStatement = $incomeStatement ;

	}

	public function collection()
	{
		$itemsNames = [];
	
		 $mainItemsWithSubItems = $this->incomeStatement->mainItems->where('has_sub_items',1)->filter(function(IncomeStatementItem $mainItem){
			$subItemsNames = $mainItem->subItems()->wherePivot('sub_item_type', 'actual')->wherePivot('financial_statement_able_id',$this->incomeStatement->id)->wherePivot('financial_statement_able_item_id',$mainItem->id)->get()->sortBy('pivot.id')->pluck('pivot.sub_item_name','pivot.id')->toArray() ;
			return $mainItem->setRelation('sub_items_name',$subItemsNames);
		}) ;
		foreach($mainItemsWithSubItems as $mainItem )
		{
			foreach($mainItem->sub_items_name as $subItemName)
			{
			$itemsNames[] = 	$mainItem->getName().' - '.$subItemName;
			}
		}
		return collect($itemsNames);
	}

	public function toResponse($request)
	{
	}

	public function headings(): array
	{
		$dates  = $this->incomeStatement->getIntervalFormatted();
		$header = [
			[
				getCurrentCompany()->getName(),
				$this->incomeStatement->name,
				__('IncomeStatement Report'),
				getExportDateTime(),
				getExportUserName()

			], [
				'',
				'',
				'',
				''

			]

		];

		$headerItems  = ['Name'];
		
		foreach ($dates as $dateAsIndex => $dateAsString) {
			if(isActualDate($dateAsString)){
				$year = explode('-',$dateAsString)[0];
				$month = explode('-',$dateAsString)[1];
				$headerItems[] = $year . '-' . $month;
			}
		}
		$header[] = $headerItems;
		
		return $header;
	}

	public function map($row): array
	{
		return [$row];

		//    return [
		//        $row->getId(),
		//        $row->getRevenueBusinessLineName(),
		//        $row->getServiceCategoryName(),
		//        $row->getServiceItemName(),
		//        $row->getDeliveryDays(),
		//        $row->getTotalRecommendPriceWithoutVatFormatted(),
		//        $row->getTotalRecommendPriceWithVatFormatted(),
		//        $row->getTotalNetProfitAfterTaxesFormatted(),

		//    ];
	}

	public function registerEvents(): array
	{
		return [
			AfterSheet::class => function (AfterSheet $afterSheet) {
				$afterSheet->sheet->getStyle('A1:Z3')->applyFromArray([
					'font' => [
						'bold' => true
					]
				]);
			}
		];
	}


	public function title(): string
	{
		return $this->incomeStatement->name;
	}
}
