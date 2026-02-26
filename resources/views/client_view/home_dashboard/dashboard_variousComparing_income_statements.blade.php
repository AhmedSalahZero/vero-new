@extends('layouts.dashboard')
@section('dash_nav')
@include('client_view.home_dashboard.main_navs-income-statement',['active'=>'various_incomestatement_dashboard'])

@endsection
@section('css')
<link href="{{ url('assets/vendors/general/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />


<style>
    .kt-portlet__body.dataTables_wrapper.dt-bootstrap4.no-footer {
        overflow: scroll
    }

    .reset-padding {
        padding-left: 20px !important;
        padding-right: 0 !important;

    }

    html body table tbody td.green {
        color: green !important
    }

    .kt-list-timeline__items {
        width: 100%;
        margin-bottom: 10px;
    }

    .chart-div {
        width: 100%;
        height: 500px;
    }

    html body table tbody td.red {
        color: red !important;
    }

    .modal-backdrop {
        display: none !important;
    }

    .main-with-no-child {
        background-color: rgb(238, 238, 238) !important;
        font-weight: bold;
    }

    .is-sub-row td.sub-text-bg {
        background-color: #aedbed !important;
        color: black !important;

    }

    .sub-numeric-bg {
        text-align: center;

    }

    .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        background-color: #0e96cd !important;
        color: white !important;
		
		
		background-color:#E2EFFE !important;
		color:black !important

    }

    .header-tr {
        background-color: #046187 !important;
    }

    .dt-buttons.btn-group {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .is-sales-rate,
    .is-sales-rate td,
    .is-sales-growth-rate,
    .is-sales-growth-rate td {
        background-color: #046187 !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        font-weight: bold;
        color: black;
    }

    a[data-toggle="modal"] {
        color: #046187 !important;
    }

    a[data-toggle="modal"].text-white {
        color: white !important;
    }

    .btn-border-radius {
        border-radius: 10px !important;
    }
	.table-bordered th, .table-bordered td {
		border:1px solid white !important;
	}

    .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        background-color: #E2EFFE !important;
        color: black !important;
		
    }
	

    .card-title:not(.collapsed) {
        background-color: #046187 !important;
        color: white !important;
    }

    .card-title span {
        font-size: 22px !important;
    }

    .card-title.collapsed span {
        color: #366cf3 !important;
    }

    .card-title.collapsed i,
    .card-title.collapsed::after {
        color: #366cf3 !important
    }

    .card-title:not(.collapsed) i,
    .card-title:not(.collapsed)::after,
    .card-title:not(.collapsed) span {
        color: white !important;
    }


    .custom-table-classes th {
        background-color: #046187 !important;
        color: white !important;
    }

</style>


<style>
    table {
        white-space: nowrap;
    }

</style>
@endsection
@section('content')
@php
$currentReportType = Request()->segment(5)
@endphp
<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title head-title text-primary text-capitalize">
                {{ __('Variance Comparing') . ' [ ' .  $currentReportType  . ' ]' }}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
        <form action="{{route('dashboard.various.incomeStatement',['company'=>$company,'subItemType'=>Request()->segments()[4]])}}" method="POST">
            @csrf
            <div class="form-group row ">
                <div class="col-md-4">
                    <label style="margin-right: 10px;"><b>{{__('Income Statement')}}</b></label>
                </div>
                <div class="col-md-4">
                    <div class="input-group date">
                        <select data-live-search="true" data-max-options="1" id="income_statement_select_id" name="income_statement_id" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder" {{-- multiple --}}>
                            @foreach($incomeStatements as $incomeSatatement)
                            <option value="{{ $incomeSatatement->id }}" @if($selectedItems['income_statement_id']==$incomeSatatement->id) selected @endif> {{ $incomeSatatement->name  }}</option>
                            @endforeach
                        </select>


                    </div>
                </div>
                @php
                $selectedTypesCount = count($selectedItems['main_items']) ;
                $selectAllOptions = $requestMethod == 'GET';
                @endphp

                <div class="col-md-4">
                    <div class="input-group date">
                        <select data-actions-box="true" data-live-search="true" data-max-options="0" name="types[]" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all" multiple>
                            @foreach ($permittedTypes as $id=>$name)
                            <option value="{{ $id }}" @if(in_array($id , $selectedItems['main_items'] ) || $selectAllOptions ) selected @endif> {{ $name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>



            </div>
            <div class="form-group row ">

                <div class="col-md-4">
                    <label>{{__('Reports')}}</label>
                </div>
                <div class="col-md-8">
                    <label>{{__('Report Type')}}</label>
                    <select id="report-type" data-actions-box="true" data-live-search="true" data-max-options="0" name="report_type" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all">
                        @foreach (getAllFinancialAbleTypesFormattedForDashboard() as $reportName=>$reportNameFormatted)
                        <option @if($reportName==$selectedItems['report_type']) selected @endif value="{{ $reportName }}"> {{ $reportNameFormatted }} </option>
                        @endforeach
                    </select>

                </div>

                {{-- <div class="col-md-4">
                    <label>{{__('Report Type')}}</label>
                <select id="second-report-type" data-actions-box="false" data-live-search="true" data-max-options="1" name="second_comparing_type" required class="form-control select2-select form-select form-select-2 form-select-solid fw-bolder select-all">
                    @foreach (getAllFinancialAbleTypes() as $secondReportType)
                    <option value="{{ $secondReportType }}" @if($secondReportType==$selectedItems['second_report_type']) selected @endif> {{ $secondReportType }} </option>
                    @endforeach

                </select>

            </div> --}}

            {{-- <div class="col-md-4">
                    <label>{{__('Note')}} </label>
            <div class="kt-input-icon">
                <div class="input-group ">
                    <input type="text" class="form-control" disabled value="{{__('The Report Will Show Max Top 50')}}">
                </div>
            </div>
    </div> --}}
</div>
<div class="form-group row ">

    <div class="col-md-4">
        <label>{{__('Interval')}}</label>
    </div>



    <div class="col-md-4">
        <label>{{__('Start Date')}}</label>
        <div class="kt-input-icon">
            <div class="input-group date">
                <input id="start_date_input_id" type="date" name="start_date" required value="{{$selectedItems['start_date']}}" class="form-control" placeholder="Select date" />
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <label>{{__('End Date')}}</label>
        <div class="kt-input-icon">
            <div class="input-group date">
                <input id="end_date_input_id" type="date" name="end_date" required value="{{$selectedItems['end_date']}}" class="form-control" placeholder="Select date" />
            </div>
        </div>
    </div>




    {{-- <div class="col-md-4">
                    <label>{{__('Data Type')}} </label>
    <div class="kt-input-icon">
        <div class="input-group ">
            <input type="text" class="form-control" disabled value="{{__('Value')}}">
        </div>
    </div>
</div> --}}
</div>



{{-- <div class="form-group row ">
                <div class="col-md-4">
                    <label><b>{{__('Third Inteval')}}</b></label>
</div>
<div class="col-md-4">
    <label>{{__('Start Date Three')}}</label>
    <div class="kt-input-icon">
        <div class="input-group date">
            <input type="date" name="start_date_three" required value="{{$start_date_2}}" class="form-control" placeholder="Select date" />
        </div>
    </div>
</div>
<div class="col-md-4">
    <label>{{__('End Date Three')}}</label>
    <div class="kt-input-icon">
        <div class="input-group date">
            <input type="date" name="end_date_three" required value="{{$end_date_2}}" max="{{date('Y-m-d')}}" class="form-control" placeholder="Select date" />
        </div>
    </div>
</div>



<div class="col-md-4">
    <label>{{__('Data Type')}} </label>
    <div class="kt-input-icon">
        <div class="input-group ">
            <input type="text" class="form-control" disabled value="{{__('Value')}}">
        </div>
    </div>
</div>
</div> --}}

<x-run />
{{-- <x-submitting /> --}}
</div>
</div>

@foreach($mainItemsWithItemsSubItems as $mainItemName=>$subItems)


<div class="kt-iconbox__content d-flex align-items-start flex-column w-100">
    <div class="kt-list-timeline__items">



        <div class="kt-portlet__body">
            <div class="kt-list-timeline">
                <div class="accordion  accordion-toggle-arrow" id="accordionExample{{ convertStringToClass($mainItemName) }}">
                    <div class="card">
                        <div class="card-header bg-white">
                            <div class="card-title collapsed" data-toggle="collapse" data-target="#collapseOne{{ convertStringToClass($mainItemName) }}" aria-expanded="true" aria-controls="collapseOne{{ convertStringToClass($mainItemName) }}">
                                <i class="flaticon2-layers-1"></i>

                                <span>{{ __($mainItemName) }}</span>

                            </div>



                        </div>
                        <div id="collapseOne{{ convertStringToClass($mainItemName) }}" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample{{ convertStringToClass($mainItemName) }}">
                            <div class="card-body with-padding">
                                <x-bar-nav :link="'#'">

                                    <div>
                                        @if($mainItemName == __('Sales Revenue'))
                                        @php
                                        $currentSalesRevenueValue = isset($chartItems[$mainItemName]) && isset(array_values($chartItems[$mainItemName])[0]) && isQuantitySubItem(array_values($chartItems[$mainItemName])[0]) ?'quantity':'value' ;
                                        @endphp

                                        <input type="hidden" name="sales_revenue_type" value="{{ $currentSalesRevenueValue  }}">
                                        <select id="value_sales_revenue_id" {{-- @if($currentSalesRevenueValue=='quantity' ) disabled @endif --}} multiple name="chart_items[{{ $mainItemName }}][]" class="form-control mr-3" style="max-width:300px;display:inline-flex;">

                                            {{-- <option value="0">{{ __('All') }}</option> --}}
                                            @foreach($subItems as $subItemName)
                                            @if(!isQuantitySubItem($subItemName))
                                            <option @if(isset($chartItems[$mainItemName]) && in_array($subItemName,$chartItems[$mainItemName])) selected @endif value="{{ $subItemName }}">{{ __($subItemName) }}</option>
                                            @endif
                                            @endforeach
                                        </select>

                                        <select id="quantity_sales_revenue_id" {{-- @if($currentSalesRevenueValue=='value' ) disabled @endif  --}} multiple name="chart_items[{{ $mainItemName }}][]" class="form-control" style="max-width:300px;display:inline-flex;">
                                            {{-- <option value="0">{{ __('All') }}</option> --}}
                                            @foreach($subItems as $subItemName)
                                            @if(isQuantitySubItem($subItemName))
                                            <option @if(isset($chartItems[$mainItemName]) && in_array($subItemName,$chartItems[$mainItemName])) selected @endif value="{{ $subItemName }}">{{ __($subItemName) }}</option>
                                            @endif
                                            @endforeach
                                        </select>

                                        @else

                                        <select multiple name="chart_items[{{ $mainItemName }}][]" class="form-control" style="max-width:300px;display:inline-flex;">
                                            {{-- <option value="0">{{ __('All') }}</option> --}}
                                            @foreach($subItems as $subItemName)
                                            <option @if(isset($chartItems[$mainItemName]) && in_array($subItemName,$chartItems[$mainItemName])) selected @endif value="{{ $subItemName }}">{{ __($subItemName) }}</option>
                                            @endforeach
                                        </select>
                                        @endif

                                        <button class="btn rounded btn-primary ml-4" type="submit">{{ __('Go') }}</button>
                                    </div>


                                </x-bar-nav>


                                <div class="row">
                                    @php
                                    $subItemValues = $charts['barChart'][$mainItemName]
                                    @endphp


                                    <div class="col-md-12">


                                        <div class="kt-portlet kt-portlet--mobile">

                                            <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">
                                                <div id="chartdiv{{ convertStringToClass($mainItemName) }}" class="chart-div"></div>

                                            </div>
                                        </div>
                                    </div>




                                    <div class="col-md-6">

                                        <div class="kt-portlet kt-portlet--mobile">

                                            <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">

                                                <input type="hidden" id="monthly_data{{ convertStringToClass($mainItemName) }}" data-total="{{ json_encode(formatDataFromTwoLinesChart($charts['barChart'][$mainItemName]) ?? []) }}">

                                                <div id="monthly_chartdiv{{ convertStringToClass($mainItemName) }}" class="chart-div" class="chartdashboard"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="kt-portlet kt-portlet--mobile">

                                            <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">
                                                <input type="hidden" id="monthly_data2{{ convertStringToClass($mainItemName) }}" data-total="{{ json_encode(formatDataFromTwoLinesChart2($charts['twoLinesChart'][$mainItemName]) ?? []) }}">
                                                <div id="monthly_chartdiv2{{ convertStringToClass($mainItemName) }}" class="chart-div" class="chartdashboard"></div>
                                            </div>
                                        </div>
                                    </div>


                                    {{-- donut charts [for foreach type (forecast and actual for example)] --}}

                                    @if(count($subItems) && $mainItemName != $subItems[0])
                                    @for($i = 0 ; $i<2 ; $i++) @php $currentReportItem=$i==0 ? $selectedItems['first_report_type'] : $selectedItems['second_report_type']; @endphp <div class="col-sm-12 col-lg-6">
                                        <div class="kt-portlet">
                                            <div class="kt-portlet__head">
                                                <div class="kt-portlet__head-label">
                                                    <h3 class="kt-portlet__head-title head-title text-primary text-capitalize">
                                                        {{ __(ucwords(str_replace('_',' ',$mainItemName))) }} ({{ __($currentReportItem) }})
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="kt-portlet kt-portlet--tabs">
                                            <div class="kt-portlet__head">
                                                <div class="kt-portlet__head-toolbar">
                                                    <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-toggle="tab" href="#kt_apps_contacts_view_tab_1_{{convertStringToClass($mainItemName.$currentReportItem)}}" role="tab">
                                                                <i class="flaticon-line-graph"></i> &nbsp; {{ __('Charts') }}
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link " data-toggle="tab" href="#kt_apps_contacts_view_tab_2_{{convertStringToClass($mainItemName.$currentReportItem)}}" role="tab">
                                                                <i class="flaticon2-checking"></i>{{ __('Reports Table') }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="kt-portlet__body">
                                                <div class="tab-content  kt-margin-t-20">

                                                    <div class="tab-pane active" id="kt_apps_contacts_view_tab_1_{{convertStringToClass($mainItemName.$currentReportItem)}}" role="tabpanel">

                                                        {{-- Monthly Chart --}}
                                                        <div class="col-xl-12">
                                                            <div class="kt-portlet kt-portlet--height-fluid">
                                                                <div class="kt-portlet__body kt-portlet__body--fluid">
                                                                    <div class="kt-widget12">
                                                                        <div class="kt-widget12__chart">
                                                                            {{-- <h4> {{ __('Sales Values') }} </h4> --}}
                                                                            <div id="chartdiv_pie{{convertStringToClass($mainItemName.$currentReportItem)}}" class="chart-div"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" id="kt_apps_contacts_view_tab_2_{{convertStringToClass($mainItemName.$currentReportItem)}}" role="tabpanel">
                                                        <div class="col-md-12">
                                                            <x-table :tableClass="'kt_table_with_no_pagination_no_scroll_without_pdf'">
                                                                @slot('table_header')
                                                                <tr class="table-active text-center ">
                                                                    <th class="text-center">
                                                                        {{ __($mainItemName) }}
                                                                    </th>
                                                                    <th class="text-center">
                                                                        {{ __('Value') }}
                                                                    </th>
                                                                    <th class="text-center">
                                                                        {{ __('Perc.% / Total') }}
                                                                    </th>
                                                                    @if(__($mainItemName) != __('Sales Revenue'))
                                                                    <th class="text-center">
                                                                        {{ __('Perc.% / Revenue') }}
                                                                    </th>
                                                                    @endif
                                                                </tr>
                                                                @endslot

                                                                @slot('table_body')
																@php
																	$totalPercentage = 0 ;
																	$totalPercentageOfRevenue = 0 ;
																@endphp

                                                                @foreach($charts['donutChart'][$mainItemName][$currentReportItem] ??[] as $subItemName=>$value)
                                                                @php
                                                                $total = array_sum($charts['donutChart'][$mainItemName][$currentReportItem]);
                                                                $totalOfSalesRevenue = isset($charts['donutChart'][__('Sales Revenue')][$currentReportItem]) ? array_sum($charts['donutChart'][__('Sales Revenue')][$currentReportItem]) : 0
                                                                @endphp
                                                                <tr >
                                                                    <td>
                                                                        {{ $subItemName }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        {{ number_format($value)  }}
                                                                    </td>
                                                                    <td class="text-center">
																	@php
																		$currentPercentage = $total ? $value / $total * 100 : 0  ;
																		$totalPercentage += $currentPercentage ;
																	@endphp
                                                                        {{ number_format( $currentPercentage, 2) }} %
                                                                    </td>
                                                                    @if(__($mainItemName) != __('Sales Revenue'))
                                                                    <td class="text-center">
																		@php
																			$percentageOfRevenue =$totalOfSalesRevenue ? $value / $totalOfSalesRevenue *100 :0;
																			$totalPercentageOfRevenue += $percentageOfRevenue;
																		@endphp
                                                                        {{ number_format( $percentageOfRevenue , 2) }} %
                                                                    </td>
																
                                                                    @endif
                                                                </tr>
																@if($loop->last)
																<tr class="table-active text-center ">
                                                                    <td>
	{{ __('Total') }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                    {{ number_format($total,0) }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                     {{ number_format($totalPercentage , 2 )  }} %
                                                                    </td>
                                                                    @if(__($mainItemName) != __('Sales Revenue'))
                                                                    <td class="text-center">
																	{{ number_format($totalPercentageOfRevenue,2) }} %
                                                                    </td>
																
                                                                    @endif
                                                                </tr>
																@endif 
																
                                                                @endforeach
                                                                @endslot
                                                            </x-table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                @endfor
                                @endif

                                <div class="col-md-6">

                                    <div class="kt-portlet kt-portlet--mobile">

                                        <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">

                                            <table class="custom-table-classes table table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center text-capitalize">{{ __('Months') }}</th>
                                                        <th class="text-center text-capitalize">{{ __($selectedItems['first_report_type']) }}</th>
                                                        <th class="text-center text-capitalize">{{ __($selectedItems['second_report_type']) }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Variance') }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Var %') }}</th>

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach($dates as $date)

                                                    <tr>
                                                        <td class="text-center">{{ formatDateWithoutDayFromString($date,true) }}</td>
                                                        <td class="text-center">{{ number_format($charts['barChart'][$mainItemName][$date][$selectedItems['first_report_type']]) }}</td>
                                                        <td class="text-center">{{ number_format($charts['barChart'][$mainItemName][$date][$selectedItems['second_report_type']]) }}</td>
                                                        <td class="text-center">{{ number_format($charts['barChart'][$mainItemName][$date]['variance']) }}</td>
                                                        <td class="text-center">{{ number_format($charts['barChart'][$mainItemName][$date]['var %'],2) . ' %' }}</td>
                                                    </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="kt-portlet kt-portlet--mobile">

                                        <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">

                                            <table class="custom-table-classes table table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
                                                <thead>
                                                    <tr>


                                                        <th class="text-center text-capitalize">{{ __('Months') }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Accumulated').' '. __($selectedItems['first_report_type']) }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Accumulated').' ' .__($selectedItems['second_report_type']) }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Accumulated Variance') }}</th>
                                                        <th class="text-center text-capitalize">{{ __('Accumulated Var %') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dates as $date)
                                                    <tr>

                                                        <td class="text-center">{{ formatDateWithoutDayFromString($date,true) }}</td>
                                                        <td class="text-center">{{ number_format($charts['twoLinesChart'][$mainItemName][$date][$selectedItems['first_report_type']]) }}</td>
                                                        <td class="text-center">{{ number_format($charts['twoLinesChart'][$mainItemName][$date][$selectedItems['second_report_type']]) }}</td>
                                                        <td class="text-center">{{ number_format($charts['twoLinesChart'][$mainItemName][$date]['variance']) }}</td>
                                                        <td class="text-center">{{ number_format($charts['twoLinesChart'][$mainItemName][$date]['var %'],2) . ' %' }}</td>

                                                    </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>








                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


</div>
@endforeach
</form>
<div class="row">




    <div class="row w-100" >

        <div class="col-md-12 reset-padding">
            <div class="kt-portlet kt-portlet--mobile">

                <div class="kt-portlet__body dataTables_wrapper dt-bootstrap4 no-footer">
                    <table class="table table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
                        <thead>
                            <tr class="header-tr ">
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell trigger-expand is-opened" style="cursor:pointer">{{ __('Expand All') }}</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">Name</th>
                                @foreach ($intervals as $intervalName )
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell text-capitalize"> {{ ''.getFirstSegmentInString($intervalName,'#').' '. __('Value') }} <br> ({{ getIntervalFromString($intervalName) }})</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell text-capitalize"> {{ __('% / Revenues') }} </th>
                                @endforeach
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">{{ __('Variance') }}</th>
                                <th class="text-center view-table-th header-th sorting_disabled sub-text-bg text-nowrap editable editable-text is-name-cell">{{ __('Percentage') }}</th>
                            </tr>
                        </thead>
                        <tbody>

								@php
								$typeIndex = 0 ;
								$currentTotalsOfSalesRevenues = [];
								@endphp 
                            @foreach ($intervalComparing as $theType => $intervals)
                            <tr class="sub-numeric-bg text-nowrap" data-model-id="{{ convertStringToClass($theType) }}">
                                <td class=" reset-table-width trigger-child-row-1 cursor-pointer sub-text-bg sub-closed">+</td>
                                <td class="sub-text-bg text-nowrap is-name-cell text-left" style="text-align: left !important;">{{ $theType }}</td>
                                @php
                                $currentValue =[ ];
                                $subIndex = 0;
								@endphp
								
                                @foreach ($intervals as $intervalName => $data )
                                @php
                                $currentValue[] = sum_all_keys($intervalComparing[$theType][$intervalName]) ;
								if($typeIndex == 0){
                                $currentTotalsOfSalesRevenues[] = sum_all_keys($intervalComparing[$theType][$intervalName]) ;
									
								}
                                $totalOfRevenue = sum_all_keys($intervalComparing[$theType][$intervalName])
								@endphp
								
                                <td class="sub-numeric-bg text-nowrap "> {{  number_format( $totalOfRevenue  ) }} </td>
								<td 
									@if($subIndex == 1)
								style="color:{{ getColorForIndexes($currentTotalsOfSalesRevenues[0],$currentTotalsOfSalesRevenues[1],$typeIndex ) }}"
								@endif 
								>
								@if($typeIndex == 0 )
								-
								@elseif($currentTotalsOfSalesRevenues[$subIndex])
								
								{{number_format($currentValue[$subIndex] /$currentTotalsOfSalesRevenues[$subIndex] * 100,2)}} % 
								@else
								
								0 % 
								
								@endif 
								</td>
								@php
								$subIndex++;
								@endphp
                                @endforeach
                                @php
                                $val = $currentValue[1] - $currentValue[0] ;
                                $percentage = isset($currentValue[0]) && $currentValue[0] ? number_format($val/ $currentValue[0] * 100 , 2) : number_format(0,2) ;
                                if($val > 0 && $currentValue[0] <0) { $percentage=$percentage * -1; } $color=getPercentageColorOfSubTypes($val,$theType) ; @endphp <td class="sub-numeric-bg text-nowrap " style="color:{{  $color }} !important">{{ number_format($val)  }}</td>
                                    <td class="sub-numeric-bg text-nowrap  " style="color:{{ getPercentageColorOfSubTypes($percentage , $theType) }} !important">
                                        {{ $percentage . ' %' }}
                                    </td>

                            </tr>
                            @php
                            $currentValue=[];
					
                            @endphp

                            @foreach(getSubItemsNames($intervalComparing[$theType]) as $subItemName=>$values )

                            <tr class="edit-info-row add-sub maintable-1-row-class{{ convertStringToClass($theType) }} is-sub-row even d-none">
                                <td class="sub-text-bg text-nowrap editable editable-text is-name-cell"> </td>
                                <td class="sub-text-bg text-nowrap editable editable-text is-name-cell">
                                    {{ $subItemName }}
                                </td>
                                @php
                                $currentValues =[];
                                $intervalIndex = 0;
								$currentPercentageValueArr = [];
								@endphp
								
                                @foreach($intervals as $newIntervalName => $intervalValue)
                                @php
                                $salesValue = $values[$newIntervalName] ?? 0;
                                $currentValues[] = $salesValue ;
                                @endphp
								
                                <td class=" sub-numeric-bg sub-text-bg text-nowrap editable editable-text is-name-cell  "> {{ number_format($salesValue) }} </td>
                                @php
								$currentPercentageValue = !isQuantitySubItem($subItemName) ? ($currentTotalsOfSalesRevenues[$intervalIndex] ? $salesValue / $currentTotalsOfSalesRevenues[$intervalIndex] * 100 : 0) : '-';
								$currentPercentageValueArr[] = !isQuantitySubItem($subItemName) ? ($currentTotalsOfSalesRevenues[$intervalIndex] ? $salesValue / $currentTotalsOfSalesRevenues[$intervalIndex] * 100 : 0) : '-';
								@endphp 
								<td class=" sub-numeric-bg sub-text-bg text-nowrap editable editable-text is-name-cell  "
								@if($intervalIndex == 1)
								style="color:{{ getColorForIndexes($currentPercentageValueArr[0],$currentPercentageValueArr[1],$typeIndex ) }}"
								@endif 
								> 
								{{
									
									is_numeric($currentPercentageValue) ? number_format($currentPercentageValue , 2)  . ' %' : $currentPercentageValue
								
								}}  </td>
                                @php
								$intervalIndex ++ ;
								@endphp
								@endforeach

                                @php

                                $val = $currentValues[1] - $currentValues[0] ;
                                $percentage = isset($currentValues[0]) && $currentValues[0] ? number_format($val/ $currentValues[0] * 100 , 2) : number_format(0,2) ;
                                if($val > 0 && $currentValues[0] <0) { $percentage=$percentage * -1; } $color=getPercentageColorOfSubTypes($val,$theType) ; @endphp <td class="sub-numeric-bg   text-nowrap editable editable-text is-name-cell " style="color:{{ getPercentageColorOfSubTypes($val , $theType) }} !important">
                                    {{ number_format($val ) }}
                                    </td>
                                    <td class="sub-numeric-bg   text-nowrap editable editable-text is-name-cell " style="color:{{ getPercentageColorOfSubTypes($percentage , $theType) }} !important">
                                        {{ $percentage .' %' }}
                                    </td>
                                    @endforeach


                            </tr>
							@php
							$typeIndex++ ;
							@endphp 
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>




    </div>

    <div id="selects-except-forecast-actual" data-value="{{ json_encode(getAllFinancialAbleTypes(['forecast','actual'])) }}"></div>
    <div id="selects-except-forecast" data-value="{{ json_encode(getAllFinancialAbleTypes(['forecast'])) }}"></div>
    @endsection
    @section('js')

    <script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js')}}" type="text/javascript"></script>
    <script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js')}}" type="text/javascript"></script>

    <script src="{{ url('assets/vendors/general/select2/dist/js/select2.full.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script>
    <script>
        reinitializeSelect2();

    </script>

    <script>
        $(document).on('click', '.trigger-child-row-1', function(e) {
            const parentId = $(e.target.closest('tr')).data('model-id');
            var parentRow = $(e.target).parent();
            var subRows = parentRow.nextAll('tr.add-sub.maintable-1-row-class' + parentId);

            subRows.toggleClass('d-none');
            if (subRows.hasClass('d-none')) {
                parentRow.find('td.trigger-child-row-1').html('+').addClass('sub-opened');
            } else if (!subRows.length) {
                // if parent row has no sub rows then remove + or - 
                parentRow.find('td.trigger-child-row-1').html('×').addClass('sub-closed');
            } else {
                parentRow.find('td.trigger-child-row-1').html('-').addClass('sub-closed');
            }

        });

    </script>

    <script>
        document.querySelector('.trigger-expand').addEventListener('click', function(e) {
            const expandText = "{{ __('Expand All') }}";
            const collapseText = "{{ __('Collapse All') }}";
            const element = e.target
            if (element.classList.contains('is-opened')) {
                element.classList.remove('is-opened');
                element.classList.add('is-closed');
                element.innerHTML = collapseText
                document.querySelectorAll('.sub-closed').forEach((elementTh) => {
                    $(elementTh).trigger('click')
                    elementTh.classList.remove('sub-closed');
                    elementTh.classList.add('sub-opened');
                })
            } else {
                element.innerHTML = expandText
                element.classList.remove('is-closed');
                element.classList.add('is-opened');
                document.querySelectorAll('.sub-opened').forEach((elementTh) => {
                    $(elementTh).trigger('click')
                    //elementTh.dispatchEvent(new Event('click'))
                    elementTh.classList.remove('sub-opened');
                    elementTh.classList.add('sub-closed');
                })
            }

        })

    </script>
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>


    @foreach($mainItemsWithItemsSubItems as $mainItemName=>$subItems)
    @php
    $subItemValues = $charts['barChart'][$mainItemName];
    @endphp
    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance

            var chart = am4core.create("monthly_chartdiv2{{ convertStringToClass($mainItemName) }}", am4charts.XYChart);

            // Increase contrast by taking evey second color
            chart.colors.step = 2;

            // Add data
            chart.data = $('#monthly_data2{{ convertStringToClass($mainItemName) }}').data('total');

            chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";
            // Create axes
            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 50;

            // Create series
            function createAxisAndSeries(field, name, opposite, bullet) {
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                if (chart.yAxes.indexOf(valueAxis) != 0) {
                    valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
                }

                var series = chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = field;
                series.dataFields.dateX = "date";
                series.strokeWidth = 2;
                series.yAxis = valueAxis;
                series.name = name;
                series.tooltipText = "{name}: [bold]{valueY}[/]";
                series.tensionX = 0.8;
                series.showOnInit = true;

                var interfaceColors = new am4core.InterfaceColorSet();

                switch (bullet) {
                    case "triangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 12;
                        bullet.height = 12;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var triangle = bullet.createChild(am4core.Triangle);
                        triangle.stroke = interfaceColors.getFor("background");
                        triangle.strokeWidth = 2;
                        triangle.direction = "top";
                        triangle.width = 12;
                        triangle.height = 12;
                        break;
                    case "rectangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 10;
                        bullet.height = 10;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var rectangle = bullet.createChild(am4core.Rectangle);
                        rectangle.stroke = interfaceColors.getFor("background");
                        rectangle.strokeWidth = 2;
                        rectangle.width = 10;
                        rectangle.height = 10;
                        break;
                    default:
                        var bullet = series.bullets.push(new am4charts.CircleBullet());
                        bullet.circle.stroke = interfaceColors.getFor("background");
                        bullet.circle.strokeWidth = 2;
                        break;
                }

                valueAxis.renderer.line.strokeOpacity = 1;
                valueAxis.renderer.line.strokeWidth = 2;
                valueAxis.renderer.line.stroke = series.stroke;
                valueAxis.renderer.labels.template.fill = series.stroke;
                valueAxis.renderer.opposite = opposite;
            }
            $.each(chart.data[0], function(key, val) {
                if (key != 'date') {
                    createAxisAndSeries(key, key, true, "circle");
                }
            });



            // Add legend
            chart.legend = new am4charts.Legend();

            // Add cursor
            chart.cursor = new am4charts.XYCursor();


        }); // end am4core.ready()

    </script>

    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            // Create chart instance

            var chart = am4core.create("monthly_chartdiv{{ convertStringToClass($mainItemName) }}", am4charts.XYChart);

            // Increase contrast by taking evey second color
            chart.colors.step = 2;

            // Add data
            chart.data = $('#monthly_data{{ convertStringToClass($mainItemName) }}').data('total');

            chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";
            // Create axes
            var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 50;

            // Create series
            function createAxisAndSeries(field, name, opposite, bullet) {
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                if (chart.yAxes.indexOf(valueAxis) != 0) {
                    valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
                }

                var series = chart.series.push(new am4charts.LineSeries());
                series.dataFields.valueY = field;
                series.dataFields.dateX = "date";
                series.strokeWidth = 2;
                series.yAxis = valueAxis;
                series.name = name;
                series.tooltipText = "{name}: [bold]{valueY}[/]";
                series.tensionX = 0.8;
                series.showOnInit = true;

                var interfaceColors = new am4core.InterfaceColorSet();

                switch (bullet) {
                    case "triangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 12;
                        bullet.height = 12;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var triangle = bullet.createChild(am4core.Triangle);
                        triangle.stroke = interfaceColors.getFor("background");
                        triangle.strokeWidth = 2;
                        triangle.direction = "top";
                        triangle.width = 12;
                        triangle.height = 12;
                        break;
                    case "rectangle":
                        var bullet = series.bullets.push(new am4charts.Bullet());
                        bullet.width = 10;
                        bullet.height = 10;
                        bullet.horizontalCenter = "middle";
                        bullet.verticalCenter = "middle";

                        var rectangle = bullet.createChild(am4core.Rectangle);
                        rectangle.stroke = interfaceColors.getFor("background");
                        rectangle.strokeWidth = 2;
                        rectangle.width = 10;
                        rectangle.height = 10;
                        break;
                    default:
                        var bullet = series.bullets.push(new am4charts.CircleBullet());
                        bullet.circle.stroke = interfaceColors.getFor("background");
                        bullet.circle.strokeWidth = 2;
                        break;
                }

                valueAxis.renderer.line.strokeOpacity = 1;
                valueAxis.renderer.line.strokeWidth = 2;
                valueAxis.renderer.line.stroke = series.stroke;
                valueAxis.renderer.labels.template.fill = series.stroke;
                valueAxis.renderer.opposite = opposite;
            }
            $.each(chart.data[0], function(key, val) {
                if (key != 'date') {
                    createAxisAndSeries(key, key, true, "circle");
                }
            });



            // Add legend
            chart.legend = new am4charts.Legend();

            // Add cursor
            chart.cursor = new am4charts.XYCursor();


        }); // end am4core.ready()

    </script>
    <script>
        am4core.ready(function() {

            // Themes begin
            am4core.useTheme(am4themes_animated);
            // Themes end

            var chart = am4core.create('chartdiv{{ convertStringToClass($mainItemName) }}', am4charts.XYChart)
            chart.colors.step = 2;

            chart.legend = new am4charts.Legend()
            chart.legend.position = 'top'
            chart.legend.paddingBottom = 20
            chart.legend.labels.template.maxWidth = 25095
            chart.tooltip.label.wrap = true;
            chart.tooltip.label.maxWidth = 150;

            var xAxis = chart.xAxes.push(new am4charts.CategoryAxis())
            xAxis.dataFields.category = 'category'
            xAxis.renderer.cellStartLocation = 0.1
            xAxis.renderer.cellEndLocation = 0.9
            xAxis.renderer.grid.template.location = 0;

            var yAxis = chart.yAxes.push(new am4charts.ValueAxis());
            //yAxis.min = 0;

            function createSeries(value, name) {
                var series = chart.series.push(new am4charts.ColumnSeries())
                series.dataFields.valueY = value
                series.dataFields.categoryX = 'category'
                series.name = name
                //series.columns.template.width = "70";
                series.columns.template.tooltipText = "{categoryX} -{name}: {valueY}";
                series.events.on("hidden", arrangeColumns);
                series.events.on("shown", arrangeColumns);
                series.columns.template.tooltipY = am4core.percent(0);
                var bullet = series.bullets.push(new am4charts.LabelBullet())
                bullet.interactionsEnabled = false
                bullet.dy = 30;

                bullet.label.fill = am4core.color('#ffffff')

                return series;
            }

            chart.data = @json(formatDataForBarChart($charts['barChart'][$mainItemName], $selectedItems['first_report_type'], $selectedItems['second_report_type']))


            createSeries('first', "{{ ucfirst($selectedItems['first_report_type']) }}")

            createSeries('second', "{{ ucfirst($selectedItems['second_report_type']) }}")

            createSeries('third', '{{ "Variance" }}');

            function arrangeColumns() {

                var series = chart.series.getIndex(0);

                var w = 1 - xAxis.renderer.cellStartLocation - (1 - xAxis.renderer.cellEndLocation);
                if (series.dataItems.length > 1) {
                    var x0 = xAxis.getX(series.dataItems.getIndex(0), "categoryX");
                    var x1 = xAxis.getX(series.dataItems.getIndex(1), "categoryX");
                    var delta = ((x1 - x0) / chart.series.length) * w;
                    if (am4core.isNumber(delta)) {
                        var middle = chart.series.length / 2;

                        var newIndex = 0;
                        chart.series.each(function(series) {
                            if (!series.isHidden && !series.isHiding) {
                                series.dummyData = newIndex;
                                newIndex++;
                            } else {
                                series.dummyData = chart.series.indexOf(series);
                            }
                        })
                        var visibleCount = newIndex;
                        var newMiddle = visibleCount / 2;

                        chart.series.each(function(series) {
                            var trueIndex = chart.series.indexOf(series);
                            var newIndex = series.dummyData;

                            var dx = (newIndex - trueIndex + middle - newMiddle) * delta

                            series.animate({
                                property: "dx"
                                , to: dx
                            }, series.interpolationDuration, series.interpolationEasing);
                            series.bulletsContainer.animate({
                                property: "dx"
                                , to: dx
                            }, series.interpolationDuration, series.interpolationEasing);
                        })
                    }
                }
            }

        }); // end am4core.ready()

    </script>

    @for($i = 0 ; $i<2 ; $i++) @php $currentReportItem=$i==0 ? $selectedItems['first_report_type'] : $selectedItems['second_report_type']; @endphp <script>
        am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("chartdiv_pie{{convertStringToClass($mainItemName.$currentReportItem)}}", am4charts.PieChart);
        // Add data
        chart.data = @json(isset($charts['donutChart'][$mainItemName][$currentReportItem]) ? formatDataForDonutChart($charts['donutChart'][$mainItemName][$currentReportItem]) : []);
        // Add and configure Series
        var pieSeries = chart.series.push(new am4charts.PieSeries());
        pieSeries.dataFields.value = "value";
        pieSeries.dataFields.category = "name";
        pieSeries.innerRadius = am4core.percent(50);
        pieSeries.ticks.template.disabled = true;
        pieSeries.labels.template.disabled = true;

        var rgm = new am4core.RadialGradientModifier();
        rgm.brightnesses.push(-0.8, -0.8, -0.5, 0, -0.5);
        pieSeries.slices.template.fillModifier = rgm;
        pieSeries.slices.template.strokeModifier = rgm;
        pieSeries.slices.template.strokeOpacity = 0.4;
        pieSeries.slices.template.strokeWidth = 0;

        chart.legend = new am4charts.Legend();
        chart.legend.position = "right";

        }); // end am4core.ready()

        </script>
        @endfor
        <script>
            $(function() {
                $('#skip').on('click', function(e) {
                    e.preventDefault();
                    $('#first_card').fadeOut("slow", function() {
                        $('#second_card').fadeIn(500);
                    });
                });

            })

        </script>


        @endforeach


        <script src="{{ asset('custom/axios.js') }}"></script>
		@if($canRefreshDates)
        <script>
            $(function() {
                const incomeStatementElement = document.querySelector('#income_statement_select_id');
                incomeStatementElement.addEventListener('change', function(e) {
                    e.preventDefault();
			
                    const income_statement_id = e.target.value
                    const startDateInput = document.querySelector('#start_date_input_id')
                    const endDateInput = document.querySelector('#end_date_input_id')
                    if (income_statement_id ) {
                        startDateInput.setAttribute('disabled', true)
                        endDateInput.setAttribute('disabled', true)
						$('.save-form').attr('disabled',true)
						
                        axios.get('/getStartDateAndEndDateOfIncomeStatementForCompany', {
                            params: {
                                company_id: '{{ getCurrentCompanyId() }}'
                                , income_statement_id
                            }
                        }).then((res) => {
                            if (res.data && res.data.status) {
                                startDateInput.value = res.data.dates.start_date
                                endDateInput.value = res.data.dates.end_date
							
                            }
                        }).catch(err => {
                        }).finally(ee => {
                            startDateInput.removeAttribute('disabled')
                            endDateInput.removeAttribute('disabled')
						$('.save-form').attr('disabled',false)
							
                        })
                    }
                })
                incomeStatementElement.dispatchEvent(new Event('change'))

            })

        </script>
		@endif 
        <script>
            $(document).on('change', '#value_sales_revenue_id', function() {
                $('#quantity_sales_revenue_id option').prop('selected', false)
            })

            $(document).on('change', '#quantity_sales_revenue_id', function() {
                $('#value_sales_revenue_id option').prop('selected', false)
            })

            $('form').on('submit', function() {
                if ($('#quantity_sales_revenue_id option:selected').length) {
                    $('#value_sales_revenue_id').prop('disabled', true)
                }
                if ($('#value_sales_revenue_id option:selected').length) {
                    $('#quantity_sales_revenue_id').prop('disabled', true)
                }
            })
			
			    </script>

        

        @endsection
