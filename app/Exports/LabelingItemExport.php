<?php

namespace App\Exports;

use App\Models\IncomeStatement;
use App\Models\LabelingItem;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS2D;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Schema;

class LabelingItemExport implements
	FromCollection,
	Responsable,
	WithHeadings,
	WithMapping,
	ShouldAutoSize,
	WithEvents,
	WithTitle,
	WithDrawings

{
	use Exportable, RegistersEventListeners;
	private Collection $exportData;
	private string $reportType;
	private $startQrcodeFromRowNumber ;
	private $maxColsCount ;
	private $headers = [];
	/**
	 * @param Collection $products
	 */

	public function __construct( $items )
	{
		$this->writerType = 'Xlsx';
		$this->fileName =   'labeling.Xlsx';
		$this->exportData = $items;
		$this->maxRows = count($items) ;
		$this->startQrcodeFromRowNumber = 6 ;
		$this->headers =LabelingItem::getHeaderFromElement($items->first())  ;

		$this->maxColsCount = count($this->headers)  ;
		
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


		$header = [
			[
				getCurrentCompany() ? getCurrentCompany()->getName() : 'test company name',
			]
			
			 ,[
				__('Labeling Items') . ' [ ' . getCurrentCompany()->labeling_report_title . ' ] '
			 ],[
				getExportDateTime()
			 ],[
				getExportUserName()
			 ]

		];
	
		return array_merge($header,[$this->headers]);
	}
	
	
public function drawings()
{


    $drawings = [];
	$startQrcodeFromRowNumber = $this->startQrcodeFromRowNumber ;
	foreach($this->exportData as $index=>$row){
		 $drawing = new Drawing();
		$imageName = strtotime(now()->toDateTimeString()).$index.'.png';
		\Storage::disk('public')->put($imageName,base64_decode((new DNS2D)->getBarcodePNG($row->getCode($index+1), 'QRCODE',5,5)));;
		$drawing->setPath(public_path('storage/'.$imageName));
		$drawing->setHeight(100);
		$drawing->setWidth(50);
		$drawing->setCoordinates('A'.$startQrcodeFromRowNumber);
		
		$startQrcodeFromRowNumber++;
		$drawings [] = $drawing;
	}
    return $drawings;
}

	public function map($row): array
	{

		
		$data= [];
		foreach($this->headers as $index=>$headerName){
			if($index == 0){ // barcode
				$data[$index] = $row->{$headerName};
				// $data[$index] = '';
				continue ;
			}
			$data[$index] = str_replace('//','// ',$row->{$headerName}) ;
		}
		return $data ;
		
	
	}

	public function registerEvents(): array
	{
		// $this->maxRows
		
		return [

			AfterSheet::class => function (AfterSheet $afterSheet) {
				$afterSheet->sheet->getStyle('A1:Z5')->applyFromArray([
					'font' => [
						'bold' => true
					]
				]);
				for($rowIndex = $this->startQrcodeFromRowNumber-1;$rowIndex< $this->maxRows ; $rowIndex++ ){
					$afterSheet->sheet->getDelegate()->getRowDimension($rowIndex)->setRowHeight(50);
					$afterSheet->sheet->getDelegate()->getStyle('A'.$rowIndex.':'.getNameFromNumber($this->maxColsCount).$rowIndex)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical('center')
				->setHorizontal('center')
				->setWrapText(true)
				->setIndent(1);
				}
				
			}
		];
	}


	public function title(): string
	{
		return 'sheet title';
	}
}
