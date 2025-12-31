<?php

namespace App\Imports;

use App\Helpers\HArr;
use App\Models\ActiveJob;
use App\Models\CachingCompany;
use App\Models\Company;
use App\Models\LastUploadFileName;
use App\Models\TablesField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class ImportData implements
	// ToModel,
	ToCollection,
	WithChunkReading,
	ShouldQueue,
	WithCalculatedFormulas,
	WithHeadingRow,
	WithBatchInserts,
	WithEvents
{
	use RegistersEventListeners;

	public $timeout = 50000*60;

	public $failOnTimeout = true;
	public $hasFailed = false;

	public static $static_model;

	public static $company_id;

	public $modelFields;

	public $format;

	public $model;

	private $job_id;

	private $companyId;

	private $batch;
	private $uploadModelName;

	private $errorMessage='';

	private $dateFailed =false;

	private $userId='';

	// private $rows = 0 ;

	public function __construct($company_id, $format, $model, $modelFields, $jobId, $userId,$uploadModelName)
	{
		Self::$company_id = $company_id;
		Self::$static_model = $model;
		$this->modelFields = $modelFields;
		$this->format = $format;
		$this->model = $model;
		$this->companyId = $company_id;
		$this->job_id = $jobId;
		$this->userId = $userId;
		$this->uploadModelName = $uploadModelName;
	}
	/**
	 * @param array $row
	 *
	 * @return \Illuminate\Database\Eloquent\Model|null
	 */



	public function collection(Collection $chunks)
	{
		$dates = [];
		$validationRow = null;
		$isInvalidData = false;
		$rowId = 2 ;
		if($rowId == 2 && $this->uploadModelName == 'LabelingItem'){
			$firstItem = $chunks->first();
		
			// $columns = array_keys((array) json_decode($firstItem));
			$columns = HArr::removeNullValues((array) json_decode($firstItem));
			$columns = array_keys($columns);
			$newItemsArr = array_combine($columns,$columns);
			$newItemsArr = FormatKeyAsColumnName($newItemsArr);
		
			foreach($newItemsArr as $newFieldName=>$newFieldTitle){
				$exists = TablesField::where('company_id',$this->companyId)->where('field_name',$newFieldName)->first();
				if(!Schema::hasColumn('labeling_items',$newFieldName)){
					Schema::table('labeling_items', function (Blueprint $table) use ($newFieldName) {
						$table->string($newFieldName)->nullable();
					});
				}
				if(!$exists){
					TablesField::create([
						'company_id'=>$this->companyId,
						'model_name'=>'LabelingItem',
						'field_name'=>$newFieldName,
						'view_name'=>$newFieldTitle
					]);
				}
				
			}
		
			$this->modelFields = array_merge($newItemsArr,[
				'company_id'=>$this->companyId,
			]);
			
		}
		foreach ($chunks as $key=>$rows) {
			$data = $this->dataCustomizationImport($rows,$rowId);
			$rowId ++ ;
			if (isset($data['validations'])) {
				$isInvalidData = true;
				$validationRow = $data['validations'];
				
				DB::table('caching_company')->where('job_id', $this->job_id)->delete();
				$cachingKey = generateCacheKeyForValidationRow($this->companyId,$this->uploadModelName);
				// Company::find($this->companyId)->deleteAllOldLastUploadFileNamesFor($this->uploadModelName,LastUploadFileName::CURRENT);
				$validationRows = $validationRow;
				if (Cache::has($cachingKey)) {
					$validationRows = arrayMergeTwoDimArray($validationRows,Cache::get($cachingKey, []));
				}
				Cache::forever($cachingKey , $validationRows);
				
			}
			$dates[] = $data;
		}
		
		if(!$isInvalidData){
			$key = Str::random(10) . 'for_company_' . $this->companyId;
			
			
			Cache::forever($key, $dates);
			DB::table('caching_company')->insert([
				'key_name'=>$key,
				'company_id'=>$this->companyId,
				'job_id'=>$this->job_id,
				'model'=>$this->uploadModelName
			]);
			
		}
	}

	public function batchSize(): int
	{
		return 1000;
	}

	public function chunkSize(): int
	{
		return 50000;
	}

	public function dateFormatting($date)
	{
		if (is_numeric($date)) {
			$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
		} else {
			if (str_contains($date, '/')) {
				$this->format = str_replace('-', '/', $this->format);
			}
			$strtotimeValue = date_create_from_format($this->format, $date);
			if(!$strtotimeValue){
				$this->format = str_replace('/', '-', $this->format);
				$strtotimeValue = date_create_from_format($this->format, $date);
				$this->format = str_replace('-', '/', $this->format);
			}
			if (!$strtotimeValue) {
		
				$this->errorMessage = __('Some Date Formate Is Not Correct');
				//TODO:if format [$this->format] is not correct it return false . so the following code causes error
				return null;
			} else {
				$date =  $strtotimeValue->format('Y-m-d');
			}
		}
				
		return $date;
	}

	protected function validateRowValue($key, $value):array
	{
		
		$invalidDates = [];
		$allValidations =[];
		if(in_array($key , ['Date'  , __('Date') , 'Estimated',__('Estimated')])){
			$dateValidation = $this->dateFormatting($value);
				if (is_null($dateValidation)) {
					$allValidations[$key] =  [
						'value'=>$value,
						'message'=>__('Invalid Date Format'),
					];
				}
		}

		if(in_array($key , ['Document Type',__('Document Type')])){
			if (!in_array($value, ['INV', 'inv', 'invoice', 'INVOICE', 'فاتوره'])) {
				$allValidations[$key] =  [
					'value'=>$value,
					'message'=>__('Invalid Document Type Only Allowed [INV , inv , invoice , INVOICE ,فاتوره ] '),
				];
			}	
		}
		if(in_array($key , array_merge(getNumericExportFields() , getNumericWithNegativeAllowedExportFields()) )){
			
			if (!is_numeric($value) && !is_null($value) && $value != '') {
				$allValidations[$key] =  [
					'message'=>__('Invalid Numeric Value'),
					'value'=>$value
				];
			}
		}
		if(in_array($key , \getNonEmptyFields())){
			if ( is_null($value) || $value == '') {
				$allValidations[$key] =  [
					'message'=>__('Empty Value Not Allowed'),
					'value'=>$value
				];
			}
		}
	
		return $allValidations;
		
		
	}

	public function dataCustomizationImport($row,$rowId)
	{
		$data = [];
		$row_with_no_spaces = [];
		$validations = [];
		
		foreach ($row as $key => $value) {
		
			
			$row_with_no_spaces[trim($key)] = trim($value);
			$rowValidation = $this->validateRowValue(trim($key), trim($value));
			if (isset($rowValidation[$key]) && count($rowValidation[$key])) {
				$validations[$rowId][$key] =  $rowValidation[$key] ;
			}
		}
		if(count($validations)){
			return [
				'validations'=>$validations
			] ;
		}

		foreach ($this->modelFields as $field_name => $row_name) {
			if (is_int($row_name)) {
				$data[$field_name] = $row_name;
			} else {
				if (isset($row_with_no_spaces[$row_name])) {
			
					if (str_contains($field_name, 'date') || str_contains($field_name,'estimated')) {
						$data[$field_name] = $this->dateFormatting($row_with_no_spaces[$row_name]);
					} else {
						$item = str_replace('\\', '', $row_with_no_spaces[$row_name]);
						$data[$field_name] = trim(preg_replace('/\s+/', ' ', $item));
						if($field_name == 'currency' && $item == 'EUR'){
							$data[$field_name] = 'EURO';
						}
					}
				} else {
					$data[$field_name] = null;
				}
			}
		}
		$data['id'] = generateIdForExcelRow($this->companyId);

		return $data;
	}

	public function registerEvents(): array
	{
		$error = $this->errorMessage;

		return [
			ImportFailed::class => function (ImportFailed $event) use ($error) {
				ActiveJob::where('id', $this->job_id)->where('model',$this->uploadModelName)->delete();
				CachingCompany::where('job_id', $this->job_id)->where('model',$this->uploadModelName)->delete();
				$key = generateCacheFailedName($this->companyId, $this->userId,$this->uploadModelName);
				$err = __('Excel Import Failed') . ' ' . $error;
				Cache::forever($key, $err);
			},
		];
	}

	
}
