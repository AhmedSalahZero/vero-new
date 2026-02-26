@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    input.form-control[type="text"][readonly] {
        background-color: white !important;
        color: black !important;
        font-weight: 400 !important;
    }

    .fixed-column-table {
        width: 100%;
        overflow-x: auto;
        /* Enable horizontal scrolling */
        border-collapse: collapse;
    }

    .fixed-column {
        position: sticky;
        left: -15px;
        background: #f8f8f8;
        /* Optional: distinguish the fixed column */
        z-index: 1;
        /* Ensure it stays above other cells */
    }

    html body input.custom-input-string-width,
    .name-max-width-class {
        width: 400px !important;
        min-width: 400px !important;
        max-width: 400px !important;
    }

</style>
@endsection
@section('sub-header')
{{ $title }}
@endsection
@section('content')
{{-- <div id="study-duration" data-duration="{{ $study->duration_in_years }}"></div> --}}
<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == Study::STUDY ?'active':'' }}" data-toggle="tab" href="#{{Study::STUDY  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ $tableTitle }}
                    </a>
                </li>
            </ul>



        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = 'study' ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">

                    <x-tables.repeater-table :tableClasses="'table-condensed fixed-column-table table-row-spacing income-class-table'" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden scrollable-table'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th :subParentClass="'plus-max-width-class fixed-column'" class="  header-border-down plus-max-width-class" :title="__('+/-')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :subParentClass="'name-max-width-class fixed-column'" class="  header-border-down name-max-width-class exclude-from-collapse" :title="__('Name')"></x-tables.repeater-table-th>
                            {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('')"></x-tables.repeater-table-th> --}}
                            @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                            @php
                            $currentMonthNumber = explode('-',$dateAsString)[1];
                            $currentYear= explode('-',$dateAsString)[0];
                            $currentYearRepeaterIndex = 0 ;
                            @endphp
                            <x-tables.repeater-table-th data-column-index="{{ $dateAsIndex }}" class=" header-border-down " :title="dateFormatting($dateAsString, 'M\' Y')"></x-tables.repeater-table-th>

                            @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
                            <x-tables.repeater-table-th :icon="true" data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse " :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
                            @php
                            $currentYearRepeaterIndex ++;
                            @endphp
                            @endif

                            @endforeach
                            {{-- <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th> --}}
                        </x-slot>
                        <x-slot name="trs">

                            @php
                            @endphp
                            @foreach($totalResults as $tableIndex => $currentTableData)
                            @include('non_banking_services.income-statement.multiple.main_rows',['hasSubItems'=>true,'hasParent'=>false])
                            @foreach($studies as $studyIndex=>$study)
                            @php
                            $currentMainRows = $subIncomeStatements[$studyIndex][$tableIndex];
                            $subItems = $currentMainRows['sub_items']??[];
                            $hasSubItems = count($subItems);
                            @endphp
                            @include('non_banking_services.income-statement.multiple.main_rows',['currentTableData'=>$currentMainRows,'study'=>$study,'hasSubItems'=>$hasSubItems,'hasParent'=>true])
                            @foreach($subItems as $subItemId => $subItemArr)
                            @include('non_banking_services.income-statement.multiple.sub_items')
                            @endforeach
							@if($tableIndex == 0 || $tableIndex == 1) 
                            @include('non_banking_services.income-statement.multiple.sub_items',['subItemArr'=>[],'enabled'=>true,'subItemId'=>__('Adjusted')])
							@endif
							
                            @endforeach
                            @endforeach
                        </x-slot>




                    </x-tables.repeater-table>


                </div>


            </div>


            @if(isset($nextButton))
            <div class="text-right mt-4 cash-flow-btn">
                <a href="{{ $nextButton['link'] }}" class="btn btn-primary ">{{ $nextButton['title'] }}</a>
            </div>
            @endif



            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>

</div>

@endsection
@section('js')

{{-- <script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script> --}}

<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript">
</script>


<script>
    $(document).on('click', '.js-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    })

</script>

@endsection
@push('js')
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>
    $(function() {

        $('.collapse-before-me').trigger('click')
        // $('.expense-category-class').trigger('change')
    })

    $(function() {
        //	$('[data-group-index]').trigger('change');
    })

</script>
<script>
$(document).on('click', '.add-btn-parent-js', function (e) {
	//console.log('from 13')
	e.preventDefault()
	$(this).toggleClass('rotate-180')
	$(this).closest('[data-is-parent-row]').nextUntil('[data-is-parent-row]').toggleClass('hidden')
	var mainIndex = $(this).closest('[data-is-parent-row]').attr('data-main-index');
	$('[data-is-sub-row][data-main-index="'+mainIndex+'"]').addClass('hidden');
})
</script>
@endpush
