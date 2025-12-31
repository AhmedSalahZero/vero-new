@extends('layouts.dashboard')

@section('css')
    {{-- <link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> --}}
    @include('datatable_css')
    <link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}"
        rel="stylesheet" type="text/css" />

        <link href="{{ url('assets/vendors/general/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
    <style>
        table {
            white-space: nowrap;
        }
        .hideit{
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
    <form action="{{ route('forecasted.sales.values', $company) }}" method="POST">
        @csrf

        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title head-title text-primary">
                        {{ __('Forecasted Sales Values') }}
                    </h3>
                </div>
            </div>

        </div>




        @if ($sales_forecast->target_base == 'new_start')
            <div class="row">
                <div class="col-md-12">
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

                        <div class="kt-portlet__body">

                            <!--begin: Datatable -->
                            <x-table  :tableClass="'kt_table_with_no_pagination_no_scroll'">
                                @slot('table_header')
                                    <tr class="table-active text-center">
                                        <th>#</th>
                                        <th>{{ __('Product Item')}}</th>
                                        <th>{{ __('Sales Values') }}</th>
                                        <th>{{ __('Sales %') }}</th>
                                        <th>{{ __('Sales Quantity') }}</th>
                                        <th>{{ __('Average Price') }}</th>
                                        <th style="background-color: #086691;"></th>
                                        <th>{{ __('Quantity Growth Rate') }}</th>
                                        <th>{{ __('Forecasted Quantity') }}</th>
                                        <th>{{ __('Price Increase Rate') }}</th>
                                        <th>{{ __('Forecasted Price') }}</th>
                                        <th>{{ __('Forecasted Sales Value') }}</th>
                                    </tr>

                                @endslot

                                @slot('table_body')

                                    @foreach ($forecasted_sales_date as $key => $item)
                                    <tr>
                                        <th>{{$key+1}}</th>
                                        <th class="max-w-classes">{{$item['item']?? '-'}}</th>
                                        <td class="text-center">{{number_format($item['Sales Value']??0)}}</td>
                                            <input type="hidden" class="sales_value"  value="{{($item['Sales Value']??0)}}">
                                        <td class="text-center">{{number_format($item['Sales %']??0,2)}} %</td>
                                            <input type="hidden" class="sales_perc" value="{{($item['Sales %']??0)}}">
                                        <td class="text-center">{{number_format($item['Sales Quantity']??0)}}</td>
                                            <input type="hidden" class="sales_quantity"  value="{{($item['Sales Quantity']??0)}}">
                                        <td class="text-center">{{number_format($item['Average Price']??0)}}</td>
                                            <input type="hidden" class="average_price"  value="{{($item['Average Price']??0)}}">
                                        <td style="background-color: #086691; border:none" ></td>
                                        <td class="text-center">
                                            <input type="number" class="form-control quantity_growth_rate" step="any" name="quantity_growth_rates[{{$key}}]" value="{{($item['quantity_growth_rates']??'')}}" >
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control forecasted_quantity" readonly value="{{round(($item['Forecasted Quantity']) )}}" style="background-color: lightgray"  step="any" name="forecasted_quantity[{{$key}}]" >
                                        </td>
                                        <td class="text-center">
                                            <input type="number" class="form-control prices_increase_rate" step="any" name="prices_increase_rates[{{$key}}]" value="{{($item['prices_increase_rates']??'')}}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control forecasted_price" readonly style="background-color: lightgray" step="any" name="forecasted_price[{{$key}}]"
                                            value="{{round($item['Forecasted Price']??'')}}">
                                        </td>
                                        <td class="text-center">
                                            <input type="text" class="form-control forecasted_sales_value" readonly style="background-color: lightgray" step="any" name="forecasted_sales_value[{{$key}}]" value="{{number_format($item['Forecasted Sales Value']??'')}}">
                                        </td>

                                    </tr>
                                    @endforeach
                                    {{-- <tr class="table-active text-center">
                                        <th colspan="2">{{__('Total')}}</th>
                                        <td class="hidden"></td>
                                        <td>{{number_format(array_sum(array_column($forecasted_sales_date,'Sales Value')))}}</td>
                                        <td>100 %</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td ></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>{{number_format(array_sum(array_column($forecasted_sales_date,'Forecasted Sales Value')))}}</td>
                                    </tr> --}}
                                @endslot
                            </x-table>


                            <!--end: Datatable -->
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
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

                        <div class="kt-portlet__body">

                            <!--begin: Datatable -->
                            <x-table  :tableClass="'kt_table_with_no_pagination_no_scroll'">
                                @slot('table_header')
                                    <tr class="table-active text-center">
                                        <th>#</th>
                                        <th>{{ __('Product Item')}}</th>
                                        <th class="max-w-class">{{ __('Sales Values') }}</th>
                                        <th>{{ __('Sales %') }}</th>
                                        <th>{{ __('Sales Quantity') }}</th>
                                        <th>{{ __('Average Price') }}</th>
                                        <th style="background-color: #086691;"></th>
                                        <th>{{ __('Forecasted Quantity') }}</th>
                                        <th>{{ __('Forecasted Price') }}</th>
                                        <th>{{ __('Forecasted Sales Value') }}</th>
                                    </tr>

                                @endslot

                                @slot('table_body')

                                    @foreach ($forecasted_sales_date as $key => $item)
                                    <tr>
                                        <th>{{$key+1}}</th>
                                        <th class="max-w-classes">{{$item['item']?? '-'}}</th>
                                        <td class="text-center">{{number_format($item['Sales Value']??0)}}</td>
                                        <td class="text-center">{{number_format($item['Sales %']??0,2)}} %</td>
                                        <td class="text-center">{{number_format($item['Sales Quantity']??0)}}</td>
                                        <td class="text-center">{{number_format($item['Average Price']??0)}}</td>
                                        <td style="background-color: #086691; border:none " ></td>
                                        <td class="text-center">{{number_format($item['Forecasted Quantity']??0)}}</td>
                                        <td class="text-center">{{number_format($item['Forecasted Price']??0)}}</td>
                                        <td class="text-center">{{number_format($item['Forecasted Sales Value']??0)}}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-active text-center">
                                        <th colspan="2">{{__('Total')}}</th>
                                        <td class="hidden"></td>
                                        <td>{{number_format(array_sum(array_column($forecasted_sales_date,'Sales Value')))}}</td>
                                        <td>100 %</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td ></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>{{number_format(array_sum(array_column($forecasted_sales_date,'Forecasted Sales Value')))}}</td>
                                    </tr>
                                @endslot
                            </x-table>


                            <!--end: Datatable -->
                        </div>
                    </div>
                </div>
            </div>
        @endif






        <x-next__button :report="true" :companyId="$company->id"> </x-next__button>

    </form>
@endsection
@section('js')
    <script src="{{ url('assets/vendors/general/select2/dist/js/select2.full.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script>
    <script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript">
    </script>
    {{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
    @include('js_datatable')
    <script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript">
    </script>
    <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript">
    </script>
    <script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}"
        type="text/javascript">
    </script>
    <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript">
    </script>

    <script>
        // $("input[name='new_start']").change(function() {

        //     if ($(this).val() == 'annual_target') {
        //         $('#prices_increase_rate_field').attr("readonly", false);
        //         $('#prices_increase_rate_field').fadeIn(300);

        //     } else {
        //         $('#prices_increase_rate_field').fadeOut(300);
        //         $('#prices_increase_rate_field').attr("readonly", true);
        //     }
        // });
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



        // $('#quantity_growth_rate,#target_base').on('change', function() {
        //     val = $('#target_base').val();
        //     quantity_growth_rate = parseFloat($('#quantity_growth_rate').val()) / 100;
        //     result = 0;
        //     if (val == 'previous_year') {
        //         result = parseFloat("{{ $sales_forecast['previous_1_year_sales'] }}") * (1 + quantity_growth_rate);
        //     } else if (val == 'previous_3_years') {
        //         result = parseFloat("{{ $sales_forecast['average_last_3_years'] }}") * (1 + quantity_growth_rate);

        //     }
        //     $('#prices_increase_rate').val(result.toFixed(0));
        // });





        $('.quantity_growth_rate').on('change', function () {
            var index = $('.quantity_growth_rate').index(this);

            var forecasted_quantity = (1 + ((parseFloat($(this).val())) / 100)) * ($('.sales_quantity').eq(index).val());
            $('.forecasted_quantity').eq(index).val(forecasted_quantity.toFixed(0));
            if ($('.forecasted_price').eq(index).val() !== '' ) {
                var forecasted_sales_value =  forecasted_quantity*parseFloat($('.forecasted_price').eq(index).val());
                $('.forecasted_sales_value').eq(index).val(forecasted_sales_value.toFixed(0));
            }

        });
        $('.prices_increase_rate').on('change', function () {
            var index = $('.prices_increase_rate').index(this);

            var forecasted_price = (1 + ((parseFloat($(this).val())) / 100)) * ($('.average_price').eq(index).val());
            $('.forecasted_price').eq(index).val(forecasted_price.toFixed(0));
            if ($('.forecasted_quantity').eq(index).val() !== '' ) {

                var forecasted_sales_value =  parseFloat($('.forecasted_quantity').eq(index).val()) * parseFloat(forecasted_price);
                $('.forecasted_sales_value').eq(index).val(forecasted_sales_value.toFixed(0));
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
        $(document).on('change' , '#product_item_check_box , #number_of_products', function(e){
            let oldIsChedked = $('#product_item_check_box').attr('data-old-checked');
            let newIsChecked = $('#product_item_check_box').is(':checked') ? 1 : 0 ;

            let oldNewProductsItems = parseFloat($('#number_of_products_field').attr('data-old-value'));
            let newProductsItems = parseFloat($('#number_of_products').val());

            if(oldIsChedked != newIsChecked  || oldNewProductsItems != newProductsItems) {
                $('#subkit_summary_report_id').addClass('hideit');
            }
            else{
                $('#subkit_summary_report_id').removeClass('hideit');
            }

        })
    </script>

    <script>
        // $('#subkit_summary_report_id').on('click',function(e){
        //     e.preventDefault();
        //     $('form').attr('action'  , "{{ route('go.to.summary.report' , $company->id) }}");
        //     $('form').submit();
        // });
    </script>
@endsection
