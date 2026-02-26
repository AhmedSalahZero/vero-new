@extends('layouts.dashboard')
@php
	use App\Helpers\HArr;
@endphp
@section('css')
<style>
.max-id-width{
	width:20px !important;
	min-width:20px !important;
	max-width:20px !important;
}
</style>
    <link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet"
        type="text/css" />
@endsection
@section('sub-header')
    {{ __($view_name) }}
@endsection
@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="kt-portlet kt-portlet--mobile">
                <div class="kt-portlet__head kt-portlet__head--lg">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon">
                            <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                        </span>
                        <h3 class="kt-portlet__head-title">
                            {{ __($view_name) }}
                        </h3>
                    </div>

                </div>
                <div class="kt-portlet__body">

                    <!--begin: Datatable -->

                    <!-- HTML -->
                    <div id="chartdiv" class="chartDiv"></div>

                    <!--end: Datatable -->
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="kt-portlet kt-portlet--mobile">
                <div class="kt-portlet__head kt-portlet__head--lg">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon">
                            <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                        </span>
                        <h3 class="kt-portlet__head-title mt-4">

                            <b> {{__('From : ')}} </b>{{ $dates['start_date']}}
                            <b> - </b>
                            <b> {{__('To : ') }}</b> {{ $dates['end_date']}}
                            <br>

                            <span class="title-spacing"><b> {{__('Last Updated Data Date : ') }}</b> {{ $last_date}}</span>
                        </h3>
                    </div>

                </div>
                <div class="kt-portlet__body">
					<h2 class="text-green pl-4"> {{ __('Sales Revenues') }} : {{ number_format($salesToDate) }}</h2>
                    <!--begin: Datatable -->


                    <x-table  :tableClass="'kt_table_with_no_pagination_no_scroll '">
                        @slot('table_header')
                            <tr class="table-active remove-max-class text-center">
                                <th class="max-id-width">#</th>
                                <th>{{ __(ucwords(str_replace('_',' ',$type))) }}</th>
                                <th>{{ __('Expense Value') }}</th>
                                <th>{{ __('Expense %') }}</th>
                                <th>{{ __('Rev %') }}</th>
                            

                            </tr>
                        @endslot
                        @slot('table_body') 

                            <?php $total = array_sum(array_column($report_view_data,'Sales Value')); $total_count = (isset($report_count_data) && count($report_count_data) > 0) ? array_sum(array_column($report_count_data,'Count')) : 0; ?>
                            @foreach ($report_view_data as $key => $item)
                             <tr>
                                 <th class="max-id-width">{{$key+1}}</th>
                                <th>{{$item['item']?? '-'}}</th>
								@php
									$currentValue = $item['Sales Value']??0 ;
								@endphp
                                <td class="text-center">{{number_format($currentValue)}}</td>
                                <td class="text-center">{{$total == 0 ? 0 : number_format((($item['Sales Value']/$total)*100) , 1) . ' %'}}</td>
								<td>{{ $salesToDate ? number_format($currentValue / $salesToDate* 100,2) . ' %' : '-'  }}</td>
                             
                             </tr>
                            @endforeach
                            <tr class="table-active text-center">
                                <th colspan="2">{{__('Total')}}</th>
                                <td class="hidden"></td>
                                <td>{{number_format($total)}}</td>
                                <td>100 %</td>
                                <td>{{ $salesToDate? number_format($total / $salesToDate *100,2) .' %': '-'  }}</td>
                              
                            </tr>
                        @endslot
                    </x-table>


                    <!--end: Datatable -->
                </div>
            </div>
        </div>
		@php
			$report_view_data_formatted = HArr::numberFormatTwoDimArrBasedOnKey($report_view_data,'Sales Value');

		@endphp
        <input type="hidden" id="total" data-total="{{ json_encode($report_view_data_formatted) }}">
    </div>
@endsection
@section('js')

    {{-- Old Chart --}}

<script src="https://www.amcharts.com/lib/4/core.js"></script>
<script src="https://www.amcharts.com/lib/4/charts.js"></script>
<script src="https://www.amcharts.com/lib/4/themes/animated.js"></script>
<script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
</script>
<!-- Chart code -->
<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("chartdiv", am4charts.PieChart);

        // Add data
        chart.data = $('#total').data('total');
        // Add and configure Series
        var pieSeries = chart.series.push(new am4charts.PieSeries());
        pieSeries.dataFields.value = "Sales Value";
        pieSeries.dataFields.category = "item";
        pieSeries.innerRadius = am4core.percent(50);
        // arrow
        pieSeries.ticks.template.disabled = true;
        //number
        pieSeries.labels.template.disabled = true;

        var rgm = new am4core.RadialGradientModifier();
        rgm.brightnesses.push(-0.8, -0.8, -0.5, 0, -0.5);
        pieSeries.slices.template.fillModifier = rgm;
        pieSeries.slices.template.strokeModifier = rgm;
        pieSeries.slices.template.strokeOpacity = 0.4;
        pieSeries.slices.template.strokeWidth = 0;

        chart.legend = new am4charts.Legend();
        chart.legend.position = "right";
    chart.legend.scrollable = true;

    }); // end am4core.ready()
</script>


@endsection
