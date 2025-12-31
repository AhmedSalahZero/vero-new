<?php

namespace App\Exports;

use App\Models\IncomeStatement;
use Carbon\Carbon;
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

class IncomeStatementExport implements
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
	private string $reportType;

	/**
	 * @param Collection $products
	 */

	public function __construct(Collection $incomeStatementReport, Request $request, IncomeStatement $incomeStatement,string $reportType)
	{
		$this->writerType = $request->get('format');
		$this->fileName = $incomeStatement->name . '.Xlsx';
		$this->exportData = $incomeStatementReport;
		$this->incomeStatement = $incomeStatement;
		$this->reportType = $reportType;
	}

	public function collection()
	{
		return $this->exportData;
	}

	public function toResponse($request)
	{
	}

	public function headings(): array
	{
		$dates = $this->exportData->toArray()[array_key_first($this->exportData->toArray())];

		$header = [
			[
				getCurrentCompany()->getName(),
			

			]
			,
			 [
				$this->incomeStatement->name . ' [ ' .  $this->reportType .' ]' ,

			 ],[
				__('IncomeStatement Report')
			 ],[
				getExportDateTime()
			 ],[
				getExportUserName()
			 ]

		];

		$headerItems  = [];
		foreach ($dates as $date => $value) {
			if(validateDate($date)){
				$headerItems[] = Carbon::make($date)->format('F`Y');
			}else{
				$headerItems[] = $date;
			}
		}
		$header[] = $headerItems;
		return $header;
	}

	public function map($row): array
	{
		return $row;
	}

	public function registerEvents(): array
	{
		return [
			// BeforeSheet::class => function (BeforeSheet $event) {
			// 	$event->sheet
			// 		->getPageSetup()
			// 		->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
			// },
			AfterSheet::class => function (AfterSheet $afterSheet) {
				$afterSheet->sheet->getStyle('A1:Z5')->applyFromArray([
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
