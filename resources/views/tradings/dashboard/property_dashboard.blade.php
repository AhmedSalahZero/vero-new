@extends('layouts.dashboard')
@section('css')
<link href="{{url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')}}" rel="stylesheet" type="text/css" />
<link href="{{url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('dash_nav')

<style>
html body .header-border-down, html body .action-class{
	border-bottom:1px solid green !important;
}

    .chartdiv_two_lines {
        width: 100%;
        height: 400px;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: transparent !important;
    }

    .chartDiv {
        max-height: 425px !important;
    }

    g[aria-labelledby^="id-"][filter] {
        display: none !important;
    }

    .margin__left {
        border-left: 2px solid #366cf3;
    }

    .sky-border {
        border-bottom: 1.5px solid blue !important;
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

    <form action="{{ route('trading.view.property.dashboard',['company'=>$company->id]) }}" class="kt-portlet__head w-full sky-border" style="">
        <div class="kt-portlet__head-label w-full">
            <h3 class="kt-portlet__head-title head-title text-primary w-full">


                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="visibility-hidden"> {{__('Currency')}}
                            @include('star')
                        </label>
                        <h3 class="font-weight-bold text-black form-label kt-subheader__title small-caps mr-5 text-nowrap" style=""> {{ __('Dashboard Results') }}</h3>

                    </div>
                    <div class="col-md-2">
                        <label class="visibility-hidden"> {{__('Currency')}}
                            @include('star')
                        </label>
                        <div class="kt-input-icon">
                            <div class="input-group date">
                                <input id="js-date" type="date" value="{{ isset($date) ? $date: date('Y-m-d') }}" name="date" class="form-control" placeholder="Select date" id="kt_datepicker_2" />
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="la la-calendar-check-o"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 kt-align-right">

                        <label class="visibility-hidden"> {{__('Currency')}}
                            @include('star')
                        </label>

                        <div class="input-group">
                            <button type="submit" class="btn active-style save-form">{{__('Save')}}</button>
                        </div>
                    </div>

                </div>



            </h3>
        </div>
    </form>

    <div class="kt-portlet__body" style="padding-bottom:0 !important;">
        <ul style="margin-bottom:0 ;" class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
            @php
            $index = 0 ;
            @endphp


            <li class="nav-item  active ">
                <a class="nav-link  active" data-toggle="tab" href="#kt_apps_contacts_view_tab_main{{ $index }}" role="tab">
                    <i class="flaticon2-checking icon-lg"></i>
                    <span style="font-size:18px !important;">{{ __('Overview') }}</span>
                </a>
            </li>
            <li class="nav-item  ">
                <a class="nav-link " href="{{ route('trading.view.property.cashflow.forecast.dashboard',['company'=>$company->id]) }}" role="tab">
                    <i class="flaticon2-checking icon-lg"></i>
                    <span style="font-size:18px !important;">{{ __('Forecast') }}</span>
                </a>
            </li>


        </ul>
    </div>
</div>

<div class="tab-content  kt-margin-t-20">
    @php
    $index = 0 ;
    @endphp



    <div class="tab-pane  @if($index == 0) active @endif" id="kt_apps_contacts_view_tab_main{{ $index }}" role="tabpanel">
       


<div class="row">
    <div class="col-md-6">
        <div class="kt-portlet ">
            <div class="kt-portlet__head sky-border">
                <div class="kt-portlet__head-label">
                    <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Property Overview') }} </h3>
                </div>

            </div>
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-md-12">
                        @include('admin.dashboard.property_avg_min_max_dashboard_table',[
                        'propertyTypes'=>$propertyTypes
                        ])

                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            {{-- @php
                    $styleClasses = [
                    'primary',
                    'success',
                    'danger',
                    'warning',
                    ];
                    @endphp --}}



            @foreach ($propertiesOverviewCards as $propertyOverviewCard)
			@php
													$currentDetailModel = $propertyOverviewCard['details_model']??[];
												@endphp
												
            <div class="col-md-6 ">
                <div class="kt-portlet " style="height:250px">
                    <div class="kt-portlet__head sky-border">
                        <div class="kt-portlet__head-label">
                            <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Total Count') }} </h3>
                        </div>

                    </div>
                    <div class="kt-portlet__body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">

                                    <!--begin::New Orders-->
                                    <div class="kt-widget24 p-0">
                                        <div class="kt-widget24__details">
                                            <div class="kt-widget24__info w-100">
                                                <h4 class="kt-widget24__title font-size text-uppercase d-flex justify-content-between align-items-center">
                                                    {{ $propertyOverviewCard['title'] }}
													@if(count($currentDetailModel))
                                                    <button class="btn btn-sm btn-brand btn-elevate btn-pill text-white" data-toggle="modal" data-target="#model-id-{{ $currentDetailModel['id'] }}">{{ __('Details') }}</button>
													@endif
                                                </h4>
												
												
                                                @if(count($currentDetailModel))

                                                <div class="modal fade " id="model-id-{{ $currentDetailModel['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                                        <form action="#" class="modal-content" method="post">


                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" style="color:#0741A5 !important" id="exampleModalLongTitle"> {{ $currentDetailModel['title'] }} </h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="customize-elements">
                                                                    <table class="table    table-white  ">
                                                                        <thead>
                                                                            <tr>
																			@foreach($currentDetailModel['headers'] as $id => $headerArr)
                                                                                <th class="form-label font-weight-bold  text-center align-middle  header-border-down {{ $headerArr['classes'] }} ">{{ $headerArr['title'] }}</th>
																				  @endforeach
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>

                                                                            @foreach($currentDetailModel['rows'] as $index => $rowItems)
{{-- {{ dd() }} --}}
                                                                            {{-- @foreach ($study->{$relationName} as $index=>$currentLeasingRevenueStreamBreakdown) --}}
                                                                            <tr>
																			@foreach($rowItems as $rowIndex => $rowItem)
                                                                                <td class=" {{ $rowItem['td_classes'] }}">
                                                                                    <div class="kt-input-icon ">
                                                                                        <div class="input-group">
                                                                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style {{ $rowItem['input-classes'] }} " value="{{ $rowItem['value'] }}">
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
																				@endforeach
{{-- 																				
                                                                                <td class="w-50-percentage">
                                                                                    <div class="kt-input-icon ">
                                                                                        <div class="input-group">
                                                                                            <input disabled type="text" step="0.1" class="form-control ignore-global-style" value="name here">
                                                                                        </div>
                                                                                    </div>
                                                                                </td>


                                                                                <td class="w-10-percentage">
                                                                                    <div class="d-flex align-items-center ">
                                                                                        <div class="kt-input-icon ml-2 ">
                                                                                            <div class="input-group">
                                                                                                <input type="text" class="form-control text-center" value="value here">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                </td> --}}





                                                                            </tr>

                                                                            {{-- @endforeach --}}
                                                                            @endforeach


                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-primary 
				
				                                      " data-dismiss="modal">{{ __('Close') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
												
												@endif


                                            </div>
                                        </div>
                                        <div class="kt-widget24__details">
                                            <span class="kt-widget24__stats kt-font-primary text-uppercase">
                                                <span class="mb-3 d-inline-block text-black"> {{ $propertyOverviewCard['value'] }} </span>

                                                <br>

                                                <span @if($loop->first) style="visibility:hidden;" @endif class="text-green">[{{ number_format($propertyOverviewCard['percentage']??0,2) }} % / Total]</span>


                                            </span>
                                        </div>
                                        <div class="progress progress--sm">
                                            <div class="progress-bar kt-bg-{{ $propertyOverviewCard['color_class'] }}" role="progressbar" style="width: 69%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                    </div>

                                    <!--end::New Orders-->
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
            @endforeach
        </div>


    </div>

</div>






<div class="row">
    <div class="col-md-6">
        <div class="kt-portlet ">
            <div class="kt-portlet__head sky-border">
                <div class="kt-portlet__head-label">
                    <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Rent Revenues') }} </h3>
                </div>

            </div>
            <div class="kt-portlet__body">
                <div class="row">
                    {{-- {{ dd($propertyTypes) }} --}}
                    <div class="col-md-12">
                        @include('admin.dashboard._rent_revenue_and_collection',[
                        'propertyTypes'=>$propertyTypes,
                        'current_category'=>'rent_revenues'
                        ])

                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="row">

            <div class="col-md-12 ">

                <div class="kt-portlet " style="height:525px">

                    <div class="kt-portlet__head sky-border">
                        <div class="kt-portlet__head-label">
                            <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Rent Revenues Breakdown') }} </h3>
                        </div>

                    </div>

                    <div class="kt-portlet__body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="pie-chart-rent_revenues-by-type-id" class="chartDiv"></div>
                                <input type="hidden" id="pie-chart-rent_revenues-by-type-data-id" data-chart-data="{{ json_encode($rentRevenuesPerTypePieData ?? []) }}">
                            </div>
                        </div>
                    </div>


                </div>



            </div>


        </div>

    </div>

</div>


<div class="row">
    <div class="col-md-6">
        <div class="kt-portlet ">
            <div class="kt-portlet__head sky-border">
                <div class="kt-portlet__head-label">
                    <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Rent Collections') }} </h3>
                </div>

            </div>
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-md-12">
                        @include('admin.dashboard._rent_revenue_and_collection',[
                        'propertyTypes'=>$propertyTypes,
                        'current_category'=>'collections'
                        ])

                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            {{-- @php
                    $styleClasses = [
                    'primary',
                    'success',
                    'danger',
                    'warning',
                    ];
                    @endphp --}}
            <div class="col-md-12 ">

                <div class="kt-portlet " style="height:525px">

                    <div class="kt-portlet__head sky-border">
                        <div class="kt-portlet__head-label">
                            <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Rent Collections Breakdown') }} </h3>
                        </div>

                    </div>

                    <div class="kt-portlet__body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="pie-chart-collections-by-type-id" class="chartDiv"></div>
                                <input type="hidden" id="pie-chart-collections-by-type-data-id" data-chart-data="{{ json_encode($rentCollectionsPerTypePieData ?? []) }}">
                            </div>
                        </div>
                    </div>


                </div>



            </div>


        </div>

    </div>

</div>



<div class="row">
    <div class="col-md-6">
        <div class="kt-portlet ">
            <div class="kt-portlet__head sky-border">
                <div class="kt-portlet__head-label">
                    <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Due Installments') }} </h3>
                </div>

            </div>
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-md-12">
                        @include('admin.dashboard._rent_revenue_and_collection',[
                        'propertyTypes'=>$propertyTypes,
                        'current_category'=>'due_installments'
                        ])

                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="col-md-6">
        <div class="row">

            <div class="col-md-12 ">

                <div class="kt-portlet " style="height:525px">

                    <div class="kt-portlet__head sky-border">
                        <div class="kt-portlet__head-label">
                            <h3 class="font-weight-bold text-black  form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap" style=""> {{ __('Due Installments Breakdown') }} </h3>
                        </div>

                    </div>

                    <div class="kt-portlet__body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="pie-chart-due_installments-by-type-id" class="chartDiv"></div>
                                <input type="hidden" id="pie-chart-due_installments-by-type-data-id" data-chart-data="{{ json_encode($dueInstallmentsPerTypePieData ?? []) }}">
                            </div>
                        </div>
                    </div>


                </div>



            </div>


        </div>

    </div>

</div>




</div>

@php
$index++;
@endphp

</div>
@endsection
@section('js')
<script src="{{ url('assets/vendors/general/jquery/dist/jquery.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    (function() {
        if (typeof window.KTUtil !== 'undefined') return;
        window.KTUtil = {
            isRTL: function() {
                return (KTUtil.attr(KTUtil.get('html'), 'direction') === 'rtl') || (document.documentElement.getAttribute('dir') === 'rtl');
            }
            , get: function(el) {
                if (el === undefined || el === null) return null;
                if (typeof el === 'string') return document.querySelector(el) || document.querySelector('#' + el);
                return el.nodeName ? el : (el && el[0]) ? el[0] : el;
            }
            , attr: function(el, name) {
                el = KTUtil.get(el);
                return el ? el.getAttribute(name) : null;
            }
        };
    })();

</script>


<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>
<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

{{-- Pie chart: Rent Revenues by Type (from $currentRunningContractMonthRentAndCollectionsPerType) --}}
@foreach(['rent_revenues','collections','due_installments'] as $current_category)
<script>
    am4core.ready(function() {
        am4core.useTheme(am4themes_animated);
        var data = $('#pie-chart-{{ $current_category }}-by-type-data-id').data('chart-data') || [];
        if (!data.length) return;
        var chart = am4core.create('pie-chart-{{ $current_category }}-by-type-id', am4charts.PieChart);
        chart.data = data;
        var pieSeries = chart.series.push(new am4charts.PieSeries());
        pieSeries.dataFields.value = 'value';
        pieSeries.dataFields.category = 'name';
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
        chart.legend.scrollable = true;

    });

</script>
@endforeach

<!--begin::Page Scripts(used by this page) -->
<script src="{{url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js')}}" type="text/javascript"></script>
<script src="{{url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/lib.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/jquery.input.js')}}" type="text/javascript"></script>
<script src="{{url('assets/vendors/general/jquery.repeater/src/repeater.js')}}" type="text/javascript"></script>
<script src="{{url('assets/js/demo1/pages/crud/forms/widgets/form-repeater.js')}}" type="text/javascript"></script>
