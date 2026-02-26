@extends('layouts.dashboard')
@section('dash_nav')
@include('client_view.home_dashboard.main_navs-income-statement',['active'=>'breadkdown_dashboard'])

@endsection
@section('css')
<link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />
<style>
    table {
        white-space: nowrap;
    }
</style>

<style>
    .swal-wide {
        width: 850px;
    }

    .custom_width_classs {
        width: 600px;
    }

    .close_custom_modal {
        position: absolute;
        top: 5px;
        right: 47px;
        color: #c8c3c6;
        font-size: 1.5rem;
    }

    .datatable_modal_div {
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        position: fixed;
        overflow-y: scroll;
        overflow-x: hidden;
        max-height: 80vh;
        width: 90%;
        z-index: 9;
        padding: 3rem 2rem;

    }

    .container__fixed {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.3);
        display: none;
        overflow: scroll;

    }

    .header___bg {
        background-color: #086691 !important;
        color: #fff !important;
    }

</style>
@endsection
@section('content')

@php
// $total_of_main_with_rows_with_depreciation = [0,0,0,0,0,0,0,0];
// $total_of_main_with_rows_depreciation = [0,0,0,0,0,0,0,0];
$earningBeforeTaxes = 0 ;
$totalOfDepreactionAndAmortization = 0;


@endphp

<div class="kt-portlet">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title head-title text-primary">
                {{ __('Dashboard Results') }}
            </h3>
        </div>
    </div>
    <div class="kt-portlet__body">
        <form action="{{route('dashboard.breakdown.incomeStatement',['company'=>$company,'reportType'=>Request()->segments()[4]])}}" method="POST">
            @csrf
            <div class="form-group row">
                <div class="col-md-4">
                    <label>{{__('Choose Income Statement')}} </label>
                    <select id="income_statement_select_id" class="form-control kt-selectpicker" name="income_statement_id">
                        <option value="">{{__('Select')}}</option>
                        @foreach (getIncomeStatementForCompany($company->id) as $item)
                        <option value="{{$item->id}}" {{@$incomeStatement->id !=  $item->id ?: 'selected'}}> {{__($item->getName())}}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>{{ __('Start Date') }}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" id="start_date_input_id" name="start_date" required value="{{ $start_date }}"  class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>{{ __('End Date') }}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" id="end_date_input_id" name="end_date" required value="{{ $end_date}}"  class="form-control" placeholder="Select date" />
                        </div>
                    </div>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-1">
                    <label> </label>
                    <div class="kt-input-icon">
                        <button type="submit" class="btn active-style">{{ __('Run') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Title --}}
<div class="row">

    <div class="kt-portlet ">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title head-title text-primary text-capitalize" style="font-size:2rem !important">
                    {{ __('Income Statement') }} [{{ getLastSegmentInRequest() }}] {{ __('Results') }}
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body  kt-portlet__body--fit">
            <div class="row row-no-padding row-col-separator-xl">
                @foreach ($types as $singleType=>$type )
				@if ($singleType == 'Corporate Taxes')
					@continue
				@endif
                @php
                $color = 'primary';
                $currentIndex = array_search($singleType,array_keys($types)) ;
                @endphp

                <div @if($currentIndex==0 || $currentIndex==1 ||$currentIndex==2 ) class="col-md-4" @else class="col-md-4" @endif>
                    <!--begin::Total Profit-->
                    <div class="kt-widget24 text-center">
                        <div class="kt-widget24__details">
                            <div class="kt-widget24__info w-100">
                                <h4 class="kt-widget24__title font-size justify-content-between">
                                    <span style="font-size:1.75rem !important; white-space: nowrap !important;">{{ __( ucwords(str_replace('_',' ',$type))) }}</span>
                                </h4>
                            </div>
                        </div>
                        @php
                        $total_of_sales_revenue = get_total_for_group_by_key($reports_data,'Sales Revenue')['total_with_depreciation'] ?? 0;
                        @endphp


                        <div class="kt-widget24__details">
                            <span class="kt-widget24__stats kt-font-{{$color}}" style="font-size:1.75rem">
                                @php
                                $totalsOfEachRows = get_total_for_group_by_key($reports_data , $type) ;
                                $total_of_each_group_with_depreciation =$totalsOfEachRows['total_with_depreciation'] ;
                                $total_of_each_group_depreciation = $totalsOfEachRows['total_depreciation'] ;
                                @endphp
                                {{ number_format($total_of_each_group_with_depreciation) }}
                                @if($singleType == __('Sales Revenue'))
                                @php
                                $total_of_sales_revenue = $total_of_each_group_with_depreciation ;
                                @endphp
                                @endif
                                @if($singleType != __('Sales Revenue'))
                                <span style="color:black !important;">

                                    [ {{ $total_of_sales_revenue ? number_format($total_of_each_group_with_depreciation / $total_of_sales_revenue  *100 , 2  ) . ' %' : 0 }} ]
                                </span>
                                @endif
                                @php
                                $total_of_main_with_rows_with_depreciation[$singleType] = $total_of_each_group_with_depreciation ;
                                $total_of_main_with_rows_depreciation[$singleType] = $total_of_each_group_depreciation ;
								
                                @endphp

                        </div>

                        <input type="hidden" id="top_for_{{ convertStringToClass($type) }}" value="{{ $top_data[$type]['item'] ?? '' }}">
                        <input type="hidden" id="value_for_{{ convertStringToClass($type) }}" value="{{ number_format(($top_data[$type]['Sales Value']??0)) }}">


                        <div class="progress progress--sm">
                            <div class="progress-bar kt-bg-{{$color}}" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="kt-widget24__action">
                            <span class="kt-widget24__change">

                            </span>
                            <span class="kt-widget24__number">

                            </span>
                        </div>
                    </div>
                </div>


                @endforeach


                <hr>

                <br>

                <br>


            </div>
        </div>







    </div>
    <input type="hidden" id="monthly_data" data-total="{{ json_encode($formattedDataForChart ?? []) }}">
    <input type="hidden" id="accumulated_data" data-total="{{ json_encode($monthlyChartCumulative ?? []) }}">

    <div class="kt_portlet w-100">
        <div class="kt-portlet__body">
            <div class="row">
                {{-- Total Facilities --}}
                <div class="col-md-12">
                    <div class="kt-portlet ">

                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="kt-widget12__chart">
                                        <h4> {{ __('Monthly Sales Values') }} </h4>
                                        <div id="monthly_chartdiv" class="chartdashboard"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="kt-widget12__chart">
                                        <!-- HTML -->
                                        <h4> {{ __('Accumulated Sales Values') }} </h4>
                                        <div id="accumulated_chartdiv" class="chartdashboard"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title head-title text-primary" style="font-size:2rem !important">
                    {{ __('Profitability Results') }}
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body  kt-portlet__body--fit">
            <div class="row row-no-padding row-col-separator-xl">
                @foreach ([5=>'Gross Profit <br> &nbsp;',13=>'Earning Before Interest Taxes <br> Depreciation Amortization - EBITDA',15=>'Earning Before Interest Taxes - EBIT <br> &nbsp;',19=>'Earning Before Taxes - EBT',21=>__('Corporate Taxes'),23=>'Net Profit'] as $idOfItem=>$mainWithoutSubItemsName )
                @php
                $color = 'primary';
                @endphp


                <div @if( $idOfItem==13 ) class="col-md-4" @else class="col-md-4" @endif>
                    <!--begin::Total Profit-->
                    <div class="kt-widget24 text-center">
                        <div class="kt-widget24__details">
                            <div class="kt-widget24__info w-100">
                                <h4 class="kt-widget24__title font-size justify-content-between">
                                    <span style="font-size:1.75rem !important;text-align:left!important;color;black!important; ">{!! __( ucwords(str_replace('_',' ',$mainWithoutSubItemsName))) !!}</span>
                                </h4>
                            </div>
                        </div>




                        @if($idOfItem == 5)
                        @php
                        $salesRevenue = $total_of_main_with_rows_with_depreciation[('Sales Revenue')] ?? 0 ;

                        $costOfGoods = $total_of_main_with_rows_with_depreciation['Cost Of Goods / Service Sold'] ??0;

                        $totalOfSub = $salesRevenue- $costOfGoods ;

                        @endphp

                        @endif
                        @if($idOfItem == 13)
                        @php
                        $salesRevenueWithDepreciation = $total_of_main_with_rows_with_depreciation[('Sales Revenue')] ??0 ;
                        $costOfGoodsWithDepreciation = $total_of_main_with_rows_with_depreciation[('Cost Of Goods / Service Sold')]?? 0;
                        // $grossProfitWithDepreciation = $total_of_main_with_rows_with_depreciation[('Gross Profit')] ?? 0 ;
                        $marketingExpensesWithDepreciation = $total_of_main_with_rows_with_depreciation[('Marketing Expenses')] ?? 0 ;
                        $salesExpensesWithExpensesWithDepreciation = $total_of_main_with_rows_with_depreciation[('Sales Expenses')] ?? 0 ;
                        $generalEXpensesWithDepreciation = $total_of_main_with_rows_with_depreciation['General Expenses'] ?? 0;
                        $costOfGoodsRowDepreciation = $total_of_main_with_rows_depreciation['Cost Of Goods / Service Sold'] ?? 0 ;
                        $grossProfitRowDepreciation = $total_of_main_with_rows_depreciation['Gross Profit'] ?? 0;
                        $marketingExpensesDepreciation = $total_of_main_with_rows_depreciation['Marketing Expenses'] ?? 0;
                        $salesExpensesDepreciation = $total_of_main_with_rows_depreciation['Sales Expenses'] ?? 0;
                        $generalExpensesDepreciation = $total_of_main_with_rows_depreciation['General Expenses'] ?? 0;
                        $totalOfDepreactionAndAmortization = $salesRevenueWithDepreciation - $costOfGoodsWithDepreciation - $marketingExpensesWithDepreciation - $salesExpensesWithExpensesWithDepreciation - $generalEXpensesWithDepreciation ;
                        $totalDepreciation = $costOfGoodsRowDepreciation + $marketingExpensesDepreciation + $salesExpensesDepreciation + $generalExpensesDepreciation ;

                        $totalOfSub = $totalOfDepreactionAndAmortization + $totalDepreciation


                        @endphp
                        @endif


                        @if($idOfItem == 15)
                        @php

                        $totalOfSub = $totalOfDepreactionAndAmortization ;
                        $earningBeforeIntresetAndTaxes = $totalOfSub ;


                        @endphp
                        @endif

                        @if($idOfItem == 19)
                        @php
                        $generalExpensesWithDepreciation = $total_of_main_with_rows_with_depreciation['Finance Income/(Expenses)'] ?? 0 ;
                        $totalOfSub = $earningBeforeIntresetAndTaxes + $generalExpensesWithDepreciation;
                        $earningBeforeTaxes =$totalOfSub ;
                          $total_of_main_with_rows_with_depreciation['Corporate Taxes'] = $earningBeforeTaxes < 0 ? 0 : $earningBeforeTaxes * $corporateTaxesPercentage/100 ;
						@endphp
                        @endif
						

                        @if($idOfItem == 21)
                        @php
                        $corportatTaxes = $total_of_main_with_rows_with_depreciation['Corporate Taxes'] ?? 0 ;
                        $totalOfSub =  $corportatTaxes ;


                        @endphp
                        @endif					


                        @if($idOfItem == 23)
                        @php
                        $corportatTaxes = $total_of_main_with_rows_with_depreciation['Corporate Taxes'] ?? 0 ;
                        $totalOfSub = $earningBeforeTaxes - $corportatTaxes ;


                        @endphp
                        @endif

                        <div class="kt-widget24__details">
                            <span class="kt-widget24__stats kt-font-{{$totalOfSub >= 0 ? 'success' : 'danger'}}" style="font-size:1.75rem">
                                @php
                                $totalWithDpreciationSalesRevenue = $total_of_main_with_rows_with_depreciation['Sales Revenue'] ?? 0 ;
                                @endphp
                                {{ number_format($totalOfSub) }} [{{ $totalWithDpreciationSalesRevenue ? number_format($totalOfSub / $totalWithDpreciationSalesRevenue * 100 , 2 ) . ' %'  : 0 }}]



                        </div>

                        <div class="progress progress--sm">
                            <div class="progress-bar kt-bg-{{$totalOfSub >= 0 ? 'success' : 'danger'}}" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="kt-widget24__action">
                            <span class="kt-widget24__change">

                            </span>
                            <span class="kt-widget24__number">

                            </span>
                        </div>
                    </div>
                </div>


                @endforeach


                <hr>

                <br>

                <br>


            </div>
        </div>







    </div>
    @foreach ($types as $singleType=>$type)
    @if($singleType != 'Corporate Taxes')
    <div class="col-sm-12 col-lg-6">
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title head-title text-primary" style="font-size:1.75rem !important">
                        {{ __(ucwords(str_replace('_',' ',$type)).' Breakdown Analysis') }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="kt-portlet kt-portlet--tabs">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-toolbar">
                    <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#kt_apps_contacts_view_tab_1_{{convertStringToClass($type)}}" role="tab">
                                <i class="flaticon-line-graph"></i> &nbsp; Charts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " data-toggle="tab" href="#kt_apps_contacts_view_tab_2_{{convertStringToClass($type)}}" role="tab">
                                <i class="flaticon2-checking"></i>Reports Table
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="kt-portlet__body">
                <div class="tab-content  kt-margin-t-20">

                    <div class="tab-pane active" id="kt_apps_contacts_view_tab_1_{{convertStringToClass($type)}}" role="tabpanel">

                        {{-- Monthly Chart --}}
                        <div class="col-xl-12">
                            <div class="kt-portlet kt-portlet--height-fluid">
                                <div class="kt-portlet__body kt-portlet__body--fluid">
                                    <div class="kt-widget12">
                                        <div class="kt-widget12__chart">
                                            {{-- <h4> {{ __('Sales Values') }} </h4> --}}
                                            <div id="chartdiv_{{convertStringToClass($type)}}" class="chartDiv"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="kt_apps_contacts_view_tab_2_{{convertStringToClass($type)}}" role="tabpanel">
                        <div class="col-md-12">
                            <div class="kt-portlet kt-portlet--mobile">

                                <div class="kt-portlet__body">

                                    <!--begin: Datatable -->
                                    <?php
                                                if ($type == 'service_provider_birth_year' || $type == 'service_provider_type') {
                                                    $report_count_data = $report_data['report_count_data']??[];
                                                    $total_count = ( count($report_count_data) > 0) ? array_sum(array_column($report_count_data,'Count')) : 0;
                                                    $report_data = $report_data['report_view_data']??[]  ;

                                                }
                                                $total = array_sum(array_column(($report_data??[]),'Sales Value'));$key=0;
                                            ?>
                                    <x-table :tableClass="'kt_table_with_no_pagination_no_scroll_without_pdf'">
                                        @slot('table_header')
                                        <tr class="table-active text-center">
                                            {{-- <th>#</th> --}}
                                            <th class="text-center">{{ __(ucwords(str_replace('_',' ',$type))) }}</th>

                                            <th class="text-center">{{ __('Value') }}</th>
                                            <th class="text-center">{{ __('Perc.% / Total') }}</th>
                                            @if($singleType == __('Sales Revenue'))
                                            <th class="text-center">{{ __('Qauntity') }}</th>
                                            @endif
                                            @if($singleType != __('Sales Revenue'))
                                            <th class="text-center">{{ __('Perc.% / Revenue') }}</th>
                                            @endif
                                        </tr>
                                        @endslot
                                        @slot('table_body')


                                        @php
                                        $totalOfSalesRevenuePercentage = 0 ;
                                        $totalForAll = getTotalForQuantityAndValues(($reports_data[$type]['sub_items']),$singleType == __('Sales Revenue'),true) ;

                                        @endphp
                                        @foreach ($reports_data[$type]['sub_items'] as $key => $item)
                                        @if(!isQuantitySubItem($key))
                                        @php
                                        $totalForSub = getTotalForQuantityAndValues(($reports_data[$type]['sub_items']),$singleType == __('Sales Revenue'),false,$key) ;

                                        @endphp

                                        <tr>
                                            <td>{{$key?? '-'}}</td>


                                            <td class="text-center">{{number_format($item)}}</td>
                                            <td class="text-center">{{$totalForAll['value'] ? number_format($item / $totalForAll['value'] * 100,2)  . ' %' : 0}}</td>
                                            @if($singleType == __('Sales Revenue'))
                                            <td class="text-center">{{ hasQuantityRow($reports_data[$type]['sub_items'],$key) ?  number_format($totalForSub['quantity']) : '-' }}</td>
                                            @endif
                                            @if($singleType != __('Sales Revenue'))

                                            <td class="text-center">
                                                @php
												
                                                $currentSalesRevenuePercentage = $total_of_sales_revenue ? $item / $total_of_sales_revenue * 100  : 0;
                                                
												$totalOfSalesRevenuePercentage += $currentSalesRevenuePercentage
                                                @endphp
                                                {{number_format($currentSalesRevenuePercentage,2) . ' %'}}</td>
                                            @endif

                                        </tr>
                                        @endif
                                        @endforeach

                                        <tr class="table-active text-center">
                                            <td>{{__('Total')}}</td>
                                            <td>{{number_format($totalForAll['value'])}}</td>
                                            <td>100 %</td>
                                            @if($singleType == __('Sales Revenue'))
                                            <td>{{ number_format($totalForAll['quantity']) }}</td>
                                            @endif

                                            @if($singleType != __('Sales Revenue'))
                                            <td>{{ isset($totalOfSalesRevenuePercentage) ? number_format($totalOfSalesRevenuePercentage,2) . ' %':0 }}</td>
                                            @endif
                                        </tr>
                                        @endslot
                                    </x-table>

                                    <!--end: Datatable -->
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="total_{{convertStringToClass($type)}}" data-total="{{ json_encode( format_for_chart($reports_data[$type]['sub_items']) ) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    <div class="container__fixed custom_modal_parent">
        <div class="datatable_modal_div kt-portlet kt-portlet--mobile">
            <div class="" id="datatable_modal_div">

            </div>
            <a class="close_custom_modal" href="#"><i class="fas fa-times"></i></a>

        </div>

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


<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance

        var chart = am4core.create("monthly_chartdiv", am4charts.XYChart);

        // Increase contrast by taking evey second color
        chart.colors.step = 2;

        // Add data
        var chartData = $('#monthly_data').data('total')
        chartData.forEach(function(objVal) {
            initialDate = getDateFormatted(new Date(objVal.date));
            if (initialDate.split('-').length == 4) {
                year = initialDate.split('-')[1];
                month = initialDate.split('-')[2]
                date = initialDate.split('-')[3];
            } else {
                year = initialDate.split('-')[0];
                month = initialDate.split('-')[1]
                date = initialDate.split('-')[2];
            }
            objVal.date = parseInt(year) + '-' + month + '-' + date;


        });

        chart.data = chartData;
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

@foreach ($types as $type )
<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("chartdiv_" + "{{convertStringToClass($type)}}", am4charts.PieChart);

        // Add data

        chart.data = $('#total_' + "{{convertStringToClass($type)}}").data('total')
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

    });

</script>
@endforeach


@foreach ($types as $type)

<script>
    $(document).on('change', '#business_sector_select_{{ convertStringToClass($type) }}', function(e) {
        e.preventDefault();
        $('#modal_for_' + "{{ convertStringToClass($type) }}").trigger('show.bs.modal')
    })

</script>

@endforeach

<script>
    $(document).on('click', '.ranged-button-ajax', function(e) {
        e.preventDefault();
        let type = $(this).data('type');
        let column = $(this).data('column');
        let direction = $(this).data('direction');
        $.ajax({
            url: "{{ route('getTopAndBottomsForDashboard') }}"
            , data: {
                "type": type
                , "column": column
                , "direction": direction
                , 'company_id': "{{ $company->id }}"
                , 'date_from': "{{ $start_date }}"
                , 'date_to': "{{ $end_date }}"
                , 'modal_id': $(this).closest('.modal__class_top_bottom').attr('id')
                , 'selected_type': $(this).closest('.modal__class_top_bottom').find('select[name="selected_type"]').val()
            }
            , "type": "post"
            , success: function(result) {
                let total_sales_values = $('#' + result.modal_id).find('#total_sales_value').attr('data-value');
                total_sales_values = parseFloat(total_sales_values.replaceAll(/,/g, ''));
                if (result.data.length) {
                    let table = "<table id='appended_table_for_view' class='appended_table_for_view datatable table-bordered table-hover table-checkable table'> <thead class='header___bg'><tr class='header___bg'><th class='header___bg'>#</th> <th class='header___bg'>{{ __('Customer Name') }}</th> <th class='header___bg text-center'>{{ __('Value') }}</th> <th class='header___bg text-center'>{{ __('Percentage') }}</th>  </tr></thead> <tbody>"
                    let order = 1;
                    let sumOfFifty = 0;
                    let salesValue = 0;
                    let percentage = 0;
                    let totalPercentage = 0;
                    for (index in result.data)

                    {
                        sumOfFifty += parseFloat(result.data[index].total_sales_value);
                        salesValue = result.data[index].total_sales_value;
                        percentage = salesValue / total_sales_values * 100;
                        totalPercentage += percentage
                        table += `<tr> <td>${order}</td> <td>${result.data[index].customer_name}</td><td class="text-center">${number_format( salesValue , 0) }</td> <td> ${number_format(percentage , 2) } % </td> </tr>`
                        order += 1;
                    }
                    table += ('<tr class="header___bg"><td class="header___bg">-</td> <td class="header___bg">{{ __("Total") }}</td> <td class="text-center header___bg">  ' + number_format(sumOfFifty) + '</td> <td class="header___bg">' + number_format(totalPercentage, 2) + ' % </td> </tr>')
                    table += '</tbody> </table>';
                    $('#datatable_modal_div').empty().append(table);
                    $('#appended_table_for_view').DataTable({
                        paging: false
                        , ordering: false
                        , info: false
                        , searching: false
                        , dom: `<'row'<'col-sm-6 text-left'f><'col-sm-6 text-right'B>>
                        <'row'<'col-sm-12'tr>>
                        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 dataTables_pager'lp>>`,

                        buttons: [
                            'print'
                            , 'copyHtml5'
                            , 'excelHtml5'
                        , ]

                    });

                    document.querySelectorAll('.close').forEach(function(modalItemCloser) {
                        $(modalItemCloser).trigger('click');
                    });
                    $('.container__fixed').css('display', 'block');
                }
            }
        });
    })

</script>
<script>
    $(document).on('click', function(e) {
        let targetElement = e.target;
        let x = $(targetElement).closest('.container__fixed').length;
        if (!x || targetElement.className.includes('container__fixed')) {
            $('.container__fixed').css('display', 'none');
        }
    })

</script>

<script>
    am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        // Create chart instance
        var chart = am4core.create("accumulated_chartdiv", am4charts.XYChart);

        // Add data



        chartData = $('#accumulated_data').data('total');
        chartData.forEach(function(objVal) {
            initialDate = getDateFormatted(new Date(objVal.date));
            if (initialDate.split('-').length == 4) {
                year = initialDate.split('-')[1];
                month = initialDate.split('-')[2]
                date = initialDate.split('-')[3];
            } else {
                year = initialDate.split('-')[0];
                month = initialDate.split('-')[1]
                date = initialDate.split('-')[2];
            }
            objVal.date = parseInt(year) + '-' + month + '-' + date;


        });

        chart.data = chartData;



        // Set input format for the dates
        chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";


        // Create axes
        var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

        // Create series
        var series = chart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = "price";
        series.dataFields.dateX = "date";
        series.tooltipText = "{price}"
        series.strokeWidth = 2;
        series.minBulletDistance = 5;

        // Drop-shaped tooltips
        series.tooltip.background.cornerRadius = 20;
        series.tooltip.background.strokeOpacity = 0;
        series.tooltip.pointerOrientation = "vertical";
        series.tooltip.label.minWidth = 40;
        series.tooltip.label.minHeight = 40;
        series.tooltip.label.textAlign = "middle";
        series.tooltip.label.textValign = "middle";

        // Make bullets grow on hover
        var bullet = series.bullets.push(new am4charts.CircleBullet());
        bullet.circle.strokeWidth = 2;
        bullet.circle.radius = 4;
        bullet.circle.fill = am4core.color("#fff");

        var bullethover = bullet.states.create("hover");
        bullethover.properties.scale = 1.3;

        // Make a panning cursor
        chart.cursor = new am4charts.XYCursor();
        chart.cursor.behavior = "panXY";
        chart.cursor.xAxis = dateAxis;
        chart.cursor.snapToSeries = series;
        valueAxis.cursorTooltipEnabled = false;

        // Create vertical scrollbar and place it before the value axis
        chart.scrollbarY = new am4core.Scrollbar();
        chart.scrollbarY.parent = chart.leftAxesContainer;
        chart.scrollbarY.toBack();

        // Create a horizontal scrollbar with previe and place it underneath the date axis
        chart.scrollbarX = new am4charts.XYChartScrollbar();
        chart.scrollbarX.series.push(series);
        chart.scrollbarX.parent = chart.bottomAxesContainer;

        dateAxis.start = 0.0005;
        dateAxis.keepSelection = true;


    }); // end am4core.ready()

</script>



<script>
    $(document).on('click', '.close_custom_modal', function(e) {
        e.preventDefault();
        $('.custom_modal_parent').fadeOut(300);
    })

    function getDateFormatted(yourDate) {
        const offset = yourDate.getTimezoneOffset()
        yourDate = new Date(yourDate.getTime() - (offset * 60 * 1000))
        return yourDate.toISOString().split('T')[0]
    }

</script>



<script src="{{ asset('custom/axios.js') }}"></script>

<script>
  

</script>

@endsection
