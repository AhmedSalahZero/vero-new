@extends('layouts.dashboard')
@section('css')

<style>
    table {
        white-space: nowrap;
        table-layout: auto;
        border-collapse: collapse;
        width: 100%;
    }
	
    table td {
        border: 1px solid #ccc;
        color: gr
    }

    table .absorbing-column {
        width: 100%;
    }

</style>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />



<style>
    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        /* color:#595d6e ; */
    }

    table.dataTable tbody tr.group-color>.dtfc-fixed-left,
    table.dataTable tbody tr.group-color>.dtfc-fixed-right {
        background-color: #086691 !important;
    }


    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        font-weight: bold;
        color: black;
    }

    thead * {
        text-align: center !important;
    }

</style>

{{-- <link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> --}}
@endsection

@section('content')

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

<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">

                <li class="nav-item ">
                    <a class="nav-link active" data-toggle="tab" href="#kt_apps_contacts_view_tab_2" role="tab">
                        <i class="flaticon2-checking"></i>Reports Table
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            <div id="chartdiv"></div>
            <!--End:: Tab  EGP FX Rate Table -->
            <!--Begin:: Tab USD FX Rate Table -->
            <div class="tab-pane active" id="kt_apps_contacts_view_tab_2" role="tabpanel">
                <x-table :tableTitle="__($view_name.' Report')" :tableClass="'kt_table_with_no_pagination'">
                    @slot('table_header')
                    <tr class="table-active text-center">
                        <th class="text-center absorbing-column">{{ __('Product Item') }}</th>
                        {{-- @foreach ($dates as $date) --}}
                        <th>{{ $firstReportData['first_report_date']  }}</th>
                        <th>{{ $secondReportData['full_date']  }}</th>
                        {{-- <th>{{ date('d-M-Y', strtotime()) }}</th> --}}
                        {{-- @endforeach --}}
                        <th>{{ __('Growth Rate %') }}</th>
                    </tr>
                    @endslot
                    @slot('table_body')
                    @php
                    $id = 0 ;
					$firstAllTotal = 0 ;
					$secondAllTotal = 0 ;
                    @endphp
                    @foreach ($mainItems as $mainItemName)
                    <tr class="group-color ">

                        <td class="white-text" style="cursor: pointer;" onclick="toggleRow('{{ $id }}')">
                            <i class="row_icon{{ $id }} flaticon2-up white-text"></i>
                            <b>{{ __($mainItemName) }}</b>
                        </td>
                        <td class="text-center white-text">
                            @php
                            $firstTotal = isset($report_data[$mainItemName]) ? sum_all_array_values($report_data[$mainItemName]) : 0 ;
							$firstAllTotal += $firstTotal ; 

                            @endphp
                            {{ number_format($firstTotal)  }}
                        </td>

                        <td class="text-center white-text">
                            @php
                            $secondTotal = isset($secondReportData['report_data'][$mainItemName]) ? sum_all_array_values($secondReportData['report_data'][$mainItemName]) : 0 ;
							$secondAllTotal += $secondTotal ; 
							
                            @endphp
                            {{ number_format($secondTotal) }}
                        </td>
                        <td class="text-center white-text">{{$firstTotal ? number_format(    ($secondTotal - $firstTotal) / $firstTotal *100    , 2 ) . ' %' : __('NA')  }} </td>
                    </tr>
					@php
						$secondReportSubs = $secondReportData['report_data'][$mainItemName]??[] ; 
						$secondSubItemsOrdered=isset($isDayNameReport) && $isDayNameReport ? App\Helpers\HArr::orderByDayNameForOneDimension($secondReportSubs) : $secondReportSubs;
						
						$firstReportSubs = $firstReportData['report_data'][$mainItemName]??[] ; 
						$firstSubItemsOrdered=isset($isDayNameReport) && $isDayNameReport ? App\Helpers\HArr::orderByDayNameForOneDimension($firstReportSubs) : $firstReportSubs;
						$subItemsOrdered = array_unique(array_merge(array_keys($firstReportSubs),array_keys($secondSubItemsOrdered)));
					@endphp
                    @foreach ( $subItemsOrdered as $subItemName )
					@php
                            $firstReportTotalForItem = $report_data[$mainItemName][$subItemName] ?? 0 ;
                            $secondReportTotalForItem = $secondReportSubs[$subItemName] ?? 0 ;
                            @endphp
							
					@if($firstReportTotalForItem == 0 && $secondReportTotalForItem ==0)
					@continue 
					@endif 
                    <tr class="row{{ $id }}  text-center" style="display: none">
                        <td class="text-left"><b>{{ $subItemName  }}</b></td>
                        <td class="text-center">
                            <span class="active-text-color"><b> {{ number_format($firstReportTotalForItem) }} </b></span>
                        </td>
                        <td class="text-center">
                            <span class="active-text-color"><b> {{ number_format($secondReportTotalForItem) }} </b></span>
                        </td>
                        <td>{{ $firstReportTotalForItem ? number_format(    ($secondReportTotalForItem - $firstReportTotalForItem) / $firstReportTotalForItem *100   , 2 ) . ' %' : __('NA') }} </td>
                    </tr>

                    @endforeach
                    <?php $id++;?>
                    @endforeach
					
					
					   <tr class="active-style text-center">
                                        <td class="active-style text-center" ><b>{{ __('Total') }}</b></td>
                

                    <td class="text-center active-style">
                        {{ number_format($firstAllTotal) }}
						</td>
						  <td class="text-center active-style">
                        {{ number_format($secondAllTotal) }}
						</td>
                  
						@php
							$finalGrowthRate = $firstAllTotal ? ($secondAllTotal - $firstAllTotal) / $firstAllTotal * 100 : 0 ;
						@endphp
                    <td class="text-center active-style">{{ number_format(  $finalGrowthRate ,2) }} %</td>
                    </tr>


                    @endslot
                </x-table>


            </div>
            <!--End:: Tab USD FX Rate Table -->
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>




@include('js_datatable')


{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
</script>
<script>
    function toggleRow(rowNum) {
        $(".row" + rowNum).toggle();
        $('.row_icon' + rowNum).toggleClass("flaticon2-down flaticon2-up");
    }

</script>
@endsection
