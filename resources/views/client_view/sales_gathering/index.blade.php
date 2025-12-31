@extends('layouts.dashboard')
@push('css')
<x-styles.commons></x-styles.commons>
<style>
.max-w-100{
	max-width:100px;
}
    .show-hide-repeater {
        cursor: pointer
    }

    [data-css-col-name="Code"],
    [data-css-col-name="code"],
    [data-css-col-name="id"],
    [data-css-col-name="ID"],
    [data-css-col-name="Id"],
    [data-css-col-name="Item"],
    [data-css-col-name="item"] {
        max-width: 300px !important;
        min-width: 300px !important;
        width: 300px !important;

    }



    svg[xmlns],
    svg[xmlns] * {
        width: 100%;
        height: 100%;
    }

    .dt-buttons.btn-group.flex-wrap {
        float: right;
    }

    .arrow-right {
        right: 10px !important;
    }

    .arrow-left {
        left: 10px !important;
    }

    .dataTables_filter {
        display: none !important;
    }

    .flex-1 {
        flex: 1 !important;
    }

    tbody .kt-option {
        border: none;
        padding: 0 !important;
        position: relative !important;
        top: -20px !important;
        max-width: 30px !important;
        left: 28% !important;
        height: 0 !important;
    }

    th .kt-checkbox.kt-checkbox--brand>span:after {
        border-color: white !important;
    }

    th .kt-checkbox.kt-checkbox--brand>span {
        border-color: white !important;
    }

    th .kt-checkbox.kt-checkbox--brand.kt-checkbox--bold>input~span {
        color: white !important;
    }

</style>
@endpush
@section('css')
<style>
    table {
        white-space: nowrap;

    }

</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />

<style>
    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    thead * {
        text-align: center !important;
    }

</style>
@endsection
@section('sub-header')
{{ camelToTitle($modelName) }} {{ __('Section') }}
<x-navigators-dropdown :navigators="$navigators ?? []"></x-navigators-dropdown>
@endsection
@section('content')
@php
$user = auth()->user();
$additionalTitle = $modelName == 'LoanSchedule' && isset($loan)  ? ' [ ' . $loan->getName(). ' ]' : ''; 
@endphp

@if($modelName == 'LabelingItem' )
<input id="pagination-per-page" type="hidden" value="{{ $company->labeling_pagination_per_page }}">
@php
$date = now()->format('d-m-Y')
@endphp
<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <x-sectionTitle :title="__('Labeling Setting')"></x-sectionTitle>
        </div>
    </div>
    <div class="kt-portlet__body">
        <form class="row" action="{{ route('save.labeling.item',['company'=>$company->id]) }}" encrypt="multipart/form-data">

            <div class="col-md-3">
                <div class="form-group">
                    <label for="label-table">
                        {{ __('Labeling Type') }}
                    </label>
                    <select name="labeling_type" class="form-control" id="labeling-type">
                        <option @if($company->labeling_type != 'barcode') selected @endif value="qrcode">{{ __('QR Code') }}</option>
                        <option @if($company->labeling_type == 'barcode') selected @endif value="barcode">{{ __('Barcode') }}</option>
                    </select>
                </div>

            </div>
			@if(!$hasLabelingItemCodeField)
			<div class="col-md-3">
                <div class="form-group">
                    <label for="label-table">
                        {{ __('Generate Code From') }}
                    </label>
                    <select  multiple name="generate_labeling_code_fields[]" class="form-control select2-select" id="labeling-code" data-live-search="true">
						@foreach($exportables as $exportableName=> $exportableTitle)
						@if(strtolower($exportableName) == 'item' || strtolower($exportableName) == 'code' )
						@continue	
						@endif
                        <option @if(in_array($exportableName,(array)$company->generate_labeling_code_fields) ) selected @endif value="{{ $exportableName }}">{{ $exportableTitle }}</option>
						@endforeach 
                    </select>
                </div>

            </div>
			@endif 
			



            <div class="col-md-3">
                <div class="form-group">
                    <label for="label-height">{{ __('Pagination Length') }}</label>
                    <input value="{{ $company->labeling_pagination_per_page }}" class="form-control" type="number" step="any" name="labeling_pagination_per_page">
                </div>
            </div>
			
			 <div class="col-md-3">
                <div class="form-group">
                    <label for="label-height">{{ __('Label / Sticker Height (cm)') }}</label>
                    <input value="{{ $company->label_height }}" class="form-control" type="number" step="any" name="label_height">
                </div>
            </div>
			
			 <div class="col-md-3">
                <div class="form-group">
                    <label for="label-height">{{ __('Label / Sticker Width (cm)') }}</label>
                    <input value="{{ $company->label_width }}" class="form-control" type="number" step="any" name="label_width">
                </div>
            </div>
			
			 <div class="col-md-3">
                <div class="form-group">
                    <label for="label-height">{{ __('Client Logo') }}</label>
                    <input value="{{ $company->labeling_client_logo }}" class="form-control" type="file" step="any" name="labeling_client_logo">
                </div>
            </div>
			
			 <div class="col-md-2">
                <div class="form-group" style="text-align:center">
                    <label for="label-height">{{ __('Use Client Logo') }}</label>
                    <input style="max-width:25px;height:25px;margin:auto" value="1" 
					@if($company->labeling_use_client_logo)
					checked
					@endif 
					class="form-control" type="checkbox"  name="labeling_use_client_logo">
                </div>
            </div>
			
			
			
			
            <div class="col-md-5">
                <div class="form-group">
                    <label for="label-height">{{ __('Report Title') }}</label>
                    <input id="report__title_for_labeling" value="{{ $company->labeling_report_title }}" class="form-control" type="text" step="any" name="labeling_report_title">
                </div>
            </div>


            <div class="col-md-2">
                <div class="form-group">
                    <label style="visibility:hidden;display:block" for="label-height">{{ __('submit form') }}</label>
                    <button class="active-btn btn btn-primary mx-auto submit-form-btn js-save-labeling-info">{{ __('Save & Generate QR Code') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@if(count($exportables))


<div class="kt-portlet">
    <div class="kt-portlet__body">
        <div class="row">
            <div class="col-md-10">
                <div class="d-flex align-items-center ">
                    <x-sectionTitle :title="__('Filtering')"></x-sectionTitle>
                </div>
            </div>
            <div class="col-md-2">
                <div class="btn active-style show-hide-repeater" data-query=".filtering-repeater">{{ __('Show/Hide') }}</div>
            </div>
        </div>
        <div class="row">
            <hr style="flex:1;background-color:lightgray">
        </div>
        <div @php $inSearchModel=false ; foreach($exportables as $exportableName=> $exportableTitle){
            if(Request()->has($exportableName)){
            $inSearchModel = true ;
            }
            }


            @endphp
            @if(!$inSearchModel)
            style="display:none"
            @endif
            class="row filtering-repeater">

            <div class="form-group row" style="flex:1;">
                <div class="col-md-12 mt-3">
                    <form method="get" action="{{ route('view.uploading',['company'=>$company->id,'model'=>'LabelingItem']) }}" class="row align-items-center">
                        <input type="hidden" name="filter_labeling" value="1">

                        @foreach($exportables as $exportableName => $exportableTitle)
						 <div class="col-md-3 mb-4">
                            <x-form.select :options="$labelingUniqueItemsPerColumn[$exportableName]??[]" :add-new="false" :label="$exportableTitle" class="select2-select   " data-filter-type="{{ 'create' }}" :all="false" name="pricing_plan_id" please-select="true" id="{{Request($exportableName).'_'.'pricing_plan_id' }}" :selected-value="Request($exportableName)"></x-form.select>
                        </div>
						
                        {{-- <div class="col-md-3">
                            <div class="form-group">
                                <x-form.select :please-select="true" :label="$exportableTitle" class="text-center  repeater-select" :options="$labelingUniqueItemsPerColumn[$exportableName]??[]"  :add-new="false" :all="false" name="{{ $exportableName }}" :selected-value="Request($exportableName)"></x-form.select>
                            </div>
                        </div> --}}
                        @endforeach

                        <div class="col-md-3" style="align-items:flex-end;display:flex;margin-left:auto">
                            <div style="margin-left:auto ">
                                <button type="submit" class="btn active-style">{{__('Save')}}</button>
                            </div>
                        </div>


                    </form>
                </div>

            </div>


        </div>
    </div>
</div>

@endif
@endif
<div class="row">
    <div class="col-lg-12">
        @if (session('warning'))
        <div class="alert alert-warning">
            <ul>
                <li>{{ session('warning') }}</li>
            </ul>
        </div>
        @endif
    </div>
</div>
@if(count($exportables))
@if($modelName != 'LabelingItem')

<form action="{{ route('multipleRowsDelete', [$company, $modelName]) }}" method="POST">
@endif 
    @csrf
    @method('delete')
    <x-table :instructions-icon="1" :notPeriodClosedCustomerInvoices="$notPeriodClosedCustomerInvoices??[]" :tableTitle="camelToTitle($modelName).' '.__(' Table') . $additionalTitle " :tableClass="'kt_table_with_no_pagination '" href="#" :importHref="$user->can($uploadPermissionName) ? route('salesGatheringImport',['company'=>$company->id , 'model'=>$modelName]) : '#'" :exportHref="$user->can($exportPermissionName) ? route('salesGathering.export',['company'=>$company->id , 'model'=>$modelName]):'#' " :exportTableHref="$user->can($uploadPermissionName)?route('table.fields.selection.view',[$company,$modelName,'sales_gathering']) : '#'" :truncateHref="$user->can($deletePermissionName)?route('truncate',[$company,$modelName]):'#' ">
        @slot('table_header')
       
        <tr class="table-active text-center">
            @if($user->can($deletePermissionName))
            <th class="">

                <label style="top:-10px;right:-7px" class="kt-option d-inline-flex border-none p-0 mt-[-15px] top-[-10] position-relative">
                    <span class="kt-option__control">
                        <span class="kt-checkbox kt-checkbox--bold kt-checkbox--brand kt-checkbox--check-bold" checked>
                            <input class="rows" type="checkbox" id="select_all">
                            <span></span>
                        </span>
                    </span>


                </label>


            </th>
            @endif
            @if($modelName == 'LabelingItem' && count((array)$company->generate_labeling_code_fields))
            <th class="select-to-delete">{{ __('No.') }}</th>
            <th data-css-col-name="qrcode">
                @if($company->labeling_type == 'qrcode')
                {{ __('QR Code') }}
                @else
                {{ __('Barcode') }}
                @endif
            </th>

            @endif


            @foreach ($viewing_names as $name)

            <th @if($modelName == 'LabelingItem') data-css-col-name="{{ $name }}" @endif >{{ __($name) }}</th>
            @endforeach

            @if($modelName == 'LabelingItem' && ! $hasCodeColumnForLabelingItem)
			
            {{-- <th data-css-col-name="id">{{ __('ID') }}</th> --}}

            @endif
			
			 @if($modelName == 'LoanSchedule' )
			 	<th class="max-w-100">{{ __('Status') }}</th>
			 	<th>{{ __('Remaining') }}</th>
			 @endif 

            <th>{{ __('Actions') }}</th>
        </tr>
        @endslot
        @slot('table_body')
        @foreach ($salesGatherings as $index=>$item)
		@php
		$serial = \App\Models\LabelingItem::generateSerial($salesGatherings,$index) ;
		@endphp
		
        <tr>
            @if($user->can($deletePermissionName))
            <td class="text-center">
                <label class="kt-option">
                    <span class="kt-option__control">
                        <span class="kt-checkbox kt-checkbox--bold kt-checkbox--brand kt-checkbox--check-bold" checked>

                            <input class="rows" type="checkbox" name="rows[]" value="{{ $item->id }}">
                            <span></span>
                        </span>
                    </span>
                    <span class="kt-option__label">
                        <span class="kt-option__head">

                        </span>

                    </span>
                </label>
            </td>

            @endif

            @if($modelName == 'LabelingItem')
            <td>
             {{ $serial }}
            </td>
			@if(count((array)$company->generate_labeling_code_fields))
            <td class="text-center" data-css-col-name="{{ 'qrcode' }}">
                @php
                $generateCode = $item->getCode($serial)  ;
                @endphp
                @if($company->labeling_type == 'barcode' )

                {!! DNS1D::getBarcodeHTML($generateCode, 'C39',3,33 ) !!}
                @else
                {{-- <img src="http://127.0.0.1:8000/assets/media/logos/vero%20analysis%20blue%20logo.png"> --}}
                <img style="max-width:120px;max-height:120px;" src="data:image/png;base64,{!! DNS2D::getBarcodePNG($generateCode, 'QRCODE') !!}" alt="barcode" />

                @endif
            </td>
			@endif

            @endif


            @foreach ($db_names as $name)
		
            @if ($name == 'date' || $name=='invoice_due_date' || $name == 'invoice_date')
			
            <td class="text-center">{{ isset($item->$name) ? date('d-M-Y',strtotime($item->$name)):  '-' }}</td>
            @elseif($name == 'invoice_amount' || $name == 'vat_amount' || $name == 'withhold_amount' || $name == 'collected_amount' || $name == 'paid_amount' || $name=='net_balance'|| $name=='net_invoice_amount')
			
            <td class="text-center">{{ number_format($item->$name?:0 ,2 ) }}   </td>
            @else
            <td @if($modelName == 'LabelingItem') data-css-col-name="{{ $name??'' }}" @endif class="text-center">
			@if($name == 'beginning_balance' || $name =='schedule_payment' || $name =='interest_amount' || $name == 'principle_amount' || $name == 'end_balance')
			@php
				$item->$name = number_format($item->$name);
			@endphp
			@endif 
                {{ qrcodeSpacing($item->$name??'') }}


                @endif
				
                @endforeach



                @if($modelName == 'LabelingItem' && !$hasCodeColumnForLabelingItem)
					{{-- <td data-css-col-name="{{ $name??'' }}">
						{{ qrcodeSpacing($item->getCode($serial)) }}
					</td> --}}
				
            @endif
			
				
					 @if($modelName == 'LoanSchedule' )
					 	<td class="text-capitalize text-wrap max-w-100">
						{{ $item->getStatusFormatted() }}
					</td>
					 	<td class="text-center">
						{{ $item->getRemainingFormatted() }}
					</td>
					
					
					 @endif 
					 

            <td class="kt-datatable__cell--left kt-datatable__cell " data-field="Actions" data-autohide-disabled="false">
			
                <span class="d-flex justify-content-center" style="overflow: visible; position: relative; width: 110px;">
					
                    {{-- <a type="button" class="btn btn-secondary btn-outline-hover-brand btn-icon" title="Edit" href="{{route('salesGathering.edit',[$company,$item])}}"><i class="fa fa-pen-alt"></i></a> --}}
                    <form class="kt-portlet__body" method="post" action="{{route('salesGathering.destroy',[$company->id,$item->id,$modelName])}}" style="display: inline">
					
						@if($modelName == 'LoanSchedule')
						{{-- {{ dd() }} --}}
						{{-- @if($item->remaining > 0) --}}
						<a href="{{ route('view.loan.schedule.settlements',['company'=>$company->id , 'loanSchedule'=>$item->id]) }}" class="btn btn-secondary btn-outline-hover-primary btn-icon">
							<i class="fa fa-dollar-sign"></i>
						</a>
						{{-- @endif --}}
						@endif 
                        @method('DELETE')
                        @csrf
						<input type="hidden" name="modelType" value="{{ $modelName }}">
                        <a class="btn btn-secondary btn-outline-hover-primary btn-icon" title="Edit" href="{{route('edit.sales.form',['company'=>$company->id,'model'=>$modelName , 'modelId'=>$item->id])}}"><i class="fa fa-edit"></i></a>
						{{-- @if(!$company->hasOdooIntegrationCredentials()) --}}
                        <button type="submit" class="btn btn-secondary btn-outline-hover-danger btn-icon" title="Delete" href=""><i class="fa fa-trash-alt"></i></button>
						{{-- @endif --}}
                    </form>
                </span>
            </td>
        </tr>

        @endforeach
        @endslot
    </x-table>
@if($modelName != 'LabelingItem')
	
</form>
@endif
<div class="kt-portlet">
    <div class="kt-portlet__head kt-portlet__head--lg">
        <div class="kt-portlet__head-label d-flex justify-content-start">
            {{ $salesGatherings->links() }}
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="kt_modal_2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{__("Instructions")}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <p class="pop-up-font">
                    <b> 1. Click on Template Download button </b>
                </p>
                <p class="pop-up-font">
                    <b> 2. Select the fields that suits your sales data structure </b>
                </p>
                <p class="pop-up-font">
                    <b> 3. Click download </b>
                </p>
                <p class="pop-up-font">
                    <b> 4. Fill your excel template </b>
                </p>
                <p class="pop-up-font">
                    <b> 5. Click Upload Data, choose your excel file then select date format finally click save </b>
                </p>
                <p class="pop-up-font">
                    <b> 6. Review your data, and then click Save Table </b>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js')
@include('js_datatable')
{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
@if($modelName != 'LabelingItem')
<script>
$(document).on('click', '#open-instructions', function(e) {
        e.preventDefault();
        $('#kt_modal_2').modal('show');
    })

    
    $(function() {
        $("td:not(.not-editable)").dblclick(function() {
            var OriginalContent = $(this).text();
            $(this).addClass("cellEditing");
            $(this).html("<input type='text' value='" + OriginalContent + "' />");
            $(this).children().first().focus();
            $(this).children().first().keypress(function(e) {
                if (e.which == 13) {
                    var newContent = $(this).val();
                    $(this).parent().text(newContent);
                    $(this).parent().removeClass("cellEditing");
                }
            });
            $(this).children().first().blur(function() {
                $(this).parent().text(OriginalContent);
                $(this).parent().removeClass("cellEditing");
            });
            $(this).find('input').dblclick(function(e) {
                e.stopPropagation();
            });
        });
    });
</script>
@endif 
<script>
    
	
	
	$('#select_all').change(function(e) {
        if ($(this).prop("checked")) {
            $('.rows').prop("checked", true);
        } else {
            $('.rows').prop("checked", false);
        }
    });
	








    window.addEventListener('scroll', function() {
        const top = window.scrollY > 140 ? window.scrollY + 210 : 250;

        $('.arrow-nav').css('top', top + 'px')
    })
    if ($('div.kt-portlet__body').length) {

        $('div.kt-portlet__body').append(`
								<i class="cursor-pointer text-dark arrow-nav  arrow-left fa fa-arrow-left"></i>
								<i class="cursor-pointer text-dark arrow-nav arrow-right fa  fa-arrow-right"></i>
								`)


        $(document).on('click', '.arrow-nav', function() {
            const scrollLeftOfTableBody = document.querySelector('.kt-portlet__body').scrollLeft
            const scrollByUnit = 500
            if (this.classList.contains('arrow-right')) {
                document.querySelector('.dataTables_scrollBody').scrollLeft += scrollByUnit

            } else {
                document.querySelector('.dataTables_scrollBody').scrollLeft -= scrollByUnit

            }
        })

        window.dispatchEvent(new Event('scroll'));

    }

</script>
<script>
    $(document).on('click', '.show-hide-repeater', function() {
        const query = this.getAttribute('data-query')
        $(query).fadeToggle(300)

    })
</script>
    <x-js.commons></x-js.commons>
@endsection
