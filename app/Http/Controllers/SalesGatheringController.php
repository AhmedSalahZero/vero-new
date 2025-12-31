<?php

namespace App\Http\Controllers;

use App\Exports\ExportData;
use App\Exports\LabelingItemExport;
use App\Exports\LabelingItemExportAsPdf;
use App\Helpers\HArr;
use App\Models\Company;
use App\Models\CustomerInvoice;
use App\Models\LabelingItem;
use App\Models\Log;
use App\Models\MediumTermLoan;
use App\Models\SalesGathering;
use App\Models\TablesField;
use Illuminate\Http\Request;
use Schema;

class SalesGatheringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	protected function getSearchDateFieldName(string $modelName,?string $fieldName){
		if(!$fieldName){
			return null;
		}
		if($modelName == 'CustomerInvoice' || $modelName == 'SupplierInvoice'){
			if($fieldName == 'invoice_due_date'){
				return 'invoice_due_date';
			}
			return 'invoice_date';
		}
		if($modelName == 'SalesGathering'){
			return 'date';
		}
		if($modelName == 'ExportAnalysis'){
			return 'purchase_order_date';
		}
		if($modelName == 'ExpenseAnalysis'){
			return 'date';
		}
		return 'date';
	}
    public function index(Company $company, Request $request, string $uploadType='SalesGathering',string $loanId = null )
    {
		$hasLabelingItemCodeField = LabelingItem::hasCodeField();
		$loan = MediumTermLoan::find($loanId);
        $modelName = $uploadType;
		$labelingUniqueItemsPerColumn = [];
		$hasCodeColumnForLabelingItem = false;  
		$orderByDirection = $uploadType == 'LabelingItem' || $uploadType == 'LoanSchedule' ? 'asc' :'desc';
		$fieldValue = $request->get('field') ;
		$searchDateField = $this->getSearchDateFieldName($modelName,$fieldValue);
		$hasField = $request->has('field') ;
        $uploadingArr = getUploadParamsFromType($uploadType);
        $fullModelPath = $uploadingArr['fullModel'];
        $mainDateOrderBy = $uploadingArr['orderByDateField'];
        $uploadPermissionName = $uploadingArr['uploadPermissionName'];
        $exportPermissionName = $uploadingArr['exportPermissionName'];
        $deletePermissionName = $uploadingArr['deletePermissionName'];
        Log::storeNewLogRecord('enterSection', null, __('Data Gathering [ '. $uploadType . ' ]'));
		$pageLength = $modelName == 'LabelingItem' && is_numeric($company->labeling_pagination_per_page) && $company->labeling_pagination_per_page > 0 ?$company->labeling_pagination_per_page  : 50 ;
        $salesGatherings = $fullModelPath::company()->when($hasField, function ($q) use ($request,$fieldValue) {
            $q->where($fieldValue, 'like', '%'.$request->get('value') .'%');
        })
        ->when($request->has('from'), function ($q) use ($request,$searchDateField) {
            $q->where($searchDateField, '>=', $request->get('from'));
        })
        ->when($request->has('to'), function ($q) use ($request,$searchDateField) {
            $q->where($searchDateField, '<=', $request->get('to'));
        })->when($request->has('filter_labeling'),function($q) use ($request){
			foreach($request->all() as $key => $val){
				if($val && Schema::hasColumn('labeling_items',$key)){
					$q->where($key,$val);
				}
			}
		})
		->when($uploadType == 'LoanSchedule',function($q) use ($loanId){
			$q->where('medium_term_loan_id',$loanId);
		})
        ->orderBy($mainDateOrderBy, $orderByDirection)->paginate($pageLength);
        $exportableFields  = (new ExportTable)->customizedTableField($company, $uploadType, 'selected_fields');
        if($modelName == 'CustomerInvoice' || 'SupplierName'==$modelName) {
            unset($exportableFields['withhold_amount']);
        }
        $viewing_names = array_values($exportableFields);
        $db_names = array_keys($exportableFields);
        if($modelName == 'LabelingItem'){
			$labeling = LabelingItem::where('company_id',$company->id)->get();
			if($labeling->first() && ($labeling->first()->code || $labeling->first()->Code)){
				$hasCodeColumnForLabelingItem= true ; 
			}
			$labelingUniqueItemsPerColumn = filterByColumnName($labeling);
		}
        $notPeriodClosedCustomerInvoices = $modelName == 'CustomerInvoice' ? CustomerInvoice::getOnlyNotClosedPeriods() : null;
		$firstIndexElementInLabeling = $salesGatherings->first() ? $salesGatherings->first()->id : 0;
		$lastIndexElementInLabeling = $salesGatherings->last() ? $salesGatherings->last()->id : 0;
        $navigators =$this->getUploadingPageExportNavigation($modelName,$uploadPermissionName,$exportPermissionName,$deletePermissionName,$firstIndexElementInLabeling,$lastIndexElementInLabeling);

        return view('client_view.sales_gathering.index', compact('navigators','loan','hasLabelingItemCodeField','hasCodeColumnForLabelingItem','labelingUniqueItemsPerColumn', 'salesGatherings', 'company', 'viewing_names', 'db_names', 'uploadPermissionName', 'exportPermissionName', 'deletePermissionName', 'modelName', 'notPeriodClosedCustomerInvoices'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Company $company)
    {
        $customerInvoice = new SalesGatheringViewModel($company);

        return view('client_view.sales_gathering.form', $customerInvoice);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Company $company)
    {
        // $request['company_id'] = $company->id;
        SalesGathering::create($request->all());
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SalesGathering  $salesGathering
     * @return \Illuminate\Http\Response
     */
    public function show(SalesGathering $salesGathering)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SalesGathering  $salesGathering
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company, SalesGathering $salesGathering)
    {

        $salesGathering  = new SalesGatheringViewModel($company, $salesGathering);

        return view('client_view.sales_gathering.form', $salesGathering);
    }

    /**
     * Update the spec  ified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SalesGathering  $salesGathering
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company, SalesGathering $salesGathering)
    {
        $salesGathering->update($request->all());
        toastr()->success('Updated Successfully');
        return (new SalesGatheringViewModel($company, $salesGathering))->view('client_view.sales_gathering.form');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SalesGathering  $salesGathering
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company,Request $request, $modelId)
    {
		$modelType  = $request->get('modelType');
		$fullModelName = 'App\Models\\'.$modelType ;
		$model = $fullModelName::find($modelId);
		toastr()->error('Deleted Successfully');
        $model->delete();
        return redirect()->back();
    }
    public function export(Company $company, string $modelName)
    {
        $uploadParams = getUploadParamsFromType($modelName);
        $exportableFields = exportableFields($company->id, $modelName);
        // If there are no exportable fields were found return with a warning msg
        if ($exportableFields === null) {
            toastr()->warning('Please choose exportable fields first');
            return redirect()->back() ;
        }
        // Get The Selected exportable fields returns a pair of ['field_name' => 'viewing name']
        $selected_fields = (new ExportTable)->customizedTableField($company, $modelName, 'selected_fields');
        // Array Contains Only the name of fields
        $exportable_fields = array_keys($selected_fields);
        
        $salesGathering = $uploadParams['fullModel']::where('company_id', $company->id)->get();
        // Customizing the collection to be exported
        $salesGathering = collect($salesGathering)->map(function ($invoice) use ($exportable_fields) {
            $data = [];
            foreach ($exportable_fields as $field) {
                if (str_contains($field, 'deduction_id_')) {
                    $value = Deduction::find($invoice->$field)->name[lang()] ??null;
                } elseif (str_contains($field, 'date')) {
                    $value = $invoice->$field ===null ?: date('d-m-Y', strtotime($invoice->$field));
                } else {
                    $value = $invoice->$field;
                }
                $data[$field] = $value ;
            }
            return $data;
        });

        return (new ExportData($company->id, array_values($selected_fields), $salesGathering))->download($modelName.'.xlsx');

    }
    public function getUploadingPageExportNavigation(string $modelName,string $uploadPermissionName,string $exportPermissionName,string $deletePermissionName,int $fromIndex=  0 , int $toIndex=0)
    {
		$additionalUploadDataArray = $modelName == 'LoanSchedule' ? ['medium_term_loan_id'=>getLastSegmentInRequest()] : [];
		$viewName = $modelName == 'LoanSchedule' ? 'upload' : 'sales_gathering'; // i do not know the purpose of this variable
		$user = auth()->user();
		$company = getCurrentCompany();
		$deleteAllDataTitle = $modelName == 'LabelingItem' ? __('Delete All Data (With Columns)') : __('Delete All Data') ;
		$deleteAllDataRouteName = $modelName == 'LabelingItem'? route('delete.all.labeling.items.with.columns',[$company->id]) : route('truncate',[$company,$modelName]);
		
		$exportNavArr = $modelName != 'LabelingItem' ? [
			'name'=>__('Export All Data'),
			'link'=>$user->can($exportPermissionName) ? route('salesGathering.export',['company'=>$company->id , 'model'=>$modelName]):'#',
			'show'=>$user->can($exportPermissionName),
			'icon'=>'fas fa-file-import',
			'attr'=>[
				// 'data-toggle'=>'modal',
				// 'data-target'=>'#search-form-modal',
			]
			] : 
			[
				'name'=>__('Export Data'),
				'link'=>'#',
				'show'=>$user->can($exportPermissionName),
				'icon'=>'fas fa-file-import',
				'sub_items'=>[
					[
						'name'=>__('Export Excel'),
						'link'=>$user->can($exportPermissionName) ? route('export.labeling.item',['company'=>$company->id , 'type'=>'excel']):'#',
						'show'=>$user->can($exportPermissionName),
						'icon'=>'fas fa-file-import',
					],
					[
						'name'=>__('Export PDF'),
						'link'=>$user->can($exportPermissionName) ? route('export.labeling.item',['company'=>$company->id , 'type'=>'pdf']):'#',
						'show'=>$user->can($exportPermissionName),
						'icon'=>'fas fa-file-import',
						'attr'=>[
							'data-toggle'=>'modal',
							'data-target'=>'#print_report'
						]
					],
					
					
				]
				]
			;
			
			
		
		
        return [
            
        [
           'name'=>__('Upload Data'),
           'link'=>'#',
           'show'=>true,
		   'icon'=>'fas fa-upload',
           'sub_items'=>[
               [
                   'name'=>__('Template Download'),
                   'link'=>$user->can($uploadPermissionName)?route('table.fields.selection.view',[$company,$modelName,$viewName]) : '#' ,
                   'show'=>$modelName != 'LabelingItem'
               ],
               [
                   'name'=>__('Upload Data'),
                   'link'=>$user->can($uploadPermissionName) ? route('salesGatheringImport',array_merge(['company'=>$company->id , 'model'=>$modelName],$additionalUploadDataArray)) : '#',
                   'show'=>true
               ]
           


			   ],
			   
        ],
		[
			'name'=>__('Filter'),
			'link'=>'#',
			'show'=>$modelName != 'LabelingItem',
			'icon'=>'fas fa-search ',
			'attr'=>[
				'data-toggle'=>'modal',
				'data-target'=>'#search-form-modal',
			]
			],
			
			$exportNavArr,
			[
				'name'=>__('Print QR Code'),
				'link'=>$user->can($exportPermissionName) ? route('print.labeling.item.qrcode',['company'=>$company->id,'fromIndex'=>$fromIndex,'toIndex'=>$toIndex ]):'#',
				'show'=>$modelName == 'LabelingItem',
				'icon'=>'fas fa-print',
				// 'attr'=>[
				// 	'data-toggle'=>'modal',
				// 	'data-target'=>'#delete_from_to_modal'
				// ]
				],
				// [
				// 	'name'=>__('Print Report'),
				// 	'link'=>$user->can($exportPermissionName) ? route('print.labeling.item.qrcode',['company'=>$company->id,'fromIndex'=>$fromIndex,'toIndex'=>$toIndex ]):'#',
				// 	'show'=>$modelName == 'LabelingItem',
				// 	'icon'=>'fas fa-print',
				// 	'attr'=>[
				// 		'data-toggle'=>'modal',
				// 		'data-target'=>'#print_report'
				// 	]
				// ]
				
			
			// ,
				
				
				
				[
					'name'=>__('Delete'),
					'link'=>'#',
					'show'=>true,
					'icon'=>'fas fa-trash',
					'sub_items'=>[
						[
							'name'=>Request()->segment(4) == 'LabelingItem'? __('Delete By Serial') :__('Delete By Date'),
							'link'=>'#',
							'show'=>true,
							'attr'=>[
								'data-toggle'=>'modal',
								'data-target'=>'#delete_from_to_modal'
							]
						],
						[
							'name'=>$deleteAllDataTitle,
							'link'=>$user->can($deletePermissionName)?$deleteAllDataRouteName:'#',
							'show'=>$user->can($deletePermissionName)
						]
					
		 
		 
						],
						
				 ],
				
				
				
				
				
				
				
				
        
    	];
    }

		public function printLabelingItemsQrcode(Company $company , Request $request , $fromIndex , $toIndex ){
			$labeling = LabelingItem::where('company_id',$company->id)->whereBetween('id',[$fromIndex,$toIndex])->get();
			if(!count($labeling)){
				return redirect()
						->route('view.uploading',['company'=>$company->id,'model'=>'LabelingItem'])
						->with('fail',__('No Data Found'))	
						;
			}
			return view('printLabelingItems',[
				'labelings'=>$labeling,
				'height'=>$company->getLabelingHeight(),
				'width'=>$company->getLabelingWidth(),
				'width'=>$company->getLabelingWidth(),
				'paddingLeft'=>$company->getLabelingHorizontalPadding(),
				'paddingTop'=>$company->getLabelingVerticalPadding(),
				'marginBottom'=>$company->getLabelingMarginBottom(),
				
			]);
		}
		function removeAllNoneEmpty($collection)
{
	return  $collection->map(function ($item) use ($collection) {
		
		return new LabelingItem(collect($item)->filter(function ($value, $key) use ($collection) {
			return $collection->pluck($key)->filter()->isNotEmpty();
		})->toArray());
	});
}
		public function exportLabelingItems(Company $company , Request $request ,string $type){
			if($type == 'excel'){
				$items = LabelingItem::where('company_id',$company->id )->get() ;
		
				$items = $this->removeAllNoneEmpty($items);
		
				return (new LabelingItemExport($items))->download('Labeling Item.XLSX','Xlsx');
			}
			if($type == 'pdf'){
				$items = LabelingItem::where('company_id',$company->id )->get() ;
				$items = $this->removeAllNoneEmpty($items);
				return (new LabelingItemExport($items))->download('Labeling Item.XLSX','Xlsx');
			}
			
		}
		
	public function deleteAllLabelingItemsWithColumns(Request $request , Company $company )
	{
		TablesField::where('company_id',$company->id)->delete();
		LabelingItem::where('company_id',$company->id)->delete();
		return redirect()->route('view.uploading',['company'=>$company->id,'model'=>'LabelingItem']);		
		
	}
	public function printLabelingByCustomHeaders(Company $company , Request $request)
	{
		$headers = $request->get('labeling_print_headers',[]) ;
		$headers = array_merge($headers, ['id','company_id']);
		$printPaper = $request->get('print_labeling_type'); // landscape or portrait
		$exportableFields  = (new ExportTable)->customizedTableField($company, 'LabelingItem', 'selected_fields');
		$exportableFields = HArr::filterByKeys($exportableFields,$headers);
		$rowsPerPage = $request->get('no_rows_for_each_page_labeling',10) ;
		$company->update([
			'no_rows_for_each_page_labeling'=>$rowsPerPage,
			'print_labeling_type'=>$printPaper,
			'labeling_print_headers'=>$headers,
			'labeling_logo_1'=>$request->has('labeling_logo_1') ? $request->file('labeling_logo_1')->store('logs','public')  : $company->labeling_logo_1,
			'labeling_logo_2'=>$request->has('labeling_logo_2') ? $request->file('labeling_logo_2')->store('logs','public')  : $company->labeling_logo_2,
			'labeling_logo_3'=>$request->has('labeling_logo_3') ? $request->file('labeling_logo_3')->store('logs','public')  : $company->labeling_logo_3,
			'labeling_stamp'=>$request->has('labeling_stamp') ? $request->file('labeling_stamp')->store('logs','public')  : $company->labeling_stamp,
		]);
        $viewing_names = array_values($exportableFields);
        $db_names = array_keys($exportableFields);
		$reportTitle = $company->labeling_report_title ; 
		$hasCodeColumnForLabelingItem = false ;
		$labeling = LabelingItem::where('company_id',$company->id)->get($headers);
		if($labeling->first() && ($labeling->first()->code || $labeling->first()->Code)){
			$hasCodeColumnForLabelingItem= true ; 
		}
		return (new LabelingItemExportAsPdf($labeling))->download('Labeling Item.pdf','Dompdf');
		
     
	}
	
}
