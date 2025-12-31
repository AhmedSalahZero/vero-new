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
use PhpOffice\PhpSpreadsheet\Shared\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Schema;

class LabelingItemExportAsPdf implements
	FromCollection,
	Responsable,
	WithHeadings,
	WithMapping,
	WithEvents,
	WithTitle,
	WithDrawings
	, ShouldAutoSize

{
	use Exportable, RegistersEventListeners;
	private Collection $exportData;
	private string $reportType;
	private $startQrcodeFromRowNumber ;
	private $verticalAlign ;
	private $horizontalAlign ;
	private $paperSize ;
	private $maxColsCount ;
	private $maxRows ;
	private $noRowsBeforeHeader ;
	private $pageOrientation ;
	private $logoRowHeight ;
	private $rowHeight ;
	private $dataRowHeight ;
	private $padding ;
	private $numberOfCodePerPage ;
	private $headers = [];
	/**
	 * @param Collection $products
	 */

	public function __construct(Collection $items )
	{
		$company = getCurrentCompany() ;
		$this->logoRowHeight = 80;
		$this->numberOfCodePerPage = 10;
		$this->dataRowHeight = 41;
		$this->paperSize = [
			'a3'=>\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3,
			'a4'=>\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4,
			'a5'=>\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A5,
		][$company->labeling_paper_size ?: 'a3'];
		$this->rowHeight = 100;
		$this->verticalAlign = 'center';
		$this->horizontalAlign = 'left';
		$this->pageOrientation = $company->print_labeling_type ?: 'portrait';
		$this->writerType = 'pdf';
		$this->fileName =   'report.pdf';
		$this->exportData = $items;
		$this->maxRows = count($items) ;
		$this->noRowsBeforeHeader = 1 ;
		$this->padding = 1 ;
		$this->startQrcodeFromRowNumber = 3 ;
		$this->headers =LabelingItem::getHeaderFromElement($items->first())  ;
		$this->maxColsCount = count($this->headers)  ;
		
	}

	public function collection()
	{
		return $this->exportData;
	}

	
	public function headings(): array
	{


		$header = [
			// [
			// 	getCurrentCompany() ? getCurrentCompany()->getName() : 'test company name',
			// ]
			
			//  ,
			 [
				getCurrentCompany()->labeling_report_title
			 ]
			//  ,[
			// 	getExportDateTime()
			//  ],[
			// 	getExportUserName()
			//  ]

		];
		return array_merge($header,[array_to_upper($this->headers)]);
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
		$drawing->setHeight($this->rowHeight);
		$drawing->setWidth(50);
		$drawing->setCoordinates('A'.$startQrcodeFromRowNumber);
		
		$startQrcodeFromRowNumber++;
		$drawings [] = $drawing;
	}
	
	// set logs
	
	
	$lastRowLetter = getNameFromNumber($this->maxColsCount) ;
	foreach(['labeling_logo_3','labeling_logo_2','labeling_logo_1'] as $logo){
		$logoPath = public_path('storage/'.getCurrentCompany()->{$logo}) ;
		if(!file_exists($logoPath)){
			continue;
		}
		$drawing = new Drawing();
		$drawing->setPath($logoPath );
		$drawing->setWidth(100);
		$drawing->setCoordinates($lastRowLetter.'1');
		$lastRowLetter = chr(ord($lastRowLetter)-1) ;
		$drawings [] = $drawing;
	}
	$lastRowLetter = getNameFromNumber($this->maxColsCount) ;
	
	// footer stamp
	$currentRow = 0 ;
	while ($currentRow < $this->maxRows){
		
		foreach(['labeling_stamp'] as $logo){
			$logoPath = public_path('storage/'.getCurrentCompany()->{$logo}) ;
			if(!file_exists($logoPath)){
				continue;
			}
			$drawing = new Drawing();
			$drawing->setPath($logoPath );
			$drawing->setWidth(100);
			$currentFooterRow = $currentRow + $this->noRowsBeforeHeader + 1 + $this->numberOfCodePerPage ;
			$currentRow = $currentRow + $this->numberOfCodePerPage ; 
			$currentFooterRow = $currentFooterRow > $this->maxRows ? $this->maxRows+$this->noRowsBeforeHeader + 1 : $currentFooterRow;
			$drawing->setCoordinates($lastRowLetter.$currentFooterRow );
			// $lastRowLetter = $lastRowLetter ;
			$drawings [] = $drawing;
		}
		
	}
	
	
	
    return $drawings;
}

	public function map($row): array
	{

		
		$data= [];
		foreach($this->headers as $index=>$headerName){
			if($index == 0){ // barcode
				$data[$index] = '';
				continue ;
			}
				$data[$index] = trim(str_replace('//','// ',$row->{$headerName})) ;
		}
		return $data ;
	}

	public function registerEvents(): array
	{
		return [
			
			
			AfterSheet::class => function (AfterSheet $afterSheet) {
				
				$lastColumnLatter = getNameFromNumber($this->maxColsCount);
				$noRowsBeforeHeader = $this->noRowsBeforeHeader ;
				
				
				
				/**
				 * * كرر الصف دا مه كل صفحه
				 * 
				 */
					/**
				 */
				$afterSheet->sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,2);
				$afterSheet->sheet->getPageSetup()->setOrientation($this->pageOrientation)
				// ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3_TRANSVERSE_PAPER)
				->setPaperSize($this->paperSize)
				// ->setFitToPage(true)
				// ->setFitToHeight(true)
			
				;
				
			

				$sheet = $afterSheet->sheet->getDelegate();
				for($columnLetter = 'B' ; $columnLetter<= $lastColumnLatter; $columnLetter++){
	
					$sheet->getColumnDimension($columnLetter)
					// ->setWidth('100','px')
					->setAutoSize(true)
					;
					if($columnLetter == $lastColumnLatter){
						break;
					}
				}
				
				$afterSheet->sheet->getStyle('A1:Z5')->applyFromArray([
					'font' => [
						'bold' => true
					]
				]);
			
				$rowBeforeHeaderB='ffffff';
				$range = 'A1'.':'.$lastColumnLatter.$noRowsBeforeHeader ;
				$borderColor = 'ffffff' ;
				$sheet->getStyle($range)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical($this->verticalAlign)
				->setHorizontal($this->horizontalAlign)
				->setWrapText(true)
				->setIndent($this->padding);
				
				$sheet->getStyle($range)->applyFromArray([
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['argb' => $borderColor],
						],
					],
					
				]);
				
				/**
				 * * اضافة لون الخلفية لاول اربع صفوف الخاصين باسم الشركة و التاريخ الخ
				 */
			
				
				
				/**
				 * * اضافة لون الخلفية للصف الخاص باسماء العواميد زي الكود 
				 */
				$headerBg = '0741A5';
				$headerRange = 'A'.($noRowsBeforeHeader+1).':'.$lastColumnLatter.($noRowsBeforeHeader+1);
				$sheet->getStyle($headerRange)->getFill()
				->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
				->getStartColor()->setARGB($headerBg);
					/**
				 * * اضافة لون الكتابة للصف الخاص باسماء العواميد زي الكود 
				 */
				$headerTextColor = 'ffffff';
				$sheet->getStyle($headerRange)
				->getFont()
				->getColor()
				->setARGB($headerTextColor);
				
				$sheet->getStyle($headerRange)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical('center')
				->setHorizontal('center')
				->setWrapText(true)
				->setIndent($this->padding);
				
				
					/**
				 * * لون البوردر
				 */
				$sheet->getStyle($headerRange)->applyFromArray([
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['argb' => $borderColor],
						],
					],
					
				]);
				
				
				

				$sheet->getRowDimension(1)->setRowHeight($this->logoRowHeight,'px');
				for($rowIndex = $this->startQrcodeFromRowNumber-1;$rowIndex<= $this->maxRows+$noRowsBeforeHeader+1 ; $rowIndex++ ){
					$sheet->getRowDimension($rowIndex)->setRowHeight($this->dataRowHeight);
					// $sheet->getColumnDimension()->setWidth(50);
					$sheet->getStyle('A'.$rowIndex.':'.getNameFromNumber($this->maxColsCount).$rowIndex)
						->getAlignment()
						->setShrinkToFit(true)
						->setVertical($this->verticalAlign)
						->setHorizontal($this->horizontalAlign)
						->setWrapText(true)
						->setIndent($this->padding);
						}
						
						$sheet->getStyle($headerRange)
				->getFont()
				->getColor()
				->setARGB($headerTextColor);
				
				$sheet->getStyle($headerRange)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical('center')
				->setHorizontal('center')
				->setWrapText(true)
				->setIndent($this->padding);
				
				
			}
		];
	}


	public function title(): string
	{
		return 'sheet title';
	}
}
