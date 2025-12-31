@extends('layouts.dashboard')
@section('css')

<style>


.DataTables_Table_0_filter{
	float:left;
	
}
.dt-buttons button {
	color:#366cf3 !important;
	border-color:#366cf3 !important;
}
.dataTables_wrapper > .row > div.col-sm-6:first-of-type {
	flex-basis:20% !important;
}
.dataTables_wrapper > .row label{
	margin-bottom:0 !important;
	padding-bottom:0 !important ;
}
.kt-portlet__head-title,
.fa-layer-group
{
	color:#366cf3 !important;
	border-bottom:2px solid  #366cf3;
	padding-bottom:.5rem !important;
}
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
   
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            <!--Begin:: Tab  EGP FX Rate Table -->
            <?php
                    array_push($days_names, 'Total');
                    array_push($days_names, 'Day_Sales_Percentages');
                    ?>

            <!--End:: Tab  EGP FX Rate Table -->

            <!--Begin:: Tab USD FX Rate Table -->
            <div class="tab-pane active" id="kt_apps_contacts_view_tab_2" role="tabpanel">
                <x-table :tableTitle="__($view_name.' Report')" :tableClass="'kt_table_with_no_pagination'">
                    @slot('table_header')
                    <tr class="table-active text-center">
                        {{-- <th>{{ __('Collapse') }}</th> --}}
                        <th class="text-center absorbing-column">{{ __('Day') }}</th>
                        @foreach ($dates as $date)
                        <th>{{ date('d-M-Y', strtotime($date)) }}</th>
                        @endforeach
                        <th>{{ __('Total') }}</th>
                    </tr>
                    @endslot
                    @slot('table_body')

                    <?php $id =1 ;?>
                    @php
                    sortReportForTotals($report_data);
                    @endphp
                    @foreach ($report_data as $day_name => $day_channels_data)
                    <?php $chart_data = [];?>
                    @if ($day_name != 'Total' && $day_name != 'Growth Rate %')

                
                    <tr class="group-color ">
                        {{-- <td class="text-center" style="cursor: pointer;"
                                            onclick="toggleRow('{{ $id }}')"><i class="row_icon{{ $id }} flaticon2-up white-text"></i>
                        </td> --}}
                        <td class="white-text" style="cursor: pointer;" onclick="toggleRow('{{ $id }}')">
                            <i class="row_icon{{ $id }} flaticon2-up white-text"></i>
                            <b>{{ __($day_name) }}</b>
                        </td>
                        {{-- Total --}}
                        <?php $total_per_day = $day_channels_data['Total'] ?? [];
                                        unset($day_channels_data['Total']); ?>
                        {{-- Growth Rate % --}}
                        <?php $growth_rate_per_day = $day_channels_data['Growth Rate %'] ?? [];
                                        unset($day_channels_data['Growth Rate %']); ?>

                        @foreach ($dates as $date)
                        <td class="text-center white-text">{{ number_format($total_per_day[$date] ?? 0) . '  [ GR '.number_format($growth_rate_per_day[$date] ?? 0) . ' % ]'}}
                        </td>
                        @endforeach
                        <td class="text-center white-text">{{number_format(array_sum($total_per_day??[]),0)}}</td>
                    </tr>

                    @php
                    sortSubItems($day_channels_data);
                    @endphp
                    @foreach ($day_channels_data as $channel_name => $channel_section)

                    <tr class="row{{ $id }}  text-center" style="display: none">
                        {{-- <td></td> --}}
                        <td class="text-left"><b>{{ $channel_name  }}</b></td>

                        @foreach ($dates as $date)
                        <td class="text-center">
                            {{ number_format(($channel_section[$name_of_report_item][$date] ?? 0),0)   }}
                            <span class="active-text-color color-{{ getPercentageColor($channel_section['Growth Rate %'][$date]??0) }}"><b> {{ ' [ '.number_format(($channel_section['Growth Rate %'][$date]??0), 1) . ' %  ]' }}</b></span>
                        </td>
                        @endforeach
                        <td>{{number_format(array_sum($channel_section[$name_of_report_item]??[]),0)}}</td>
                    </tr>

                    @endforeach







                    @elseif ($day_name == 'Total' || $day_name == 'Growth Rate %')
                    <tr class="active-style text-center">
                        <td class="active-style text-center"><b>{{ __($day_name) }}</b></td>
                        {{-- <td class="hidden"></td> --}}
                        <?php $decimals = $day_name == 'Growth Rate %' ? 2 : 0; ?>
                        @foreach ($dates as $date)

                        <td class="text-center active-style">
                            {{ number_format($day_channels_data[$date] ?? 0,$decimals) . ($decimals == 0 ? '' : ' %')}}</td>
                        @endforeach
                        <td class="text-center active-style">{{$day_name == 'Growth Rate %' ? "-" : number_format(array_sum($day_channels_data  ?? []),0)}}</td>
                    </tr>
                    @endif
                    <?php $id++ ;?>
                    @endforeach


                    @endslot
                </x-table>

            </div>
            <!--End:: Tab USD FX Rate Table -->
        </div>
    </div>
</div>

@endsection

@push('css')


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />

<style>
    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        font-weight: bold;
        color: black;
    }

    table.dataTable tbody tr.group-color>.dtfc-fixed-left,
    table.dataTable tbody tr.group-color>.dtfc-fixed-right {
        background-color: #086691 !important;
    }


    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        color: black;
        font-weight: bold;
    }

    thead * {
        text-align: center !important;
    }

</style>
@endpush
@push('js')
@include('js_datatable')
@endpush

@section('js')
<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
</script>
<script>
    function toggleRow(rowNum) {
        $(".row" + rowNum).toggle();
        $('.row_icon' + rowNum).toggleClass("flaticon2-down flaticon2-up");
    }

</script>
@endsection
