@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Expense;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/expenses.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    .js-parent-to-table {
        min-height: 70vh;
    }
.payment_terms{
		min-width:140px !important;
	}
</style>
@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">

        @php

        $tableId = 'expense_per_employee';
        $repeaterId = 'expense_per_employee_repeater';
        $cardId = $tableId;
        @endphp
       
        <div class="kt-portlet parent-card ">
            <div class="kt-portlet__body">
                {{-- start of one time expense --}}
                <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{ $storeRoute }}">
                    @include('non_banking_services.expense-per-employee._input-hidden')

                    <input type="hidden" name="model_id" value="{{ $study->id }}">
                    <input type="hidden" name="expense_type" value="{{ $tableId }}">
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :triggerInputChangeWhenAddNew="true" :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down max-w-200" :title="__('Expense <br> Category')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down" :title="__('Expense <br> Name')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down" :title="__('Department')" :helperTitle="__('Department')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down" :title="__('Employee <br> Position')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Start <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Monthly Cost <br> Per Unit')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('End <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down" :title="__('Payment <br> Terms')" :helperTitle="__('You can either choose one of the system default terms (cash, quarterly, semi-annually, or annually), if else please choose Customize to insert your payment terms')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down rate-class" :title="__('VAT <br> Rate')"></x-tables.repeater-table-th>
                            {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Is <br> Deductible')"></x-tables.repeater-table-th> --}}
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down rate-class" :title="__('Withhold <br> Tax Rate')" :helperTitle="__('Withhold Tax rate will be calculated based on Monthly Amount excluding VAT')"></x-tables.repeater-table-th>
									@if(!$model->isMonthlyStudy())
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down rate-class" :title="__('Annual <br> Increase %')"></x-tables.repeater-table-th>
							@endif
                            {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-2 header-border-down" :title="__('Annual Increase <br> Interval')"></x-tables.repeater-table-th> --}}
                        </x-slot>
                        <x-slot name="trs">
                            @php
	                            $rows = isset($model) ? $model->generateRelationDynamically($tableId,$expenseType)->get() : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof Expense) ){
                            unset($subModel);
                            }

                            @endphp

                            <tr @if($isRepeater) data-repeater-item data-repeater-style @endif>
                                <input type="hidden" name="expense_type" value="manpower">
                                <td class="text-center">
                                    <div class="">
                                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                        </i>
                                    </div>
                                </td>

                                <td>
                                    <div class="min-w-150">
                                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getExpenseCategory() : 'cash'" :options="getEmployeeExpenseCategoriesForSelect2()" :add-new="false" class="select2-select repeater-select expense_category " :all="false" name="@if($isRepeater) expense_category @else {{ $tableId }}[0][expense_category] @endif"></x-form.select>
                                    </div>
                                </td>

                                <td>
                                    <div class="min-w-200">
                                        <x-form.select data-current-selected="{{ isset($subModel) ? $subModel->getExpenseNameId() : '' }}" :selectedValue="isset($subModel) ? $subModel->getExpenseNameId() : ''" :options="[]" :add-new="false" class="select2-select repeater-select expense_name_id " :all="false" name="@if($isRepeater) expense_name_id @else {{ $tableId }}[0][expense_name_id] @endif"></x-form.select>
                                    </div>
                                </td>





                                <td>
                                    <div class="min-w-200">
                                        <x-form.select :multiple="true" :selectedValue="isset($subModel) ? $subModel->getDepartmentIds() : ''" :options="$departmentsFormatted" :add-new="false" class="select2-select repeater-select  js-update-positions-for-department" :all="false" data-current-selected="{{ json_encode(isset($subModel) ? $subModel->getPositionIds():[]) }}" ></x-form.select>
                                    </div>
                                </td>
								
								
                                <td>
                                    <div class="min-w-200">
                                        <x-form.select :multiple="true" :selectedValue="isset($subModel) ? $subModel->getPositionIds() : ''" :options="[]" :add-new="false" class="select2-select repeater-select  position-class" :all="false" name="@if($isRepeater) position_ids @else {{ $tableId }}[0][position_ids] @endif"></x-form.select>

                                    </div>
                                </td>
                                <td>
								<div class="max-w-150">
								@include('components.calendar-month-year',[
                                            'name'=>'start_date',
                                            'value'=>isset($subModel) ? $subModel->getStartDateYearAndMonth() : $study->getOperationStartDateYearAndMonth()
                                            ])
								</div>
                                    {{-- <x-calendar :value="isset($subModel) ? $subModel->getStartDateFormatted() : $study->getStudyStartDate() " :id="'start_date'" name="start_date"></x-calendar> --}}
                                </td>
								
							
                                <td>
                                    <input value="{{ (isset($subModel) ? number_format($subModel->getMonthlyCostOfUnit(),0) : 0) }}" class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getMonthlyCostOfUnit() : 0) }}" @if($isRepeater) name="monthly_cost_of_unit" @else name="{{ $tableId }}[0][monthly_cost_of_unit]" @endif>

                                </td>
									<td>
								  <div class="max-w-150">
                                            @include('components.calendar-month-year',[
                                            'name'=>'end_date',
                                            'value'=>isset($subModel) ? $subModel->getEndDateYearAndMonth() : $study->getStudyEndDateYearAndMonth()
                                            ])
                                        </div>
								</td>
								
                                <td>
                                    <x-form.select :selectedValue="isset($subModel) ? $subModel->getPaymentTerm() : 'cash'" :options="getPaymentTerms()" :add-new="false" class="select2-select repeater-select  payment_terms" :all="false" name="@if($isRepeater) payment_terms @else {{ $tableId }}[0][payment_terms] @endif"></x-form.select>
                                    <x-modal.custom-collection :subModel="isset($subModel) ? $subModel : null " :tableId="$tableId" :isRepeater="$isRepeater" :id="$repeaterId.'test-modal-id'"></x-modal.custom-collection>


                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getVatRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                                        <span style="margin-left:3px	">%</span>
                                        <input type="hidden" value="{{ (isset($subModel) ? $subModel->getVatRate() :0) }}" @if($isRepeater) name="vat_rate" @else name="{{ $tableId }}[0][vat_rate]" @endif>

                                    </div>
                                </td>
                         
                                <td>
                                    <div class="d-flex align-items-center">
                                        <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getWithholdTaxRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                                        <span style="margin-left:3px	">%</span>
                                        <input type="hidden" value="{{ (isset($subModel) ? $subModel->getWithholdTaxRate() : 0) }}" @if($isRepeater) name="withhold_tax_rate" @else name="{{ $tableId }}[0][withhold_tax_rate]" @endif>
                                    </div>
                                </td>
		@if(!$model->isMonthlyStudy())
                                <td>
                                      <div class="d-flex align-items-center increase-rate-parent">
                            {{-- <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getIncreaseRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text"> --}}
                            {{-- <span style="margin-left:3px	">%</span> --}}
							<button class="btn btn-primary btn-md text-nowrap increase-rate-trigger-btn" type="button" data-toggle="modal" >{{ __('Increase Rates') }}</button>
							<x-modal.increase-rates :study="$study" :subModel="isset($subModel) ? $subModel : null " :tableId="$tableId" :isRepeater="$isRepeater" :id="$repeaterId.'test-modal-id'"></x-modal.increase-rates>

                            {{-- <input type="hidden" value="{{ (isset($subModel) ? $subModel->getIncreaseRate() : 0) }}" @if($isRepeater) name="increase_rate" @else name="{{ $tableId }}[0][increase_rate]" @endif> --}}

                        </div>
						
                                </td>
								@endif
                                {{-- <td>
                                    <x-form.select :selectedValue="isset($subModel) ? $subModel->getIncreaseInterval() : 'annually' " :options="getDurationIntervalTypesForSelectExceptMonthly()" :add-new="false" class="select2-select   repeater-select" :all="false" name="@if($isRepeater) increase_interval @else {{ $tableId }}[0][increase_interval] @endif"></x-form.select>

                                </td> --}}


                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>
<x-save-or-continue-btn />
            </div>

        </div>


{{-- {{ dd('e') }} --}}


        </form>
        {{-- </div>
        </div> --}}




        <!--end::Form-->

        <!--end::Portlet-->
    </div>


</div>

</div>



</div>









</div>
</div>
{{-- </form> --}}

</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>



<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            let form = $(this).closest('form')[0];
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);
            let addNewDepartment = $(this).attr('data-save-and-add-new-department');
            addNewDepartment = addNewDepartment ? addNewDepartment : 0;
            formData.append('addNewDepartment', addNewDepartment)

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

<script>
    function reinitalizeMonthYearInput(dateInput) {
        var currentDate = $(dateInput).val();
        var startDate = "{{ isset($studyStartDate) && $studyStartDate ? $studyStartDate : -1 }}";
        startDate = startDate == '-1' ? '' : startDate;
        var endDate = "{{ isset($studyEndDate) && $studyEndDate? $studyEndDate : -1 }}";
        endDate = endDate == '-1' ? '' : endDate;
        if (startDate && endDate) {
            $(dateInput).datepicker({
                    viewMode: "year"
                    , minViewMode: "year"
                    , todayHighlight: false
                    , clearBtn: true,


                    autoclose: true
                    , format: "yyyy-mm-01"
                , })
                .datepicker('setDate', new Date(currentDate))
                .datepicker('setStartDate', new Date(startDate))
                .datepicker('setEndDate', new Date(endDate))
        } else {
            $(dateInput).datepicker({
                    viewMode: "year"
                    , minViewMode: "year"
                    , todayHighlight: false
                    , clearBtn: true,


                    autoclose: true
                    , format: "yyyy-mm-01"
                , })
                .datepicker('setDate', new Date(currentDate))
        }



    }

    $(function() {

        $('.only-month-year-picker').each(function(index, dateInput) {
            //     reinitalizeMonthYearInput(dateInput)
        })
    });
    $('.js-parent-to-table').show();

    $(function() {
        $('#expense_type').trigger('change')

    })

    $(function() {
        $(document).on('click', '.js-show-all-categories-trigger', function() {
            const elementToAppendIn = $(this).parent().find('.js-append-into');
            const texts = [];
            let lis = '';
            text = '<u><a href="#" data-close-new class="text-decoration-none mb-2 d-inline-block text-nowrap ">' + 'Add New' + '</a></u>'
            lis += '<li >' + text + '</li>'
            $(this).closest('table').find('.js-show-all-categories-popup').each(function(index, element) {
                let text = $(element).val().trim();
                if (text && !texts.includes(text)) {
                    texts.push(text)
                    text = '<a href="#" data-add-new class="text-decoration-none mb-2 d-inline-block">' + text + '</a>'
                    lis += '<li >' + text + '</li>'
                }
            })




            elementToAppendIn.removeClass('d-none');
            elementToAppendIn.find('ul').empty().append(lis);
        })


    })
    $(document).on('click', '[data-add-new]', function(e) {
        e.preventDefault();
        let content = $(this).html();
        $(this).closest('.js-common-parent').find('input').val(content);
    })
    $(document).on('click', '[data-close-new]', function(e) {
        e.preventDefault();
        $(this).closest('.js-append-into').addClass('d-none');
        $(this).closest('.js-common-parent').find('input').val('').focus();
    })
    $(document).on('click', function(e) {
        let closestParent = $(e.target).closest('.js-append-into').length;
        if (!closestParent && !$(e.target).hasClass('js-show-all-categories-trigger')) {
            $('.js-append-into').addClass('d-none');
        }
    })
    $(function() {
        $('.repeater-with-select2').closest('.repeater-class').find('[data-repeater-delete]').trigger('click');
        $('.repeater-with-select2').closest('.repeater-class').find('[data-repeater-create]').trigger('click');
    });

</script>
@endsection



@push('js_end')

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
    $(function() {
        $('.rate-element').trigger('change');
    })

</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>


</script>


<script>
    $(document).on('change', 'select.expense_category', function() {
        const parent = $(this).closest('tr');
        const expenseCategoryId = $(this).val();
        const currentSelected = $(parent).find('select.expense_name_id').attr('data-current-selected');
        $.ajax({
            url: "{{ route('get.expense.name.for.category.only.in.employee',['company'=>$company->id,'study'=>$study->id]) }}"
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

@endpush
