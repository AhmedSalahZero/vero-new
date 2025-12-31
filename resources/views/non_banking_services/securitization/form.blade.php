@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Securitization;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/expenses.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    .expenses-table {
        min-height: 50vh !important;
    }

</style>
@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">
        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{ $storeRoute }}">
            @csrf
            <input type="hidden" name="model_id" value="{{ $model->id ?? 0  }}">
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="model_name" value="Study">
            <input type="hidden" name="expense_type" value="{{ $expenseType }}">
            <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">
            <input type="hidden" id="study-start-date" value="{{ $study->getStudyStartDate() }}">
            <input type="hidden" id="study-end-date" value="{{ $study->getStudyEndDate() }}">


            <div class="kt-portlet" style="">


                <div class="kt-portlet__body">

                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row justify-content-center">
                                @php
                                $index = 0 ;
                                @endphp


                                @php
                                $tableId = 'securitizations';
                                $repeaterId = $tableId.'_repeater';
                                @endphp
                                <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                                <x-tables.repeater-table :initEmpty="!count($model->securitizations )" :firstElementDeletable="true" :hideByDefault="false" :removeRepeater="false" :repeater-with-select2="true" :parentClass="' js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                                    <x-slot name="ths">
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Revenue <br> Stream')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down " :title="__('Disbursement <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Securitization <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Annual <br> Discount Rate')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Collection Revenue <br> Rate')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('MTLs Early Settlement  <br> Expense Rate')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                        <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Securitization <br> Expense')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                                    </x-slot>

                                    <x-slot name="trs">
                                        @php
                                        $rows = isset($model) ? $model->securitizations : [-1] ;
                                        @endphp
                                        @foreach( count($rows) ? $rows : [-1] as $subModel)
                                        @php
                                        if( !($subModel instanceof Securitization) ){
                                        unset($subModel);
                                        }

                                        @endphp

                                        <tr @if($isRepeater) data-repeater-item data-repeater-style @endif>
                                            <td class="text-center">
                                                <div class="">
                                                    <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                                    </i>
                                                </div>
                                            </td>


                                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                                            <td>
                                                <x-form.select :selectedValue="isset($subModel) ? $subModel->getRevenueStreamType() : ''" :options="$revenueStreamTypes" :add-new="false" class="select2-select repeater-select  " :all="false" name="@if($isRepeater) revenue_stream_type @else {{ $tableId }}[0][revenue_stream_type] @endif"></x-form.select>
                                            </td>



                                            <td>
                                                <div class="max-w-">
                                                    @include('components.calendar-month-year',[
                                                    'name'=>'disbursement_date',
                                                    'value'=>isset($subModel) ? $subModel->getDisbursementDateYearAndMonth() : $study->getOperationStartDateYearAndMonth()
                                                    ])

                                                </div>
                                            </td>

                                            <td>
                                                <div class="max-w-">
                                                    @include('components.calendar-month-year',[
                                                    'name'=>'securitization_date',
                                                    'value'=>isset($subModel) ? $subModel->getSecuritizationDateYearAndMonth() : $study->getStudyEndDateYearAndMonth()
                                                    ])
                                                </div>

                                            </td>

                                            <td>

                                                <div class="d-flex align-items-center ">
                                                    <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getDiscountRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                                                    <span style="margin-left:3px	">%</span>
                                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getDiscountRate() : 0) }}" @if($isRepeater) name="discount_rate" @else name="{{ $tableId }}[0][discount_rate]" @endif>
                                                </div>
                                            </td>

                                            <td>

                                                <div class="d-flex align-items-center ">
                                                    <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getCollectionRevenueRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                                                    <span style="margin-left:3px	">%</span>
                                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getCollectionRevenueRate() : 0) }}" @if($isRepeater) name="collection_revenue_rate" @else name="{{ $tableId }}[0][collection_revenue_rate]" @endif>
                                                </div>
                                            </td>

                                            <td>

                                                <div class="d-flex align-items-center ">
                                                    <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getEarlySettlementsExpenseRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                                                    <span style="margin-left:3px	">%</span>
                                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getEarlySettlementsExpenseRate() : 0) }}" @if($isRepeater) name="early_settlements_expense_rate" @else name="{{ $tableId }}[0][early_settlements_expense_rate]" @endif>
                                                </div>
                                            </td>

                                            <td>
                                                <input value="{{ (isset($subModel) ? number_format($subModel->getSecuritizationExpenseAmount(),0) : 0) }}" class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
                                                <input type="hidden" value="{{ (isset($subModel) ? $subModel->getSecuritizationExpenseAmount() : 0) }}" @if($isRepeater) name="expense_amount" @else name="{{ $tableId }}[0][expense_amount]" @endif>
                                            </td>
                                        </tr>
                                        @endforeach

                                    </x-slot>




                                </x-tables.repeater-table>
                                {{-- end of fixed monthly repeating amount --}}

                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-12 text-right">
                            <input type="submit" name="save" class="btn active-style save-form" value="{{  __('Calculate') }}">
                        </div>

                    </div>

                </div>
            </div>









            @if(count($securitizationCalculations))
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Securitization Calculation Table') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Revenue <br> Stream') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Disbursement <br> Date') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Disbursement <br> Amount') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Schedule <br> Amounts') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Securitization <br> Date') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Net Present <br> Value') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Profit Or <br> Loss') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Collection <br> Revenue') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Settlements <br> Expense') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Securitization <br> Expense') !!}</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($securitizationCalculations as $securitizationCalculation)
                                    @if(!isset($securitizationCalculation['revenue_stream_type'] ))
                                    @continue
                                    @endif


                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ $securitizationCalculation['revenue_stream_type'] }}" disabled="" class="form-control text-left " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ $securitizationCalculation['disbursement_date'] }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['portfolio_disbursement_amount']??0) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['portfolio_schedule_payment_sum']??0) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ $securitizationCalculation['securitization_date'] }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>


                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['net_present_value']) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['securitization_profit_or_loss']) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format(array_sum($securitizationCalculation['collection_revenue_amounts']??[])) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['early_settlements_expense_amount']??0) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ number_format($securitizationCalculation['securitization_expense_amount']) }}" disabled="" class="form-control " type="text">
                                            </div>

                                        </td>




                                    </tr>
                                    @endforeach






                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-12 text-right">
                            <input type="submit" name="save_and_next" class="btn active-style save-form" value="{{  __('Save & Next') }}">
                        </div>

                    </div>


                </div>
            </div>
            @endif







    </div>


</div>
</div>











</form>

</div>
@endsection
@section('js')

<x-js.commons></x-js.commons>



<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            let form = document.getElementById('form-id');
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);
            var saveAndContinue = $(this).attr('name');
            formData.append('save', saveAndContinue);
            $.ajax({
                cache: false
                , contentType: false
                , processData: false
                , url: form.getAttribute('action')
                , data: formData
                , type: form.getAttribute('method')
                , success: function(res) {
                    $('.save-form').prop('disabled', false)

                    Swal.fire({
                        icon: 'success'
                        , title: res.message,

                    });

                    window.location.href = res.redirectTo;




                }
                , complete: function() {
                    $('#enter-name').modal('hide');
                    $('#name-for-calculator').val('');

                }
                , error: function(res) {
                    $('.save-form').prop('disabled', false);
                    $('.submit-form-btn-new').prop('disabled', false)
					let errorMessage = res.responseJSON.message;
					if (res.responseJSON && res.responseJSON.errors) {
                            errorMessage = res.responseJSON.errors[Object.keys(res.responseJSON.errors)[0]][0]
                        }
                    Swal.fire({
                        icon: 'error'
                        , title: errorMessage
                    , });
                }
            });
        }
    })

</script>


@endsection



@push('js_end')
<script src="{{ url('custom/math.js') }}" type="text/javascript"></script>

<script>
</script>
<script>
    $(document).on('change', 'input:not([placeholder])[type="number"],input:not([placeholder])[type="password"],input:not([placeholder])[type="text"],input:not([placeholder])[type="email"],input:not(.exclude-text)', function() {
        if (!$(this).hasClass('exclude-text')) {
            let val = $(this).val()
            val = number_unformat(val)
            if (isNumber(val)) {
                $(this).parent().find('input[type="hidden"]:not([name="_token"])').val(val)
            }

        }
    })
    $(document).on('click', '.repeat-to-r', function() {
        const columnIndex = $(this).data('column-index');
        const digitNumber = $(this).data('digit-number');
        const val = $(this).parent().find('input[type="hidden"]').val();
        $(this).closest('tr').find('.can-be-repeated-parent').each(function(index, parent) {
            if (index > columnIndex) {
                $(parent).find('.can-be-repeated-text').val(val);
                $(parent).find('.can-be-repeated-text').val(number_format(val, digitNumber));

            }
        })
    })


    $('select.js-condition-to-select').change(function() {
        const value = $(this).val();
        const conditionalValueTwoInput = $(this).closest('tr').find('input.conditional-b-input');
        if (value == 'between-and-equal' || value == 'between') {
            conditionalValueTwoInput.prop('disabled', false).trigger('change');
        } else {
            conditionalValueTwoInput.prop('disabled', true).trigger('change');
        }
    })

    $('select.js-condition-to-select').trigger('change');
    $(document).on('change', '.conditional-input', function() {
        if (!$(this).closest('tr').find('conditional-b-input').prop('disabled')) {
            const conditionalA = $(this).closest('tr').find('.conditional-a-input').val();
            const conditionalB = $(this).closest('tr').find('.conditional-b-input').val();
            if (conditionalA >= conditionalB) {
                if (conditionalA == 0 && conditionalB == 0) {
                    return;
                }
                Swal.fire('conditional a must be less than conditional b value');
                $(this).closest('tr').find('.conditional-a-input').val($(this).closest('tr').find('.conditional-b-input').val() - 1);
            }
        }

    })

</script>
<script>
    $(document).on('change', '.rate-element', function() {
        let total = 0;
        const parent = $(this).closest('tbody');
        parent.find('.rate-element-hidden').each(function(index, element) {
            total += parseFloat($(element).val());
        });
        parent.find('td.td-for-total-payment-rate').html(number_format(total, 2) + ' %');

    })

</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>
    $(document).on('change', 'select.expense_category', function() {
        const parent = $(this).closest('tr');
        const expenseCategoryId = $(this).val();
        const currentSelected = $(parent).find('select.expense_name_id').attr('data-current-selected');
        $.ajax({
            url: "{{ route('get.expense.name.for.category',['company'=>$company->id,'study'=>$study->id]) }}"
            , data: {
                expenseCategoryId
            }
            , success: function(res) {
                let result = res.data;
                let options = '';
                for (index in result) {
                    var row = result[index];
                    options += `<option ${currentSelected==row.id ? 'selected':''} value="${row.id}">${row.name}</option>`;
                }
                $(parent).find('select.expense_name_id').empty().append(options).trigger('change');
            }
        })
    })
    $('select.expense_category').trigger('change')

</script>

<script>
    $(function() {
        //	$('button.js-type-btn[data-value="percentage_of_sales"]').trigger('click')
    })

</script>
<script>


</script>
<script>


</script>
@endpush
