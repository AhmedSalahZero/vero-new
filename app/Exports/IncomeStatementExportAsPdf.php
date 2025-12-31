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
use Maatwebsite\Excel\Events\BeforeSheet;

class IncomeStatementExportAsPdf implements
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
	private array $mainRowsIndexes;
	private array $percentageRowsIndexes;
	private array $subRowsIndexes;
	private int $maxRowsCount;
	private int $maxColsCount;
	private string $reportType;

	/**
	 * @param Collection $products
	 */

	public function __construct(Collection $incomeStatementReport, Request $request, IncomeStatement $incomeStatement,array $mainRowsIndexes,array $percentageRowsIndexes,array $subRowsIndexes,int $maxColsCount , int $maxRowsCount,string $reportType)
	{
		$this->writerType = $request->get('format');
		$this->fileName = $incomeStatement->name . '.Xlsx';
		$this->exportData = $incomeStatementReport;
		$this->incomeStatement = $incomeStatement;
		$this->mainRowsIndexes = $mainRowsIndexes;
		$this->percentageRowsIndexes = $percentageRowsIndexes;
		$this->subRowsIndexes = $subRowsIndexes;
		$this->maxColsCount = $maxColsCount;
		$this->maxRowsCount = $maxRowsCount;
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
				__('Company Name:').' '.getCurrentCompany()->getName() . '
				
				' . __('Income Statement Report').' [ ' .  $this->reportType . ' ]' . ' 
				
				 ' . $this->incomeStatement->name .'
				 
				 '
				 . __('User Name:') .getExportUserName() .
				  '
				  
				   '.__('Date & Time:'). getExportDateTime() . ' 
				   
				   ',
					  
				'',
				'',
				'',
				''
			

			],
		

			

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
			BeforeSheet::class => function (BeforeSheet $event) {
				// $event->sheet->getColumnDimension('A')->setWidth('300','px');
				// $event->sheet->getColumnDimension('B')->setWidth('100','px');
				// $event->sheet->getColumnDimension('C')->setWidth('100','px');
				// $event->sheet->getColumnDimension('D')->setWidth('100','px');
				// $event->sheet->getColumnDimension('E')->setWidth('100','px');
				// $event->sheet->getColumnDimension('F')->setWidth('100','px');
				// $event->sheet->getColumnDimension('G')->setWidth('100','px');
				// $event->sheet->getColumnDimension('H')->setWidth('100','px');
				// $event->sheet->getColumnDimension('I')->setWidth('100','px');
				$event->sheet
					->getPageSetup()
					->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
					// PAPERSIZE_B4_ENVELOPE
					// PAPERSIZE_A3_TRANSVERSE_PAPER
					// PAPERSIZE_A2_PAPER [for largest ever]
					// PAPERSIZE_C3_ENVELOPE [best for now]
					->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A2_PAPER);
			},
			AfterSheet::class => function (AfterSheet $afterSheet) {
				$sheet = $afterSheet->sheet->getDelegate();
				// $sheet = $sheet->freezePane('A2');
				$lastColumnLatter = getNameFromNumber($this->maxColsCount);
			$mainRowBg = 'FFFFFFFF';
			$mainRowTextColor='000000';
			$mainRowsIndexes = $this->mainRowsIndexes ;
			// $mainRowsIndexes = [3,69,72,74,79,82 , 110,112,114,122,124,126] ;
			
			$subRowBg='E2EFFE';
			$subRowTextColor='000000';
			
			$percentageRowBg='046187';
			$percentageRowTextColor='ffffff';
			// $percentageRowsIndexes = [68,71,73,78,81,109,111,113,121,123,125,127] ;
			$percentageRowsIndexes = $this->percentageRowsIndexes ;
				for($i = 1 ; $i< $this->maxRowsCount ; $i++){
					$range = 'A'.$i.':'.$lastColumnLatter.$i ;
					
					// default design for sub rows 
					$bgColor=$subRowBg;
						$textColor=$subRowTextColor;
						
						
						$sheet->getStyle($range)->applyFromArray([
							'font' => [
								'bold' => true
							],
							
						]);
						
						if($i == 1){
						$sheet->mergeCells($range);
						
						$sheet->getStyle($range)->applyFromArray([
							'borders' => [
								'allBorders' => [
									'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
									'color' => ['argb' => 'ffffff'],
								],
							],
							
						]);
						
					}
						
					if($i < 3){
						$bgColor='074FA4';
					$textColor='FFFFFFFF';
					}
					if($i == 1){
						$bgColor='ffffff';
						$textColor="000000";

					}
					if( in_array($i , $percentageRowsIndexes)){
						// sales revenue header 
						$bgColor = $percentageRowBg;
						$textColor=$percentageRowTextColor;
					}
					
					if( in_array($i , $mainRowsIndexes)){
						// sales revenue header 
						$bgColor = $mainRowBg;
						$textColor=$mainRowTextColor;
					}
					
					$sheet->getRowDimension($i)->setRowHeight(40,'px');
					$sheet->getStyle($range)->getFill()
					->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
					->getStartColor()->setARGB($bgColor);
					$sheet->getStyle($range)->getFill()
            	->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            	->getStartColor()->setARGB($bgColor);
				$sheet->getStyle($range)
				->getFont()
				->getColor()
				->setARGB($textColor);
				// align text center
				$sheet->getStyle($range)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical('center')
				->setHorizontal('center')
				->setWrapText(true)
				->setIndent(1);
				
				$sheet->getStyle('A'.$i)
				->getAlignment()
				->setShrinkToFit(true)
				->setVertical('center')
				->setHorizontal('left')
				->setWrapText(true)
				->setIndent(1);
				
				}
			}
		];
	}


	public function title(): string
	{
		return $this->incomeStatement->name;
	}
}
