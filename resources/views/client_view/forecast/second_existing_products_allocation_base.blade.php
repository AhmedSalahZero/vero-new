@extends('layouts.dashboard')

@section('css')
    <link href="{{ url('assets/vendors/general/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css" />
    {{-- <link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> --}}
    @include('datatable_css')
    <link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet"
        type="text/css" />
    <style>
        table {
            white-space: nowrap;
            font-size:12px !important;

        }
		
		table.dataTable tbody tr>.dtfc-fixed-right,
    table.dataTable tbody tr>.dtfc-fixed-left {
        right: 0 !important;
        background-color: #086691 !important;
        color: white;
    }

    </style>
@endsection
@section('content')
    <form action="{{ route('second.existing.products.allocations', $company) }}" method="POST">
        @csrf
@if(canShowNewItemsProducts($company->id) && count($sales_targets_values))
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <?php $total_new_items_targets = array_sum($sales_targets_values); ?>
                    <h2>
                        {{ __('New Products Items Sales Target Year ') .date('Y', strtotime($sales_forecast->start_date)) .' : ' .number_format($total_new_items_targets) }}
                    </h2>
                </div>
            </div>
            <div class="kt-portlet__body">

                <x-table :tableTitle="__('New Product Items Table')" :tableClass="'kt_table_with_no_pagination_no_fixed_right'">
                    @slot('table_header')
                        <tr class="table-active text-center">
                            <th>{{ __(str_replace('_', ' ', ucwords($allocation_base))) }}</th>
                            <th>{{ __('Sales Target Value') }}</th>
                            @if ($sales_forecast->target_base !== 'new_start' || $sales_forecast->new_start !== 'product_target' || true /*by salah*/ )
                                <th>{{ __('Sales Target %') }}</th>
                            @endif
                        </tr>
                    @endslot
                    @slot('table_body')
                    @php
                        $percentages = [];
                        sortTwoDimensionalArr($sales_targets_values);
                    @endphp
                        @foreach ($sales_targets_values as $base_vame => $target)
                            <?php $percentages[$base_vame] = $total_new_items_targets == 0 ? 0 : ($target / $total_new_items_targets) * 100; ?>
                            <tr>
                                <td>{{ $base_vame }}</td>
                                <td class="text-center">{{ number_format($target) }}</td>
                                <td class="text-center">{{ number_format($percentages[$base_vame] ?? 0, 2) . ' %' }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td class="active-style">{{ __('Total') }}</td>
                            <td class="text-center active-style">{{ number_format($total_new_items_targets) }}</td>
                            <td class="text-center active-style">{{ number_format(array_sum($percentages), 2) . ' %' }}</td>
                        </tr>
                    @endslot
                </x-table>
            </div>
        </div>
        @else
        @php
        $total_new_items_targets  = 0;

        @endphp
@endif

        <?php $item = ucwords(str_replace('_', ' ', $allocation_base)); ?>

        {{-- @if(hasAtLeastOneOfType($company , $allocation_base)) --}}

        @if(hasProductsItems($company))
        <div class="kt-portlet">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <?php $existing_items_target = $sales_forecast->sales_target - $total_new_items_targets; ?>
                    <h2>
                        {{ __('Existing Products Items Sales Target Year ') .date('Y', strtotime($sales_forecast->start_date)) .' : ' .number_format($existing_items_target) }}
                    </h2>
                </div>
            </div>
            <input type="hidden" name="total_existing_target" value="{{$existing_items_target}}">
            <div class="kt-portlet__body">
                @if ($allocations_setting->breakdown === 'new_breakdown_quarterly')
                    <x-table :tableTitle="__($item.' Sales Targets Values Table')"
                        :tableClass="'kt_table_with_no_pagination'">
                        @slot('table_header')
                            <tr class="table-active text-center">
                                <th>{{ __('Item') }}</th>
                                @foreach ($sales_targets as $quarter_name => $value)
                                    <th>{{ __($quarter_name) }}</th>
                                @endforeach
                                <th>{{ __('Total Year') }}</th>

                            </tr>
                        @endslot
                        @slot('table_body')
                            <tr>
                                <td>{{ __('Quaterly Sales Target Values') }}</td>
                                @foreach ($sales_targets as $quarter_name => $value)
                                    <td class="text-center">{{ number_format($value ?? 0) }}</td>
                                @endforeach
                                <td class="text-center">{{ number_format(array_sum($sales_targets)) }}</td>
                            </tr>
                        @endslot
                    </x-table>
                @endif
                <br>
                <br>

                <div class="kt-portlet">
                    <div class="kt-portlet__foot">
                        <div class="kt-form__actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="kt-option bg-secondary">
                                        <span class="kt-option__control">
                                            <span
                                                class="kt-checkbox kt-checkbox--bold kt-checkbox--brand kt-checkbox--check-bold"
                                                checked>
                                                <input class="rows" name="use_modified_targets" type="checkbox"
                                                    value="1"
                                                    {{  ($existing_allocations_base['use_modified_targets']??(old('use_modified_targets'))) == 0 ?: 'checked' }}
                                                    id="product_item_check_box">
                                                <span></span>
                                            </span>
                                        </span>
                                        <span class="kt-option__label d-flex">
                                            <span class="kt-option__head mr-auto p-2">
                                                <span class="kt-option__title">
                                                    <b>
                                                        {{ __('Click To Activate Modified Targets') }}
                                                    </b>
                                                </span>

                                            </span>
                                        </span>
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->has('percentages_total'))
                    <h4 style="color: red"><i class="fa fa-hand-point-right">
                        </i></i>{{ $errors->first('percentages_total') }}</h4>
                @endif

                <x-table :tableTitle="__($item.' Sales Target Values Table')" :tableClass="'kt_table_with_no_pagination_no_fixed_right'">
                    @slot('table_header')
                        <tr class="table-active text-center">
                            <th>{{ __($item . ' Name') }}</th>
                            <th>{{ __(($allocations_setting->breakdown == 'last_3_years' ? 'Last 3 Years Average' : 'Pervious Year') . ' Sales Value') }}
                            </th>
                            @if ($allocations_setting->breakdown !== 'new_breakdown_annually')
                                <th>{{ __('Sales Target Value') }}</th>
                                <th>{{ __('Sales Target %') }}</th>
                            @endif
                            @if ($allocations_setting->breakdown !== 'new_breakdown_quarterly')
                                <th>{{ __(($allocations_setting->breakdown !== 'new_breakdown_annually' ? 'Modify' : 'Insert') . ' Sales Target') }}
                                </th>
                                <th style="width: 8% !important">
                                    {{ __(($allocations_setting->breakdown !== 'new_breakdown_annually' ? 'Modify' : 'Insert') . ' Sales %') }}
                                </th>
                            @else
                                @foreach ($sales_targets as $quarter_name => $value)
                                    <th>{{ __('Modify ' . $quarter_name . ' Sales Target') }}</th>
                                    <th style="width: 8% !important">{{ __('Modify ' . $quarter_name . ' Sales %') }}</th>
                                @endforeach
                            @endif
                        </tr>
                    @endslot
                    @slot('table_body')
                        <?php $total = array_sum(array_column($breakdown_base_data, 'Sales Value'));
                        $allocations_setting->breakdown !== 'last_3_years' ?: ($total = $total / 3);
                        $total_existing_targets = 0;
                         sortTwoDimensionalBaseOnKey($breakdown_base_data , 'Sales Value');
                        ?>
                        @foreach ($breakdown_base_data as $key => $product_data)
                            <tr>
                                <td>{{ $product_data['item'] ?? '-' }}</td>
                                <?php
                                $sales_value = $allocations_setting->breakdown == 'last_3_years' ? ($product_data['Sales Value'] ?? 0) / 3 : $product_data['Sales Value'] ?? 0;
                                $target_percentage = $total == 0 ? 0 : $sales_value / $total;
                                $existing_target_per_product = $target_percentage * $existing_items_target;
                                $total_existing_targets += $existing_target_per_product;
                                ?>
                                <td class="text-center">{{ number_format($sales_value ?? 0) }}</td>

                                @if ($allocations_setting->breakdown !== 'new_breakdown_annually')
                                    <td class="text-center">{{ number_format($existing_target_per_product) }}</td>
                                    <input type="hidden" name="existing_products_target[{{ $product_data['item'] }}]"
                                        value="{{ $existing_target_per_product }}">
                                    <td class="text-center light-gray-bg">
                                        {{ number_format($target_percentage * 100, 1) . ' %' }}
                                    </td>
                                @endif
                                @if ($allocations_setting->breakdown !== 'new_breakdown_quarterly')
                                    <td class="text-center">
                                        <input type="number" {{-- name="modify_sales_target[{{ $product_data['item'] }}][value]" --}} placeholder="{{ __('Value') }}"
                                            class="modify_sales_target form-control" {{-- value="{{ @$modified_targets['products_modified_targets'][$product_data['item']] }}" --}}>
                                    </td>
                                    <?php
                                        if ($existing_allocations_base === null ) {
                                            $percentage = old('modify_sales_target')[ $product_data['item']] ?? '';

                                        } else {
                                            $percentage = $existing_allocations_base['allocation_base_percentages'][$product_data['item']] ?? '';
                                            if (is_array($percentage)) {
                                                $percentage = '';
                                            }
                                        }
                                    ?>
                                    <td class="text-center light-gray-bg">
                                        <input type="number" name="modify_sales_target[{{ $product_data['item'] }}]"
                                            placeholder="{{ __('%') }}"
                                            class="modify_sales_target_percentage form-control" value="{{ $percentage }}">
                                    </td>
                                @else
                                    @foreach ($sales_targets as $quarter_name => $value)
                                        <?php $quarter_name = str_replace(' ', '_', $quarter_name); ?>

                                        <td class="text-center">
                                            <input type="number" {{-- name="modify_sales_target[{{ $product_data['item'] }}][{{ $quarter_name }}][value]" --}} placeholder="{{ __('Value') }}"
                                                class="modify_sales_target_{{ $quarter_name }} form-control"
                                                data-quarter_annual_target="{{ $value }}" {{-- value="{{ @$existing_allocations_base['products_modified_targets'][$product_data['item']][$quarter_name]  }}" --}}>
                                        </td>

                                        <td class="text-center light-gray-bg">
                                            <input type="number"
                                                name="modify_sales_target[{{ $product_data['item'] }}][{{ $quarter_name }}]"
                                                data-quarter_annual_target="{{ $value }}"
                                                value="{{ $existing_allocations_base['allocation_base_percentages'][$product_data['item']][$quarter_name] ?? '' }}"
                                                placeholder="{{ __('%') }}"
                                                class="modify_sales_target_percentage_{{ $quarter_name }} form-control">
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                        <tr class="table-active text-center">
                            <th>{{ __('Total') }}</th>
                            <td>{{ number_format($total) }}</td>
                            @if ($allocations_setting->breakdown !== 'new_breakdown_annually')
                                <td>{{ number_format($total_existing_targets) }}</td>
                                <td>100.00 %</td>
                            @endif
                            @if ($allocations_setting->breakdown !== 'new_breakdown_quarterly')
                                <td id="total_modify_sales_target">
                                    {{ !isset($existing_allocations_base['products_modified_targets'])? 0: number_format(array_sum(array_column($existing_allocations_base['products_modified_targets'], 'value') ?? [])) }}
                                </td>
                                <td id="total_modify_sales_target_percentage">
                                    {{ !isset($existing_allocations_base['products_modified_targets'])? 0: number_format(array_sum(array_column($existing_allocations_base['products_modified_targets'], 'percentage') ?? [])) }}
                                </td>
                            @else
                                @foreach ($sales_targets as $quarter_name => $value)
                                    <?php $quarter_name = str_replace(' ', '_', $quarter_name); ?>
                                    <td id="total_modify_sales_target_{{ $quarter_name }}">
                                        {{ !isset($modified_targets['products_modified_targets'])? 0: number_format(array_sum(array_column($modified_targets['products_modified_targets'], 'value') ?? [])) }}
                                    </td>
                                    <td id="total_modify_sales_target_percentage_{{ $quarter_name }}">
                                        {{ !isset($modified_targets['products_modified_targets'])? 0: number_format(array_sum(array_column($modified_targets['products_modified_targets'], 'percentage') ?? [])) }}
                                    </td>
                                @endforeach
                            @endif

                        </tr>
                    @endslot
                </x-table>
            </div>
        </div>
        @endif


        <x-submitting />

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

    @if ($allocations_setting->breakdown !== 'new_breakdown_quarterly')
        <script>
            $(document).ready(function() {
                $('.modify_sales_target_percentage').each(function(index, element) {
                    // element == this
                    var modify_sales_target_percentage = parseFloat($(this).val()) / 100;

                    modifiedTragetPercentage(index, modify_sales_target_percentage);
                });
            });
        </script>
    @endif


    @if ($allocations_setting->breakdown == 'new_breakdown_quarterly')
        @foreach ($sales_targets as $quarter_name => $value)
            <?php $quarter_name = str_replace(' ', '_', $quarter_name); ?>
            <script>
                $(document).ready(function() {
                    $('.modify_sales_target_percentage_' + '{{ $quarter_name }}').each(function(index, element) {
                        // element == this

                        var modify_sales_target_percentage = parseFloat($(this).val()) / 100;
                        var value = (modify_sales_target_percentage * parseFloat("{{ $value }}"));
                        quarterPercentages("{{ $quarter_name }}", index, modify_sales_target_percentage, value);
                    });
                });


                $('.modify_sales_target_' + '{{ $quarter_name }}').on('change', function() {
                    var index = $('.modify_sales_target_' + '{{ $quarter_name }}').index(this);
                    var modify_sales_target = parseFloat($(this).val());

                    var percentage = (modify_sales_target / parseFloat("{{ $value }}")) * 100;
                    $('.modify_sales_target_percentage_' + '{{ $quarter_name }}').eq(index).val(percentage.toFixed(2));
                    totalFunction('.modify_sales_target_' + '{{ $quarter_name }}', '#total_modify_sales_target_' +
                        '{{ $quarter_name }}', 0, null);
                    totalFunction('.modify_sales_target_percentage_' + '{{ $quarter_name }}',
                        '#total_modify_sales_target_percentage_' + '{{ $quarter_name }}', 2, '%');
                });


                $('.modify_sales_target_percentage_' + '{{ $quarter_name }}').on('change', function() {
                    var index = $('.modify_sales_target_percentage_' + '{{ $quarter_name }}').index(this);
                    var modify_sales_target_percentage = parseFloat($(this).val()) / 100;
                    var value = (modify_sales_target_percentage * parseFloat("{{ $value }}"));




                    quarterPercentages("{{ $quarter_name }}", index, modify_sales_target_percentage, value);
                });
            </script>
        @endforeach
    @endif
    {{-- Quarters change Percentages --}}
    <script>
        function quarterPercentages(quarter_name, index, modify_sales_target_percentage, value) {
            $('.modify_sales_target_' + quarter_name).eq(index).val(value.toFixed(0));
            totalFunction('.modify_sales_target_percentage_' + quarter_name,
                '#total_modify_sales_target_percentage_' + quarter_name, 2, '%');
            totalFunction('.modify_sales_target_' + quarter_name, '#total_modify_sales_target_' + quarter_name, 0, null);
        }
    </script>
    <script>
        $('#allocation_base').on('change', function() {
            val = $(this).find('option:selected').text();
            $('#add_new_items').attr('disabled', false);

            $('#item_type').html(' ' + $.trim(val));
        });


        $('#add_new_items').change(function() {
            if ($(this).prop("checked")) {
                $('#number_of_items').fadeIn(300);
            } else {
                $('#number_of_items').fadeOut(300);
            }

        });

        // Changing Target
        $('.modify_sales_target').on('change', function() {
            var index = $('.modify_sales_target').index(this);
            var modify_sales_target = parseFloat($(this).val());
            var percentage = (modify_sales_target / parseFloat("{{ $existing_items_target ?? 0 }}")) * 100;
            $('.modify_sales_target_percentage').eq(index).val(percentage.toFixed(2));

            totalFunction('.modify_sales_target_percentage', '#total_modify_sales_target_percentage', 2, '%');
            totalFunction('.modify_sales_target', '#total_modify_sales_target', 0, null);
        });

        // Changing Percentage
        $('.modify_sales_target_percentage').on('change', function() {
            var index = $('.modify_sales_target_percentage').index(this);
            var modify_sales_target_percentage = parseFloat($(this).val()) / 100;

            modifiedTragetPercentage(index, modify_sales_target_percentage);
        });

        function modifiedTragetPercentage(index, modify_sales_target_percentage) {
            var value = (modify_sales_target_percentage * parseFloat("{{ $existing_items_target ?? 0 }}"));
            $('.modify_sales_target').eq(index).val(value.toFixed(0));
            totalFunction('.modify_sales_target_percentage', '#total_modify_sales_target_percentage', 2, '%');
            totalFunction('.modify_sales_target', '#total_modify_sales_target', 0, null);
        }

        function totalFunction(field_name, total_field_name, decimals, characters = null) {
            total = 0;
            $(field_name).each(function(index, element) {

                if (element.value !== '') {
                    total = parseFloat(element.value) + total;
                }

            });
            if (characters === null) {
                total = total.toFixed(decimals);
            } else {
                total = total.toFixed(decimals) + ' %';

            }
            $(total_field_name).html(total);
        }
    </script>
@endsection
;
