@extends('layouts.dashboard')
@php
use App\Helpers\HArr;
use App\Helpers\HMath;
use MathPHP\Statistics\Correlation ;
@endphp
@section('css')
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
@endsection


@section('dash_nav')
<style>
	table , table * {
		font-size : 14px !important;
	}
    .max-column-th-class {
        width: 30% !important;
        min-width: 30% !important;
        max-width: 30% !important;
    }
	.js-parent-to-table{
		overflow:scroll;
		margin-bottom:20px !important;
	}

	.expandable-percentage-input {
		max-width: 50px !important;
		min-width: 50px !important;
		text-align: center !important;
	}

    .three-dots-parent {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .b-bottom {
        border-bottom: 1px solid green !important;
    }

    .expandable-amount-input {
        max-width: 60px !important;
        min-width: 60px !important;
        width: 60px !important;
    }

    table:not(.table-condensed) thead th,
    table:not(.table-condensed) tbody td {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    input {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    .chartdiv_two_lines {
        width: 100%;
        height: 500px;
    }

    .chartDiv {
        max-height: 500px !important;
    }

    .margin__left {
        border-left: 2px solid #366cf3;
    }

    .sky-border {
        border-bottom: 1.5px solid #CCE2FD !important;
    }

    .kt-widget24__title {
        color: black !important;
    }

</style>

@endsection
@section('css')
<link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />
<style>
    table {
        white-space: nowrap;
    }

    /* .dataTables_wrapper{max-width: 100%;  padding-bottom: 50px !important;overflow-x: overlay;max-height: 4000px;} */

</style>
@endsection
@section('content')
<div class="kt-portlet">


</div>

<div class="tab-content  kt-margin-t-20">
    @php
    $index = 0 ;
    @endphp


    <div class="tab-pane  active " id="kt_apps_contacts_view_tab_main" role="tabpanel">













<div class="row">



    <div class="col-md-12">
        <div class="kt-portlet kt-portlet--tabs">

            <div class="kt-portlet__body pt-0">


                <div class="tab-content  kt-margin-t-20">

                    <div class="tab-pane active" id="FullySecuredOverdraftchartkt_apps_contacts_view_tab_1" role="tabpanel">


                        <div class="row">
                            <div class="col-md-4">
                                <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Income Statement Summary Fig In Million') }} </h3>
                            </div>
                            <div class="col-md-8 mb-3">
                                @php
                                $currentModalId = 'spread-rate-sensitivity';
                                $currentModalTitle = __('Spread Rate Sensitivity');
                                $spreadRates = [];
                                @endphp
                                <button class="btn btn-sm btn-brand btn-elevate btn-pill text-white" data-toggle="modal" data-target="#{{ $currentModalId }}">{{ $currentModalTitle }}</button>
								@if($withSensitivity)
                                <a href="{{ route('view.results.dashboard',['company'=>$company,'study'=>$study->id]) }}" class="btn btn-sm btn-brand btn-elevate btn-pill text-white" >{{ __('Reset Sensitivity') }}</a>
								@endif 
                                @include('non_banking_services.dashboard._spread-rate-sensitivity-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle])
                            </div>
		

                            @include('non_banking_services.dashboard._income-statement')
							@if($withSensitivity)
							
                            @include('non_banking_services.dashboard._income-statement',['formattedResult'=>$sensitivityFormattedResult])
							
							@endif 
							




                            @include('non_banking_services.dashboard._income-statement-percentage-of',['formattedResult'=>$formattedResult])
							
							@if($withSensitivity)
                            @include('non_banking_services.dashboard._income-statement-percentage-of',['formattedResult'=>$sensitivityFormattedResult])
							@endif 






                        </div>

                    </div>


                </div>
            </div>
        </div>


    </div>
	@if(!$withSensitivity)
    <div class="col-md-6 max-card-height">
        <div class="kt-portlet kt-portlet--tabs">

            <div class="kt-portlet__body pt-0">


                <div class="tab-content  kt-margin-t-20">

                    <div class="tab-pane active" id="FullySecuredOverdraftchartkt_apps_contacts_view_tab_1" role="tabpanel">

				
                        <div class="row">


                            <div class="col-md-12 ">

                                <div class="row mb-3 ml-4 b-bottom">
                                    <div class="col-6">
                                        <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Choose Revenue Stream') }} </h3>
                                    </div>
                                    <div class="col-md-6 ">
                                        <select js-refresh-three-line-chart class="form-control">
                                            @foreach($lineChart as $id => $arr)
                                            <option value="{{ $id }}"> {{ $titlesMapping[$id]['title'] }} </option>
                                            @endforeach
                                        </select>
                                    </div>



                                </div>
                                <div class="chartdiv_two_lines" id="three-line-chart-id-chart"></div>
                                @foreach($lineChart as $chartName => $currentChartData )
                                <input type="hidden" class="three-line-chart-data-class" data-chart-name="{{ $chartName }}" data-chart-data="{{ json_encode($currentChartData) }}">
                                @endforeach
                            </div>

                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 max-card-height">
        <div class="kt-portlet kt-portlet--tabs">

            <div class="kt-portlet__body pt-0">


                <div class="tab-content  kt-margin-t-20">

                    <div class="tab-pane active" id="FullySecuredOverdraftchartkt_apps_contacts_view_tab_1" role="tabpanel">


                        <div class="row">






                            <div class="col-md-12 ">

                                <div class="row mb-3 ml-4 b-bottom">
                                    <div class="col-6">
                                        <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Revenue Stream Breakdown') }} </h3>
                                    </div>




                                </div>
                                <div id="bar-chart-id" class="chartdashboard"></div>
                             
                            </div>

                        </div>

                    </div>


                </div>
            </div>
        </div>
    </div>
	@endif	

    <div class="col-md-12">
        <div class="kt-portlet kt-portlet--tabs">

            <div class="kt-portlet__body pt-0">


                <div class="tab-content  kt-margin-t-20">

                    <div class="tab-pane active" id="FullySecuredOverdraftchartkt_apps_contacts_view_tab_1" role="tabpanel">


                        <div class="row">
                            <div class="col-md-12">
                                <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Cost And Expense Summary') }} </h3>
                            </div>

                       @include('non_banking_services.dashboard._expenses',['formattedExpenses'=>$formattedExpenses])
					   @if($withSensitivity)
                       @include('non_banking_services.dashboard._expenses',['formattedExpenses'=>$sensitivityFormattedExpenses])
					   
					   @endif 

                       @include('non_banking_services.dashboard._expenses-percentage-of',['formattedExpenses'=>$formattedExpenses])

					   @if($withSensitivity)
                       @include('non_banking_services.dashboard._expenses-percentage-of',['formattedExpenses'=>$sensitivityFormattedExpenses])
					   @endif 


                            







                        </div>

                    </div>


                </div>
            </div>
        </div>


    </div>





</div>



<!--end:: Widgets/Stats-->


</div>





</div>
@endsection
@section('js')
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>








<!--begin::Page Scripts(used by this page) -->
<script src="{{url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js')}}" type="text/javascript"></script>
<script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js')}}" type="text/javascript"></script>
<script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/lib.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/jquery.input.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/repeater.js')}}" type="text/javascript"></script>
<script src="{{url('assets/js/demo1/pages/crud/forms/widgets/form-repeater.js')}}" type="text/javascript"></script>

<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

{{-- pie chart --}}
<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("three-line-chart-id-chart", am4charts.XYChart);
        var data = [];
        //
        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        chart.data = data;

        // Create axes
        var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        dateAxis.renderer.minGridDistance = 50;
        dateAxis.dateFormats.setKey("year", "yyyy");
        dateAxis.periodChangeDateFormats.setKey("year", "yyyy");
        dateAxis.tooltipDateFormat = "yyyy";
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

        createAxisAndSeries("revenue_value", "{{ __('Revenues Value ') }}", false, "circle");
        createAxisAndSeries("growth_rate", "{{ __('Growth Rate %') }}", true, "triangle");
        //   createAxisAndSeries("revenue_percentage", "{{ __('Revenue %') }}", true, "rectangle");

        // Add legend
        chart.legend = new am4charts.Legend();

        // Add cursor
        chart.cursor = new am4charts.XYCursor();



    }); // end am4core.ready()



    am5.ready(function() {

        // Create root element
        // https://www.amcharts.com/docs/v5/getting-started/#Root_element
        var root = am5.Root.new("bar-chart-id");
        root.numberFormatter.set("numberFormat", "#,###.##");

        // Set themes
        // https://www.amcharts.com/docs/v5/concepts/themes/
        root.setThemes([
            am5themes_Animated.new(root)
        ]);


        // Create chart
        // https://www.amcharts.com/docs/v5/charts/xy-chart/
        var chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false
            , panY: false
            , wheelX: "panX"
            , wheelY: ""
            , layout: root.verticalLayout
        }));

        // Add scrollbar
        // https://www.amcharts.com/docs/v5/charts/xy-chart/scrollbars/
        chart.set("scrollbarX", am5.Scrollbar.new(root, {
            orientation: "horizontal"
        }));
        var chartData = @json($barChart);

        var data = chartData;





        // Create axes
        // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
        var xRenderer = am5xy.AxisRendererX.new(root, {});
        var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
            categoryField: "year"
            , renderer: xRenderer
            , tooltip: am5.Tooltip.new(root, {}),

        }));

        xRenderer.grid.template.setAll({
            location: 1
        })

        xAxis.data.setAll(data);

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            min: 0
            , renderer: am5xy.AxisRendererY.new(root, {
                strokeOpacity: 0.1
            })
        }));


        // Add legend
        // https://www.amcharts.com/docs/v5/charts/xy-chart/legend-xy-series/
        var legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50
            , x: am5.p50
        }));


        // Add series
        // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
        function makeSeries(name, fieldName) {
            var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: name
                , stacked: true
                , xAxis: xAxis
                , yAxis: yAxis
                , valueYField: fieldName
                , categoryXField: "year"
            }));

            series.columns.template.setAll({
                tooltipText: "{name}, {categoryX}: {valueY}"
                , tooltipY: am5.percent(10)
            });
            series.data.setAll(data);

            // Make stuff animate on load
            // https://www.amcharts.com/docs/v5/concepts/animations/
            series.appear();

            series.bullets.push(function() {
                return am5.Bullet.new(root, {
                    sprite: am5.Label.new(root, {
                        text: "{valueY}"
                        , fill: root.interfaceColors.get("alternativeText")
                        , centerY: am5.p50
                        , centerX: am5.p50
                        , populateText: true
                    })
                });
            });

            legend.data.push(series);
        }
		
        makeSeries("Leasing", "leasing");
        makeSeries("Direct Factoring", "direct-factoring");
        makeSeries("Reverse Factoring", "reverse-factoring");
        makeSeries("Portfolio Mortgage", "portfolio-mortgage");
        makeSeries("Microfinance", "microfinance");


        // Make stuff animate on load
        // https://www.amcharts.com/docs/v5/concepts/animations/
        chart.appear(1000, 100);

    }); // end am5.ready()


    //three lines chart

</script>

<script>
    $(function() {
        $(document).on('change', 'select[js-refresh-three-line-chart]', function(e) {
            let chartId = $(this).val();
            var chartDataArr = $('.three-line-chart-data-class[data-chart-name="' + chartId + '"]').attr('data-chart-data');
            if (chartDataArr) {
                chartDataArr = JSON.parse(chartDataArr);
            } else {
                chartDataArr = {};
            }
            let currentChartId = 'three-line-chart-id-chart';
            am4core.registry.baseSprites.find(c => c.htmlContainer.id === currentChartId).data = chartDataArr
        })

    })

</script>
<script>
    $(function() {
        $('select[js-refresh-three-line-chart]').trigger('change')
    })

</script>

{{-- <script src="{{url('assets/js/demo1/pages/crud/forms/validation/form-widgets.js')}}" type="text/javascript"></script> --}}

<!--end::Page Scripts -->

<script src="/custom/js/non-banking-services/common.js"></script>
<script>
$(function(){
$('.collapse-before-me').trigger('click')
	
})
</script>
@endsection
