@extends('layouts.dashboard')

@section('css')
    @include('datatable_css')

{{-- <link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> --}}
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />

<link href="{{ url('assets/vendors/general/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<style>
    table {
        white-space: nowrap;
    }

    .hideit {
        display: none;
    }
	
</style>
@endsection
@section('content')
@if (session()->has('message'))
<div class="row">

    <div class="col-1"></div>
    <div class="col-10">
        <div class="alert alert-danger" role="alert">
            <div class="alert-icon"><i class="flaticon-warning"></i></div>
            <div class="alert-text">{{ __(' Please .. refill the fields according to the new dates') }}</div>
        </div>
    </div>
</div>
@endif
<form action="{{ route('sales.forecast.quantity.save', $company) }}" method="POST">
    @csrf

    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title head-title text-primary">
                    {{ __('Sales Forecast') }}
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body">

            <div class="form-group row">
                <div class="col-md-6">
                    <label>{{ __('Choose Date') }}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" name="start_date" required value="{{ $sales_forecast['start_date'] }}" class="form-control" placeholder="{{ __('Select date') }}" aria-describedby="emailHelp" />
                        </div>
                        <span class="input-note text-muted kt-font-primary kt-font-bold"> <i class="flaticon-warning note-icon"> </i>
                            {{ __('Kindly take note incase you changed the dates the info you filled will be deleted ') }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{ __('End Date') }}</label>
                    <div class="kt-input-icon">
                        <div class="input-group date">
                            <input type="date" name="end_date" disabled value="{{ $sales_forecast['end_date'] }}" class="form-control" placeholder="{{ __('Select date') }}" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--begin:: Widgets/Stats-->

    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title head-title text-primary">
                    {{ __('Sales Results (Value)') }}
                </h3>
            </div>
        </div>
        <div class="kt-portlet__body  kt-portlet__body--fit">
            <div class="row row-no-padding row-col-separator-xl">

                {{-- Pervious Year Sales --}}
                <div class="col-md-4 col-lg-4 col-xl-4">

                    <!--begin::New Users-->
                    <div class="kt-widget24">
                        <div class="kt-widget24__details">
                            <div class="kt-widget24__info">
                                <h4 class="kt-widget24__title font-size">
                                    {{ __('Pervious Year Sales (year' . $sales_forecast['previous_year'] . ' )') }}
                                </h4>

                            </div>
                        </div>
                        <div class="kt-widget24__details">
                            <span class="kt-widget24__stats kt-font-success">
                                {{ $previous_year_sales =  number_format($sales_forecast['previous_1_year_sales'] ?? 0) }}

                                @php
                                $previous_year_sales = 0;
                                @endphp
                            </span>
                            <input type="hidden" name="previous_1_year_sales" value="{{ $sales_forecast['previous_1_year_sales'] ?? 0 }}">
                            <input type="hidden" name="previous_year" value="{{ $sales_forecast['previous_year'] }}">
                        </div>
                        <div class="progress progress--sm">
                            <div class="progress-bar kt-bg-success" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="kt-widget24__action">

                        </div>
                    </div>

                    <!--end::New Users-->
                </div>
                {{-- Year  Gr Rate --}}
                <div class="col-md-4 col-lg-4 col-xl-4">

                    <!--begin::Total Profit-->
                    <div class="kt-widget24 text-center">
                        <div class="kt-widget24__details">
                            <div class="kt-widget24__info">
                                <h4 class="kt-widget24__title font-size">
                                    {{ __('Year ' . $sales_forecast['previous_year'] . ' Gr Rate %') }}
                                </h4>

                            </div>
                        </div>
                        <div class="kt-widget24__details">
                            <span class="kt-widget24__stats kt-font-brand">
                                {{ number_format($sales_forecast['previous_year_gr'] ?? 0, 2) . ' % ' }}
                            </span>
                            <input type="hidden" name="previous_year_gr" value="{{ $sales_forecast['previous_year_gr'] ?? 0 }}">
                        </div>

                        <div class="progress progress--sm">
                            <div class="progress-bar kt-bg-brand" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="kt-widget24__action">
                            <span class="kt-widget24__change">

                            </span>
                            <span class="kt-widget24__number">

                            </span>
                        </div>
                    </div>

                    <!--end::Total Profit-->
                </div>
                {{-- Average Last 3 Years --}}
                <div class="col-md-4 col-lg-4 col-xl-4">

                    <!--begin::New Feedbacks-->
                    <div class="kt-widget24">
                        <div class="kt-widget24__details">
                            <div class="kt-widget24__info">
                                <h4 class="kt-widget24__title font-size">
                                    {{ __('Last 3 Years Average Sales') }}
                                </h4>
                            </div>
                        </div>
                        <div class="kt-widget24__details">
                            <span class="kt-widget24__stats kt-font-warning">
                                {{ number_format($sales_forecast['average_last_3_years'] ?? 0) }}
                            </span>
                            <input type="hidden" name="average_last_3_years" value="{{ $sales_forecast['average_last_3_years'] ?? 0 }}">
                        </div>
                        <div class="progress progress--sm">
                            <div class="progress-bar kt-bg-warning" role="progressbar" style="width: 100%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="kt-widget24__action">
                            <span class="kt-widget24__change">

                            </span>
                            <span class="kt-widget24__number">

                            </span>
                        </div>
                    </div>

                    <!--end::New Feedbacks-->
                </div>
            </div>
        </div>
    </div>
    <!--end:: Widgets/Stats-->




    <div class="row">
        <div class="col-md-6">
            <div class="kt-portlet kt-portlet--mobile">
                <div class="kt-portlet__head kt-portlet__head--lg">
                    <div class="kt-portlet__head-label">
                        <span class="kt-portlet__head-icon">
                            <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                        </span>
                        <h3 class="kt-portlet__head-title">

                            <b> {{__('Previous Year Sales Breakdown')}} </b>

                        </h3>
                    </div>


                </div>
                <div class="kt-portlet__head kt-portlet__head--lg">
                    {{-- <div class="kt-portlet__head-label"> --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">

                                <?php $sales_forecast['others_products_previous_year'] = isset($sales_forecast['others_products_previous_year']) ? $sales_forecast['others_products_previous_year'] : old('others_products_previous_year'); ?>

                                <label>{{ __('Show From Others (Multi-Selector  - Maximum 5 )') }} @include('star')</label>

                                <select class="form-control kt-select2" id="kt_select2_9" name="others_products_previous_year[]" multiple="multiple">
                                    @foreach ($selector_products_previous_year??[] as $product)
                                    <option value="{{$product}}" {{(false !== $found = array_search($product,($sales_forecast['others_products_previous_year']??[]))) ? 'selected' : ''}}>{{$product}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn active-style" name="submit" value="{{__('Show Result')}}">
                        </div>
                    </div>
                    {{-- </div> --}}


                </div>

                <div class="kt-portlet__body">

                    <!--begin: Datatable -->
                    <x-table :tableClass="'kt_table_with_no_pagination_no_scroll'">
                        @slot('table_header')
                        <tr class="table-active text-center">
                            <th>#</th>
                            <th class="max-w-classes">{{ __('Product Item')}}</th>
                            <th>{{ __('Sales Values') }}</th>
                            <th>{{ __('Sales %') }}</th>
                            <th>{{ __('Sales Qt') }}</th>
                            <th>{{ __('Av. Price') }}</th>


                        </tr>

                        @endslot

                        @slot('table_body')
                        <?php $total = array_sum(array_column($sales_forecast['previous_year_seasonality']??[],'Sales Value')); ?>
                        @foreach ($sales_forecast['previous_year_seasonality']??[] as $key => $item)
                        <tr>
                            <th>{{$key+1}}</th>
                            <th class="max-w-classes">{{$item['item']?? '-'}}</th>
                            <input type="hidden" name="previous_year_seasonality[{{$key}}][item]" value="{{($item['item']?? '-')}}">
                            <td class="text-center">{{number_format($item['Sales Value']??0)}}</td>
                            <input type="hidden" name="previous_year_seasonality[{{$key}}][Sales Value]" value="{{($item['Sales Value']??0)}}">
                            <td class="text-center">{{number_format($item['Sales %']??0,2)}} %</td>
                            <input type="hidden" name="previous_year_seasonality[{{$key}}][Sales %]" value="{{($item['Sales %']??0)}}">
                            <td class="text-center">{{number_format($item['Sales Quantity']??0)}}</td>
                            <input type="hidden" name="previous_year_seasonality[{{$key}}][Sales Quantity]" value="{{($item['Sales Quantity']??0)}}">
                            <td class="text-center">{{number_format($item['Average Price']??0)}}</td>
                            <input type="hidden" name="previous_year_seasonality[{{$key}}][Average Price]" value="{{($item['Average Price']??0)}}">

                        </tr>
                        @endforeach
                        <tr class="table-active text-center">
                            <th colspan="2">{{__('Total')}}</th>
                            <td class="hidden"></td>
                            <td>{{number_format($total)}}</td>
                            <td>100 %</td>
                            {{-- <td>{{number_format(  array_sum(array_column($sales_forecast['previous_year_seasonality'],'Sales Quantity')) )}}</td>
                            <td>{{number_format( array_sum(array_column($sales_forecast['previous_year_seasonality'],'Average Price')) )}}</td> --}}
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        @endslot
                    </x-table>


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
                        <h3 class="kt-portlet__head-title">

                            <b> {{__('Average Last 3 Years Sales Breakdown')}} </b>

                        </h3>
                    </div>

                </div>
                <div class="kt-portlet__head kt-portlet__head--lg">
                    {{-- <div class="kt-portlet__head-label"> --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?php $sales_forecast['others_products_previous_3_year'] = isset($sales_forecast['others_products_previous_3_year']) ? $sales_forecast['others_products_previous_3_year'] : old('others_products_previous_3_year'); ?>
                                <label>{{ __('Show From Others (Multi-Selector  - Maximum 5 )') }} @include('star')</label>

                                <select class="form-control kt-select2" id="kt_select2_8" name="others_products_previous_3_year[]" multiple="multiple">
                                    @foreach ($selector_products_previous_3_year??[] as $product)
                                    <option value="{{$product}}" {{(false !== $found = array_search($product,($sales_forecast['others_products_previous_3_year']??[]))) ? 'selected' : ''}}>{{$product}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" class="btn active-style" name="submit" value="{{__('Show Result')}}">
                        </div>
                    </div>
                    {{-- </div> --}}


                </div>
                <div class="kt-portlet__body">

                    <!--begin: Datatable -->
                    <x-table :tableClass="'kt_table_with_no_pagination_no_scroll'">
                        @slot('table_header')
                        <tr class="table-active text-center">
                            <th>#</th>
                            <th class="max-w-classes">{{ __('Product Item')}}</th>
                            <th>{{ __('Sales Values') }}</th>
                            <th>{{ __('Sales %') }}</th>
                            <th>{{ __('Sales Qt') }}</th>
                            <th>{{ __('Av. Price') }}</th>


                        </tr>
                        @endslot
                        @slot('table_body')
                        <?php $total = array_sum(array_column($sales_forecast['last_3_years_seasonality'],'Sales Value')); ?>
                        @foreach ($sales_forecast['last_3_years_seasonality'] as $key => $item)
                        <tr>
                            <th>{{$key+1}}</th>
                            <th class="max-w-classes">{{$item['item']?? '-'}}</th>
                            <input type="hidden" name="last_3_years_seasonality[{{$key}}][item]" value="{{($item['item']?? '-')}}">
                            <td class="text-center">{{number_format($item['Sales Value']??0)}}</td>
                            <input type="hidden" name="last_3_years_seasonality[{{$key}}][Sales Value]" value="{{($item['Sales Value']??0)}}">
                            <td class="text-center">{{number_format($item['Sales %']??0,2)}} %</td>
                            <input type="hidden" name="last_3_years_seasonality[{{$key}}][Sales %]" value="{{($item['Sales %']??0)}}">
                            <td class="text-center">{{number_format($item['Sales Quantity']??0)}}</td>
                            <input type="hidden" name="last_3_years_seasonality[{{$key}}][Sales Quantity]" value="{{($item['Sales Quantity']??0)}}">
                            <td class="text-center">{{number_format($item['Average Price']??0)}}</td>
                            <input type="hidden" name="last_3_years_seasonality[{{$key}}][Average Price]" value="{{($item['Average Price']??0)}}">

                        </tr>
                        @endforeach
                        <tr class="table-active text-center">
                            <th colspan="2">{{__('Total')}}</th>
                            <td class="hidden"></td>
                            <td>{{number_format($total)}}</td>
                            <td>100 %</td>
                            {{-- <td>{{number_format(  array_sum(array_column($sales_forecast['last_3_years_seasonality'],'Sales Quantity')) )}}</td>
                            <td>{{number_format( array_sum(array_column($sales_forecast['last_3_years_seasonality'],'Average Price')) )}}</td> --}}
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        @endslot
                    </x-table>
                    <!--end: Datatable -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="kt-portlet kt-portlet--mobile">
                <?php $sales_forecast_data = App\Models\QuantitySalesForecast::company()->first() ?? old(); ?>
                <div class="kt-portlet__body">
                    <!--begin: Datatable -->
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{ __('Target Base') }} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group date validated">

                                    <select name="target_base" class="form-control" id="target_base" required>
                                        <option value="" selected>{{ __('Select') }}</option>
                                        @if(hasProductsItems($company))
                                        <option value="previous_year" {{ @$sales_forecast_data['target_base'] !== 'previous_year' ?'': 'selected' }}>
                                            {{ __('Based On Pervious Year Sales') }}</option>
                                        <option value="previous_3_years" {{ @$sales_forecast_data['target_base'] !== 'previous_3_years' ?'': 'selected' }}>
                                            {{ __('Based On Last 3 Years Sales') }}</option>
                                        @endif
                                        <option value="new_start" {{ @$sales_forecast_data['target_base'] !== 'new_start' ?'': 'selected' }}>
                                            {{ __('New Start') }}</option>
                                    </select>
                                    @if ($errors->has('target_base'))
                                    <div class="invalid-feedback">{{ $errors->first('target_base') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>



                        <div class="col-md-2" style="display: {{ @$sales_forecast_data['target_base'] == 'previous_year' || @$sales_forecast_data['target_base'] == 'previous_3_years'? 'block': 'none' }}" id="quantity_growth_rate_field">
                            <label>{{ __('Quantity Growth Rate %') }} @include('star')</label>
                            <div class="kt-input-icon validated">
                                <div class="input-group">
                                    <input type="number" step="any" class="form-control" name="quantity_growth_rate" value="{{ @$sales_forecast_data['quantity_growth_rate'] }}" id="quantity_growth_rate">
                                </div>
                                @if ($errors->has('quantity_growth_rate'))
                                <div class="invalid-feedback">{{ $errors->first('quantity_growth_rate') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-2" style="display: {{ @$sales_forecast_data['target_base'] == 'previous_year' || @$sales_forecast_data['target_base'] == 'previous_3_years' ? 'block': 'none' }}" id="prices_increase_rate_field">
                            <label>{{ __('Prices Increase Rate') }} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group validated">
                                    <input type="number" step="any" class="form-control" name="prices_increase_rate" value="{{ @$sales_forecast_data['prices_increase_rate'] }}" id="prices_increase_rate">
                                    @if ($errors->has('prices_increase_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('prices_increase_rate') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2" style="display: {{ @$sales_forecast_data['target_base'] == 'previous_year' || @$sales_forecast_data['target_base'] == 'previous_3_years' ? 'block': 'none' }}" id="other_products_growth_rate_field">
                            <label>{{ __('Other Products Growth Rate') }} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group validated">
                                    <input type="number" step="any" class="form-control" name="other_products_growth_rate" value="{{ @$sales_forecast_data['other_products_growth_rate'] }}" id="other_products_growth_rate">
                                    @if ($errors->has('other_products_growth_rate'))
                                    <div class="invalid-feedback">{{ $errors->first('other_products_growth_rate') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-6 ">
                            <label class="kt-option bg-secondary">
                                <span class="kt-option__control">
                                    <span class="kt-checkbox kt-checkbox--bold kt-checkbox--brand kt-checkbox--check-bold" checked>
                                        <input class="rows" name="add_new_products" type="checkbox" value="1" {{ @$sales_forecast_data['add_new_products'] == 0 ?: 'checked' }} id="product_item_check_box" data-old-checked="{{ @$sales_forecast_data['add_new_products']?:0 }}">
                                        <span></span>
                                    </span>
                                </span>
                                <span class="kt-option__label d-flex">
                                    <span class="kt-option__head mr-auto p-2">
                                        <span class="kt-option__title">
                                            <b>
                                                {{ __('Add New Products Or Product Item') }}
                                            </b>
                                        </span>

                                    </span>
                                </span>
                            </label>
                        </div>
                        <div class="col-md-6" style="display:{{ @$sales_forecast_data['add_new_products'] == 1 ? 'block' : 'none' }}" id="number_of_products_field" data-old-value="{{ @$sales_forecast_data['number_of_products'] ?: 0  }}">
                            <label>{{ __('How Many Products') }} @include('star')</label>
                            <div class="kt-input-icon">
                                <div class="input-group validated">
                                    <input type="number" step="any" class="form-control" name="number_of_products" value="{{ @$sales_forecast_data['number_of_products'] }}" id="number_of_products">
                                    @if ($errors->has('number_of_products'))
                                    <div class="invalid-feedback">{{ $errors->first('number_of_products') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    @if(hasProductsItems($company))

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group  form-group-marginless">
                                <label>{{ __('Seasonality') }} @include('star')</label>
                                <div class="kt-input-icon">
                                    <div class="input-group date validated">

                                        <select name="seasonality" class="form-control" id="seasonality">
                                            <option value="" selected>{{ __('Select') }}</option>
                                            <option value="previous_year" {{ @$sales_forecast_data['seasonality'] !== 'previous_year' ?: 'selected' }}>
                                                {{ __('Pervious Year Seasonality') }}</option>
                                            <option value="last_3_years" {{ @$sales_forecast_data['seasonality'] !== 'last_3_years' ?: 'selected' }}>
                                                {{ __('Last 3 Years Seasonality') }}</option>


                                        </select>
                                        @if ($errors->has('seasonality'))
                                        <div class="invalid-feedback">{{ $errors->first('seasonality') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    @endif

                </div>
            </div>
        </div>
    </div>
    <x-next__button :report="true" :companyId="$company->id"> </x-next__button>

</form>
@endsection
@section('js')
<script src="{{ url('assets/vendors/general/select2/dist/js/select2.full.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
</script>
    @include('js_datatable')

{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript">
</script>

<script>
    $('#target_base').on('change', function() {
        val = $(this).val();

        if (val == 'previous_year' || val == 'previous_3_years') {

            // $('#new_start_field').fadeOut("slow", function() {
            $('#quantity_growth_rate_field').fadeIn(300);
            $('#prices_increase_rate_field').fadeIn(300);
            $('#other_products_growth_rate_field').fadeIn(300);
            // });
        } else if (val == 'new_start') {
            // $('#quantity_growth_rate_field').fadeOut("slow", function() {
            $('#quantity_growth_rate_field').fadeOut(300);
            $('#prices_increase_rate_field').fadeOut(300);
            $('#other_products_growth_rate_field').fadeOut(300);

            // });
        }
    });


    $('#seasonality').on('change', function() {
        val = $(this).val();

        if (val == 'previous_year' || val == 'last_3_years') {

            $('#monthly_seasonality').fadeOut(300)
            $('#quarterly_seasonality').fadeOut(300)
        } else if (val == 'new_seasonality_monthly') {
            $('#quarterly_seasonality').fadeOut("slow", function() {
                $('#monthly_seasonality').fadeIn(300);
            });
        } else if (val == 'new_seasonality_quarterly') {
            $('#monthly_seasonality').fadeOut("slow", function() {
                $('#quarterly_seasonality').fadeIn(300);
            });

        }
    });


    $('#product_item_check_box').change(function(e) {
        if ($(this).prop("checked")) {
            $('#number_of_products_field').fadeIn(300);
        } else {
            $('#number_of_products_field').fadeOut(300);
        }
    });


    function totalFunction(field_name, total_field_name, decimals) {
        total = 0;
        $(field_name).each(function(index, element) {

            if (element.value !== '') {
                total = parseFloat(element.value) + total;
            }

        });
        $(total_field_name).val(total.toFixed(decimals));
    }

</script>


<script>
    $(document).on('change', '#product_item_check_box , #number_of_products', function(e) {
        let oldIsChedked = $('#product_item_check_box').attr('data-old-checked');
        let newIsChecked = $('#product_item_check_box').is(':checked') ? 1 : 0;

        let oldNewProductsItems = parseFloat($('#number_of_products_field').attr('data-old-value'));
        let newProductsItems = parseFloat($('#number_of_products').val());

        if (oldIsChedked != newIsChecked || oldNewProductsItems != newProductsItems) {
            $('#subkit_summary_report_id').addClass('hideit');
        } else {
            $('#subkit_summary_report_id').removeClass('hideit');
        }

    })

</script>

@endsection
