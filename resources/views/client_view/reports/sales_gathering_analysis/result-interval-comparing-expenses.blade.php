@extends('layouts.dashboard')
@php
	use App\Helpers\HArr;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<style>
.max-id-width{
	width:20px !important;
	min-width:20px !important;
	max-width:20px !important;
}

    .chartdiv {
        width: 100% !important;
        height: 500px !important;
    }

</style>
@endsection
@section('sub-header')
{{ __($view_name) }}
@endsection
@section('content')
<div class="row">
    <?php $intervals = ['First'=>'_one', 'Second' => '_two']; ?>
    @foreach ($intervals as $interval_name => $name)

    <div class="col-md-6">
        <div class="kt-portlet kt-portlet--mobile">
            <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
                    <span class="kt-portlet__head-icon">
                        <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                    </span>
                    <h3 class="kt-portlet__head-title">
                        <b> {{__('From : ')}} </b>{{ $dates['start_date'.$name]}}
                        <b> - </b>
                        <b> {{__('To : ') }}</b> {{ $dates['end_date'.$name]}}
                        <br>
							
                        <span class="title-spacing"><b> {{__('Last Updated Data Date : ') }}</b> {{ $last_date}}</span>
                        <br>
                    </h3>
                </div>

            </div>
            <div class="kt-portlet__body">

                <!--begin: Datatable -->

                <!-- HTML -->
                <div id="chartdiv{{$name}}" class="chartdiv"></div>

                <!--end: Datatable -->
            </div>
        </div>
    </div>
    <?php $report_name = 'result_for_interval'.$name?>
    <input type="hidden" id="data{{$name}}" data-total="{{ json_encode($$report_name) }}">
    @endforeach
</div>
<div class="row">
    {{-- Tables --}}
    @foreach ($intervals as $interval_name => $name)
    <?php
                $report_name = 'result_for_interval'.$name ;
                $report_count_data = 'count_result_for_interval'.$name ;
            ?>

    <div class="col-md-6">
        <div class="kt-portlet kt-portlet--mobile">
            <div class="kt-portlet__head kt-portlet__head--lg">
                <div class="kt-portlet__head-label">
                    <span class="kt-portlet__head-icon">
                        <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                    </span>
                    <h3 class="kt-portlet__head-title  mt-4">

                        <b> {{__('From : ')}} </b>{{ $dates['start_date'.$name]}}
                        <b> - </b>
                        <b> {{__('To : ') }}</b> {{ $dates['end_date'.$name]}}
                        <br>

                        <span class="title-spacing"><b> {{__('Last Updated Data Date : ') }}</b> {{ $last_date}}</span>
                    </h3>
                </div>

            </div>
            <div class="kt-portlet__body">
			@php
				$currentSalesValue = $salesToDateForIntervals[$name] ?? 0 ;
			
			@endphp

                <!--begin: Datatable -->
			<h2 class="text-green pl-4"> {{ __('Sales Revenues') }} : {{ number_format($currentSalesValue) }}</h2>
                <x-table :tableClass="'kt_table_with_no_pagination_no_scroll_no_search_no_info	'">
                    @slot('table_header')
                    <tr class="table-active remove-max-class text-center">
                        <th class="max-id-width">#</th>
                        <th class="text-center">{{ __(ucwords(str_replace('_',' ',$type))) }}</th>
                        <th class="text-center">{{ __('Expense Values') }}</th>
                        <th class="text-center">{{ __('Expense %') }}</th>
						@if($name == $latestReport)
                        <th class="text-center">{{ __('GR %') }}</th>
						@endif 
						   <th>{{ __('Rev %') }}</th>
                   
                    </tr>
                    @endslot
                    @slot('table_body')
					
                    <?php $total = array_sum(array_column($$report_name,'Sales Value'));
					$totals[$name] = $total ;
                    $total_count = (isset($$report_count_data) && count($$report_count_data) > 0) ? array_sum(array_column($$report_count_data,'Count')) : 0; ?>
                    @foreach ($$report_name as $key => $item)
                    <tr>
                        <th class="max-id-width">{{$key+1}}</th>
				
                        <th>{{$item['item']?? '-'}}</th>
                        <td class="text-center">{{number_format($item['Sales Value']??0)}}</td>
                        <td class="text-center">{{$total == 0 ? 0 : number_format((($item['Sales Value']/$total)*100) , 1) . ' %'}}</td>
						@php
							$currentItemValue = $item['Sales Value'] ?? 0 ;
							
						@endphp
						@if($name == $latestReport)
						@php
							$otherIntervalCurrentValue = $latestReport == '_two' ? HArr::searchForCorrespondingItem($result_for_interval_one,$item['item']) :HArr::searchForCorrespondingItem($result_for_interval_two,$item['item']); 
						@endphp
                        <td class="text-center">{{$otherIntervalCurrentValue ? number_format(($currentItemValue /$otherIntervalCurrentValue  -1) *100,2) .' %' : 0 }}</td>
						@endif
						<td>{{ $currentSalesValue ? number_format($currentItemValue / $currentSalesValue* 100,2) . ' %' : '-'  }}</td>
                    
                    </tr>
                    @endforeach
                    <tr class="table-active text-center">
                        <th colspan="2">{{__('Total')}}</th>
                        <td class="hidden"></td>
                        <td>{{number_format($total)}}</td>
                        <td>100 %</td>
						@php
							
						$currentTotal = 0 ;
						@endphp
						@if($name == $latestReport)
						@php
							$totalForOne = array_sum(array_column($result_for_interval_one,'Sales Value')) ;
							$totalForTwo = array_sum(array_column($result_for_interval_two,'Sales Value')) ;
						if($latestReport == '_two'){
							$currentTotal =  $totalForOne ? ($totalForTwo /$totalForOne - 1) * 100 : 0 ;
						}
						if($latestReport == '_one'){
						
							$currentTotal =  $totalForTwo ? ($totalForOne /$totalForTwo - 1) * 100 : 0 ;
						}		
						@endphp
                        <td>{{ number_format($currentTotal,2) . ' %' }}</td>
						@endif
						    <td>
								{{ $currentSalesValue ? number_format($total / $currentSalesValue * 100,2) . ' %' : 0 }}  
							</td>
                     
                    </tr>
                    @endslot
                </x-table>

                <!--end: Datatable -->
            </div>
        </div>
    </div>
    @endforeach
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
@foreach ($intervals as $interval_name => $name)
<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("chartdiv" + '{{$name}}', am4charts.PieChart);

        // Add data
        chart.data = $('#data' + '{{$name}}').data('total');
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
@endforeach



@endsection
