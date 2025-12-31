@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Expense;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/expenses.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">

@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">
        {{-- {{ dd($storeRoute) }} --}}

        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{ $storeRoute }}">



            @php

            $tableId = 'newBranchOpeningProjections';
            $repeaterId = $tableId.'_repeater';
            $cardId = $tableId;
            @endphp

            <div class="kt-portlet  ">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Branches Openings Projection') }} </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>

                    {{-- start of one time expense --}}
                    @csrf
                    <input type="hidden" name="model_id" value="{{ $model->id ?? 0  }}">
                    <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
                    <input type="hidden" name="model_name" value="Study">
                    <input type="hidden" name="study_id" id="study-id-js" value="{{ $study->id }}">

                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">

                    <x-tables.repeater-table :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('New Branches <br> Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Start <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Operation <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Total <br> Branches')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php

                            $rows = isset($model) ?$model->newBranchOpeningProjections : [-1] ;
                            @endphp

                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof \App\Models\NonBankingService\NewBranchOpeningProjection) ){
                            unset($subModel);
                            }

                            @endphp
                            <tr @if($isRepeater) data-repeater-item data-repeater-style @endif>

                                <td class="text-center">
                                    <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                                    <div class="">
                                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                        </i>
                                    </div>
                                </td>
                                <td>
                                    <input value="{{ (isset($subModel) ? $subModel->getCounts() : 0) }}" class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getCounts() : 0) }}" @if($isRepeater) name="counts" @else name="{{ $tableId }}[0][counts]" @endif>

                                </td>
                                <td>
                                    <x-calendar :value="isset($subModel) ? $subModel->getStartDateAsString() : $study->getStudyStartDate() " :id="'start_date'" name="start_date_as_string"></x-calendar>
                                </td>

                                <td>
                                    <x-calendar :value="isset($subModel) ? $subModel->getOperationDateAsString() : $study->getOperationStartDate() " :id="'start_date'" name="start_date_as_string"></x-calendar>
                                </td>

                                <td>
                                    <input value="{{ (isset($subModel) ? number_format($subModel->getTotalBranches(),0) : 0) }}" class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getTotalBranches() : 0) }}" @if($isRepeater) name="total_branches" @else name="{{ $tableId }}[0][total_branches]" @endif>

                                </td>

                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>

                </div>
            </div>



            <div class="kt-portlet " style="margin-bottom:5px;">


                <div class="kt-portlet__body">

                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Branches Loan Cases Projection') }} </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>


                    @php

                    $tableId = 'newBranchLoanCaseProjections';
                    $repeaterId = $tableId.'_repeater';

                    @endphp


                    @csrf

                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    {{-- <x-tables.repeater-table :canAddNewItem="false" :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="false">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Loan Cases <br> Per Office')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('First 3 Months')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Second 3 Months')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Third Months')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Fourth Months')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($model) ?$model->newBranchLoanCaseProjections : [-1] ;
                            @endphp
                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof \App\Models\NonBankingService\NewBranchLoanCaseProjection) ){
                            unset($subModel);
                            }
                            @endphp
                            <tr @if($isRepeater) data-repeater-item data-repeater-style @endif>

                                <td class="text-center">
                                    <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                                    <div class="">
                                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                        </i>
                                    </div>
                                </td>

                                <td>
                                    <input readonly value="{{ __('Average Loan Cases Count Per Officer Per Month') }}" class="form-control" type="text">

                                </td>

                                <td>
                                    <x-repeat-right-dot-inputs :mark="__('Count')" :removeThreeDots="true" :removeThreeDotsClass="true" :numberFormatDecimals="0" :removeCurrency="true" :name="'first_three_count'" :currentVal="isset($subModel) ? $subModel->getFirstThreeCount() :0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="-1"></x-repeat-right-dot-inputs>


                                </td>

                                <td>
                                    <x-repeat-right-dot-inputs :mark="__('Count')" :removeThreeDots="true" :removeThreeDotsClass="true" :numberFormatDecimals="0" :removeCurrency="true" :name="'second_three_count'" :currentVal="isset($subModel) ? $subModel->getSecondThreeCount() :0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="-1"></x-repeat-right-dot-inputs>


                                </td>
                                <td>
                                    <x-repeat-right-dot-inputs :mark="__('Count')" :removeThreeDots="true" :removeThreeDotsClass="true" :numberFormatDecimals="0" :removeCurrency="true" :name="'third_three_count'" :currentVal="isset($subModel) ? $subModel->getThirdThreeCount() :0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="-1"></x-repeat-right-dot-inputs>


                                </td>
                                <td>
                                    <x-repeat-right-dot-inputs :mark="__('Count')" :removeThreeDots="true" :removeThreeDotsClass="true" :numberFormatDecimals="0" :removeCurrency="true" :name="'fourth_three_count'" :currentVal="isset($subModel) ? $subModel->getFourthThreeCount() :0" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="-1"></x-repeat-right-dot-inputs>


                                </td>
                          



                            </tr>
                            @endforeach




                        </x-slot>




                    </x-tables.repeater-table> --}}





                </div>
            </div>


    
            <div class="row btn-for-submit--js {{ isset($isHidden)&&$isHidden ? 'd-none':'' }}">
                <div class="col-lg-6">

                </div>
                <div class="col-lg-6 kt-align-right">
                    <button type="submit" class="btn active-style">
                        {{ __('Save & Go To Next') }}
                    </button>
                </div>
            </div>
       



            <!--end::Form-->

            <!--end::Portlet-->
    </div>


</div>

</div>




</div>









</div>
</div>
</form>

</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>

<script>
    $(document).on('change', '.financial-statement-type', function() {
        validateDuration();
    })
    $(document).on('change', 'select[name="duration_type"]', function() {
        validateDuration();
    })
    $(document).on('change', '#duration', function() {
        validateDuration();
    })

    function validateDuration() {
        let type = $('input[name="type"]:checked').val();
        let durationType = $('select[name="duration_type"]').val();
        let duration = $('#duration').val();
        let isValid = true;
        let allowedDuration = 24;
        if (type == 'forecast' && durationType == 'monthly') {
            allowedDuration = 24;
            isValid = duration <= allowedDuration;
        }
        if (type == 'forecast' && durationType == 'quarterly') {
            allowedDuration = 8;
            isValid = duration <= allowedDuration
        }
        if (type == 'forecast' && durationType == 'semi-annually') {
            allowedDuration = 4
            isValid = duration <= allowedDuration
        }
        if (type == 'forecast' && durationType == 'annually') {
            allowedDuration = 2;
            isValid = duration <= allowedDuration
        }
        if (type == 'actual' && durationType == 'monthly') {
            allowedDuration = 36;
            isValid = duration <= allowedDuration;
        }
        if (type == 'actual' && durationType == 'quarterly') {
            allowedDuration = 12
            isValid = duration <= allowedDuration;
        }
        if (type == 'actual' && durationType == 'semi-annually') {
            allowedDuration = 6;
            isValid = duration <= allowedDuration
        }
        if (type == 'actual' && durationType == 'annually') {
            allowedDuration = 3
            isValid = duration <= allowedDuration
        }
        let allowedDurationText = "{{ __('Allowed Duration') }}";

        $('#allowed-duration').html(allowedDurationText + '  ' + allowedDuration)

        if (!isValid) {
            Swal.fire({
                icon: 'error'
                , title: 'Invalid Duration. Allowed [ ' + allowedDuration + ' ]'
            , })

            $('#duration').val(allowedDuration).trigger('change');

        }


    }

    $(function() {
        $('.financial-statement-type').trigger('change')

    })

</script>

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
    //  $(document).on('change', '#expense_type', function() {
    //      $('.js-parent-to-table').hide();
    //      let tableId = '.' + $(this).val();
    //      $(tableId).closest('.js-parent-to-table').show();
    //
    //  }) 
    $(document).on('click', '.js-type-btn', function(e) {
        e.preventDefault();
        const mainCardId = $(this).attr('data-value')
        $('.js-parent-to-table').show();
        $('.js-type-btn').removeClass('active');
        $(this).addClass('active');
        $('.parent-card').hide();
        $('[data-card-id="' + mainCardId + '"]').show();
    })
    $(function() {
        $('#expense_type').trigger('change')
        //  $('.js-type-btn.active').trigger('click')
        $('.js-parent-to-table').show();
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
@endpush
