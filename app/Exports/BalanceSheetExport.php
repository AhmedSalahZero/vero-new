<?php

namespace App\Exports;

use App\Models\BalanceSheet;
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

class BalanceSheetExport implements
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
	private BalanceSheet $balanceSheet;

	/**
	 * @param Collection $products
	 */

	public function __construct(Collection $balanceSheetReport, Request $request, BalanceSheet $balanceSheet)
	{
		$this->writerType = $request->get('format');
		$this->fileName = $balanceSheet->name . '.Xlsx';
		$this->exportData = $balanceSheetReport;
		$this->balanceSheet = $balanceSheet;
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
				$this->balanceSheet->name,
				__('BalanceSheet Report'),
				getExportDateTime(),
				getExportUserName()

			], [
				'',
				'',
				'',
				''

			]

		];

		$headerItems  = [];
		foreach ($dates as $date => $value) {
			$headerItems[] = $date;
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
		return $this->balanceSheet->name;
	}
}
