<?php
namespace App\Exports\Tradings;


use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IncomeStatementExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithTitle
{
    use Exportable;

    private Collection $exportData;
    private string $reportType;
    private array $isPercentageRow = [];
    private array $isMainItem = [];
    private array $dates;
    protected ?string $writerType;
    protected string $fileName;
    protected string $studyName;
    public function __construct(Collection $incomeStatementReport, array $dates, string $studyName, string $reportType)
    {
        $this->writerType = 'Xlsx';
        $this->exportData = $incomeStatementReport;
        $this->reportType = $reportType;
        $this->fileName = $reportType.' [ '.$studyName.' ] '.'.Xlsx';
        $this->dates = $dates;
        $this->studyName = $studyName;
    }

    public function collection()
    {
        return $this->flattenIncomeStatement($this->exportData);
    }

    public function title(): string
    {
        return 'Income Statement';
    }

    private function getOrderedColumns(): array
    {
        $orderedColumns = [];
        $yearsFound = [];
        foreach ($this->dates as $dateIndex => $dateAsString) {
            $carbonDate = Carbon::parse($dateAsString);
            $yearLabel = $carbonDate->year;
        
            // تحديد ترتيب السنة (0 للسنة الأولى، 1 للسنة الثانية، وهكذا)
            $yearOrder = (int) ($dateIndex / 12);
        
            // استخدام مفتاح فريد يجمع بين الترتيب والسنة لمنع تكرار الشهور في نفس العمود
            $uniqueYearKey = $yearOrder . '_' . $yearLabel;
        
            $yearsFound[$uniqueYearKey]['months'][] = [
                'index' => $dateIndex,
                'label' => $carbonDate->format("M' Y")
            ];
            $yearsFound[$uniqueYearKey]['year_label'] = $yearLabel;
            $yearsFound[$uniqueYearKey]['order'] = $yearOrder;
        }

        foreach ($yearsFound as $data) {
            // إضافة الشهور أولاً
            foreach ($data['months'] as $monthData) {
                $orderedColumns[] = [
                    'type' => 'month',
                    'index' => $monthData['index'],
                    'label' => $monthData['label']
                ];
            }
            // إضافة التوتال الخاص بهذه السنة تحديداً بعد شهورها مباشرة
            $orderedColumns[] = [
                'type' => 'total',
                'year_order_index' => $data['order'],
                'label' => "Total Yr.\n" . $data['year_label']
            ];
        }

        return $orderedColumns;
    }

    private function flattenIncomeStatement(Collection $sections): Collection
    {
        $rows = collect();
        $orderedColumns = $this->getOrderedColumns();
    
        // حساب عدد الصفوف في الـ Headings ديناميكياً
        // بما أن الـ headings() ترجع مصفوفة، فعدد صفوفها هو مكان بداية البيانات
        $headerRowsCount = count($this->headings());
        $rowIndex = $headerRowsCount + 1; // البيانات تبدأ بعد الهيدر مباشرة
        foreach ($sections as $section) {
			foreach (['main_items' => true, 'sub_items' => false] as $groupKey => $isMain) {
                foreach ($section[$groupKey] ?? [] as $itemKey => $item) {
                    $row = [];
                    $row['Item'] = $item['options']['title'] ?? $itemKey;
                
                    // تخزين الحالة باستخدام الـ rowIndex الديناميكي
                    $this->isPercentageRow[$rowIndex] = $item['options']['is-percentage']  ?? false;
					$this->isPercentageRow[$rowIndex] = $itemKey =='% Of Revenue' ? true : $this->isPercentageRow[$rowIndex];
					
                    $this->isMainItem[$rowIndex] = $isMain;

                    $yearTotalKeys = array_keys($item['year_total'] ?? []);

                    foreach ($orderedColumns as $col) {
                        if ($col['type'] == 'month') {
                            $val = $item['data'][$col['index']] ?? 0;
							$value = is_numeric($val) ? (float)$val : 0 ;
							if ($this->isPercentageRow[$rowIndex]) {
     										  $value = $value / 100;
  								 }
							$value = $value ? $value : '0';
                            $row[] = $value;
                        } else {
                            $keyIndex = $col['year_order_index'];
                            $actualKey = $yearTotalKeys[$keyIndex] ?? null;
                            $val = ($actualKey !== null) ? ($item['year_total'][$actualKey] ?? 0) : 0;
							$value = is_numeric($val) ? (float)$val : 0 ;
							if ($this->isPercentageRow[$rowIndex]) {
     										  $value = $value / 100;
  								 }
							$value = $value ? $value : '0';
                            $row[] = $value;
                        }
                    }

                    $rows->push($row);
                    $rowIndex++;
                }
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        $orderedColumns = $this->getOrderedColumns();
        $headerRow = ['Item Name'];
        foreach ($orderedColumns as $col) {
            $headerRow[] = $col['label'];
        }
        return [
            [app(Company::class)->getName()],
            [$this->studyName],
            [$this->reportType],
            ['Generated: ' . now()->format('Y-m-d')],
            [''],
            $headerRow
        ];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // الحصول على عدد صفوف الهيدر ديناميكياً
                $headerRowsCount = count($this->headings());
                $dataStartRow = $headerRowsCount + 1;

                // تنسيق صف العناوين الأخير (الذي يحتوي على أسماء الأعمدة)
                $sheet->getStyle("A{$headerRowsCount}:{$lastColumn}{$headerRowsCount}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
                ]);

                for ($i = $dataStartRow; $i <= $lastRow; $i++) {
                    $rowRange = "A{$i}:{$lastColumn}{$i}";
                
                    if (isset($this->isMainItem[$i]) && $this->isMainItem[$i]) {
                        $sheet->getStyle($rowRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                        $sheet->getStyle("A{$i}")->getFont()->setBold(true);
                    }

                    $dataRange = "B{$i}:{$lastColumn}{$i}";
                    if (isset($this->isPercentageRow[$i]) && $this->isPercentageRow[$i]) {
    // تنسيق النسبة: موجب ; سالب (أحمر) ; صفر
    $formatCode = '0.00%;[Red]-0.00%;0.00%';
} else {
    // تنسيق الأرقام العادية: موجب ; سالب (أحمر) ; صفر
    $formatCode = '#,##0;[red]-#,##0;0';
}
                    $sheet->getStyle($dataRange)->getNumberFormat()->setFormatCode($formatCode);

                    $sheet->getStyle($rowRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_NONE],
                            'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E2E2']],
                        ],
                    ]);
                }

                // تجميد الألواح ديناميكياً بناءً على بداية البيانات
                $sheet->freezePane("B{$dataStartRow}");
            
                $sheet->setShowGridlines(false);
                $sheet->getColumnDimension('A')->setAutoSize(true);
            },
        ];
    }
}
