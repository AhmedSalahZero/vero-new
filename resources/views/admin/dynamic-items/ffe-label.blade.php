@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
@endsection
@section('sub-header')
<style>
.max-w-abb{
	max-width:80px !important;
	width:80px !important;
	min-width:80px !important;
	
}
.max-w-code{
		max-width:80px !important;
	width:80px !important;
	min-width:80px !important;
}
.max-w-150{
	max-width:150px !important;
	width:150px !important;
	min-width:150px !important;
}
    .max-w-checkbox {
        min-width: 25px !important;
        width: 25px !important;
    }

    .customize-elements .bootstrap-select {
        min-width: 100px !important;
        text-align: center !important;
    }

    .customize-elements input.only-percentage-allowed {
        min-width: 100px !important;
        max-width: 100px !important;
        text-align: center !important;
    }

    [data-repeater-create] span {
        white-space: nowrap !important;
    }

    .type-btn {
        max-width: 150px;
        height: 70px;
        margin-right: 10px;
        margin-bottom: 5px !important;
    }

    .type-btn:hover {}

    .bootstrap-select {
        min-width: 240px;
    }

  

    input.only-month-year-picker {
        min-width: 100px;
    }

    input.only-greater-than-or-equal-zero-allowed {
        min-width: 120px;
    }

    input.only-percentage-allowed {
        min-width: 80px;
    }

    i {
        text-align: left
    }

    .kt-portlet .kt-portlet__body {
        overflow-x: scroll;
    }

    .repeat-to-r {
        flex-basis: 100%;
        cursor: pointer
    }

    .icon-for-selector {
        background-color: white;
        color: #0742A8;
        font-size: 1.5rem;
        cursor: pointer;
        margin-left: 3px;
        transition: all 0.5s;
    }

    .icon-for-selector:hover {
        transform: scale(1.2);

    }

    .filter-option {
        text-align: center !important;
    }


    td input,
    td select,
    .filter-option {
        border: 1px solid #CCE2FD !important;
        margin-left: auto;
        margin-right: auto;
        color: black;
        font-weight: 400;
    }

    th {
        border-bottom: 1px solid #CCE2FD !important;
    }

    tr:last-of-type {}

    .table tbody+tbody {
        border-top: 1px solid #CCE2FD;
    }

</style>
<x-main-form-title :id="'main-form-title'" :class="''">{{ $pageTitle  }}</x-main-form-title>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="kt-portlet">


            <div class="kt-portlet__body" style="overflow:hidden !important;">
			
			<x-section-title :title="__('Item and Subitems Table')"></x-section-title>
    <hr style="width:100%;">
			

                <form method="post" action="{{ route('add.count.dynamic.items',['company'=>$company->id]) }}" class="row align-items-center">
                    @csrf
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="label">{{ __('Item Name') }}</label>
                            <input value="{{ __('FF&E') }}" type="text" name="main_field_name" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="form-group text-center">
                            <label class="label">{{ __('Has Subitems') }}</label>
                            <input {{ isset($subItemsCount['has_sub_items']) && $subItemsCount['has_sub_items'] ? 'checked' :'' }} type="checkbox" value="1" name="has_sub_items" style="height:20px;width:20px;margin:auto" class="form-control js-toggle-how-many">
                        </div>
                    </div>

                    <div class="col-md-1 d-none how-many-div-js">
                        <div class="form-group">
                            <label class="label">{{ __('How Many ? ') }}</label>
                            <input type="numeric" value="{{ $subItemsCount['how_many_items'] ?? 0 }}" name="how_many" class="form-control">
                        </div>
                    </div>
					
						<div class="row col-12">
					  @for($i = 0 ; $i < $howManyItems ; $i++) 
						<div class="col-md-3">
                        <div class="form-group">
                            <label class="label">{{ __('Subitem ['. ($i+1) .'] Name') }}</label>
                            <input value="{{ $subItemsNames[$i]??''  }}" type="text" name="sub_items_names[]" class="form-control">
                        </div>
          			  </div>
            @endfor
						</div>
                    <div class="col-md-3" style="align-items:flex-end;display:flex;margin-left:auto">
                        <div style="margin-left:auto ">
                            <button type="submit" class="btn active-style">{{__('Save')}}</button>
                        </div>
                    </div>


                </form>

            </div>
        </div>



@if($subItemsCount['how_many_items']??0 )
    <form id="form-id" class="kt-form kt-form--label-right align-items-center" method="POST" enctype="multipart/form-data" action="#">
        @csrf
        <input type="hidden" name="model_id" value="{{ $model->id ?? 0  }}">
        {{-- <input type="hidden" name="model_name" value="IncomeStatement"> --}}
        <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
        <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
        <div class="kt-portlet">


            <div class="kt-portlet__body">
			
			<x-section-title :title="__('Item and Subitems Table')"></x-section-title>

                <div class="form-group row justify-content-center">
                    @php
                    $index = 0 ;
                    @endphp



                    {{-- start of fixed monthly repeating amount --}}
                    @php
                    $tableId = 'items';
                    $repeaterId = 'repeater_id';

                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :repeater-with-select2="true" :parentClass="'js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            
							 {{-- <x-tables.repeater-table-th
							 :helperTitle="'Please select {title} or click (+) to add a new one' "
							    class="col-md-2" :title="$subItemsCount['main_field_name'] ?? '' "></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th
							 :helperTitle="__('Please write an Abbreviation')"
							
							  class="col-md-2" :title="__('Abb.')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th 
							
							 :helperTitle="__('Please write a Code')"
							
							  class="col-md-2" :title="__('Code')"></x-tables.repeater-table-th> --}}
							
								@foreach($subItemsNames as $index=>$subItemName)
								
								
									 <x-tables.repeater-table-th
							 :helperTitle="'Please select {title} or click (+) to add a new one' "
									 
									   class="col-md-2" :title="ucfirst($subItemName) "></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th 
							 :helperTitle="__('Please write an Abbreviation')"
							
							 class="col-md-2" :title="__('Abb.')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th
							
							 :helperTitle="__('Please write a Code')"
							
							  class="col-md-2" :title="__('Code')"></x-tables.repeater-table-th>
								@endforeach 

							{{--
							
                            <tr>
                                <x-tables.repeater-table-th class="col-md-1" :title="__('Action')"></x-tables.repeater-table-th>

                                <x-tables.repeater-table-th class="col-md-1" :title="$subItemsCount['main_field_name']??'' "></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-1" :title="__('Abbreviation')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-2" :title="'Code'"></x-tables.repeater-table-th>


                                <x-tables.repeater-table-th class="col-md-1" :title="$subItemsCount['main_field_name']??'' "></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-1" :title="__('Abbreviation')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-2" :title="'Code'"></x-tables.repeater-table-th>


                                <x-tables.repeater-table-th class="col-md-1" :title="$subItemsCount['main_field_name']??''"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-1" :title="__('Abbreviation')"></x-tables.repeater-table-th>
                                <x-tables.repeater-table-th class="col-md-2" :title="'Code'"></x-tables.repeater-table-th>

                            </tr> --}}
                        </x-slot>

                        <x-slot name="trs">
                            @php
                            $rows = [-1] ;
                            @endphp
                            {{-- @foreach( count($rows) ? $rows : [-1] as $subModel) --}}
                            @php
                            // if( !($subModel instanceof \App\Models\Expense) ){
                            // unset($subModel);
                            // }

                            @endphp
                            <tr @if($isRepeater) data-repeater-item @endif>
                                <td class="text-center align-middle">
									<label class="visibility-hidden">delete</label>
                                    <div class="">
                                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                                        </i>
                                    </div>
                                </td>

								
								 
							
									@foreach($subItemsNames as $index=>$subItemName)
									
								
									<td>
									@if($loop->last)
 										<label class="form-label font-weight-bold visibility-hidden">1</label>
                                        <input data-name-id="{{ $subItemName }}" type="text" value="" class="form-control bootstrap-select" @if($isRepeater) name="{{ 'name' }}" @else name="{{ $tableId }}[0][{{ 'name' }}]" @endif>
                                    									
									@else
                                    <x-form.select-with-modal-abb data-name-id="{{ $subItemName }}" :label-class="'visibility-hidden'" :add-new-modal="true" class="text-center  repeater-select" :add-new-modal-modal-type="''" :add-new-modal-modal-name="$subItemName" :add-new-modal-modal-title="$subItemName " :options="$index == 0 ?  getTestFfeArray() : getTestCategory()" :add-new="false" :label="$subItemName " class="select2-select main_item_js  " data-filter-type="{{ __('create') }}" :all="false" name="revenue_business_line_id" id="{{'revenue_business_line_id' }}" :selected-value="1"></x-form.select-with-modal-abb>
									@endif
                                    </td>

                                    <td>
                                        <label class="form-label font-weight-bold visibility-hidden">Abb</label>
                                        <input data-name-id="{{ $subItemName }}" type="text" value="" class="form-control abb-js max-w-abb" @if($isRepeater) name="{{ 'name' }}" @else name="{{ $tableId }}[0][{{ 'name' }}]" @endif>
                                    </td>
                                    <td>
                                        <label class="form-label font-weight-bold visibility-hidden">Code</label>
                                        <input data-name-id="{{ $subItemName }}" type="text" value="" class="form-control code-js max-w-code" @if($isRepeater) name="{{ 'code' }}" @else name="{{ $tableId }}[0][{{ 'code' }}]" @endif>
                                    </td>
									@endforeach 
									
									



                            </tr>
                            {{-- @endforeach --}}

                        </x-slot>




                    </x-tables.repeater-table>
                    {{-- end of fixed monthly repeating amount --}}















































































                </div>


            </div>
        </div>
        <x-save />




        <!--end::Form-->

        <!--end::Portlet-->
</div>


</div>

</div>




</div>









</div>
</div>
</form>
@endif
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

            let form = document.getElementById('form-id');
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);

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
                        , title: res.message
                        , timer: 1500
                        , showConfirmButton: false

                    }).then(function() {
                        window.location.href = res.redirectTo;
                    });





                }
                , complete: function() {
                    $('#enter-name').modal('hide');
                    $('#name-for-calculator').val('');

                }
                , error: function(res) {
                    $('.save-form').prop('disabled', false);
                    $('.submit-form-btn-new').prop('disabled', false)
                    Swal.fire({
                        icon: 'error'
                        , title: res.responseJSON.message
                    , });
                }
            });
        }
    })

</script>
<script>
    $(document).find('.datepicker-input').datepicker({
        dateFormat: 'mm-dd-yy'
        , autoclose: true
    })

</script>
<script>
    function reinitalizeMonthYearInput(dateInput) {
        var currentDate = $(dateInput).val();
        var startDate = "{{ isset($studyStartDate) && $studyStartDate ? $studyStartDate : -1 }}";
        startDate = startDate == '-1' ? '' : startDate;
        var endDate = "{{ isset($studyEndDate) && $studyEndDate? $studyEndDate : -1 }}";
        endDate = endDate == '-1' ? '' : endDate;

        $(dateInput).datepicker({
                viewMode: "year"
                , minViewMode: "year"
                , todayHighlight: false
                , clearBtn: true,


                autoclose: true
                , format: "mm/01/yyyy"
            , })
            .datepicker('setDate', new Date(currentDate))
            .datepicker('setStartDate', new Date(startDate))
            .datepicker('setEndDate', new Date(endDate))


    }

    $(function() {

        $('.only-month-year-picker').each(function(index, dateInput) {
            reinitalizeMonthYearInput(dateInput)
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
        $('.js-type-btn').removeClass('active');
        $(this).addClass('active');
        $('.js-parent-to-table').show();
        let tableId = '.' + $(this).attr('data-value');
        $(tableId).closest('.js-parent-to-table').show();

    })
    $('.js-parent-to-table').show();
    $(function() {
        $('#expense_type').trigger('change')
        $('.js-type-btn.active').trigger('click')
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
    let oldValForInputNumber = 0;
    $('input:not([placeholder]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([readonly]):not(.exclude-text):not(.date-input)').on('focus', function() {
        oldValForInputNumber = $(this).val();
        if (isNumber(oldValForInputNumber)) {
            $(this).val('')

        }
    })
    $('input:not([placeholder]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([readonly]):not(.exclude-text):not(.date-input)').on('blur', function() {

        if ($(this).val() == '') {
            if (isNumber(oldValForInputNumber)) {
                $(this).val(oldValForInputNumber)
            }
        }
    })

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
    const handlePaymentTermModal = function() {
        const parentTermsType = $(this).closest('select').val();
        const tableId = $(this).closest('table').attr('id');
        if (parentTermsType == 'customize') {
            $(this).closest('tr').find('#' + tableId + 'test-modal-id').modal('show')
        }



    };
    $(document).on('change', 'select.payment_terms', handlePaymentTermModal)
    $('select.js-due_in_days').change(function() {
        // const selectValue = $(this).val();
        // $(this).find('option').prop('selected',false)
        // $(this).find('option[value="'+selectValue+'"]').prop('selected',true);
        // reinitializeSelect2();
    })

    //$(document).on('click','option',handlePaymentTermModal)
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
@if(session()->has('success'))

<script>
    Swal.fire({
        text: "{{ session()->get('success') }}"
        , icon: 'success'

    });

</script>


@endif

<script>
    $(document).on('click', '.trigger-add-new-modal', function() {
        var additionalName = '';
        if ($(this).attr('data-previous-must-be-opened')) {
            const previosSelectorQuery = $(this).attr('data-previous-select-selector');
            const previousSelectorValue = $(previosSelectorQuery).val()
            const previousSelectorTitle = $(this).attr('data-previous-select-title');
            if (!previousSelectorValue) {
                Swal.fire({
                    text: "{{ __('Please Select') }}" + ' ' + previousSelectorTitle
                    , icon: 'warning'
                })
                return;
            }
            const previousSelectorVal = $(previosSelectorQuery).val();
            const previousSelectorHtml = $(previosSelectorQuery).find('option[value="' + previousSelectorVal + '"]').html();
            additionalName = "{{' '. __('For')  }}  [" + previousSelectorHtml + ' ]'
        }
        const parent = $(this).closest('label').parent();
        parent.find('select');
        const type = $(this).attr('data-modal-title')
        const name = $(this).attr('data-modal-name')
        $('.modal-title-add-new-modal-' + name).html("{{ __('Add New ') }}" + type + additionalName);
        parent.find('.modal').modal('show')
    })
    $(document).on('click', '.store-new-add-modal', function() {
        const that = $(this);
        $(this).attr('disabled', true);
        const modalName = $(this).attr('data-modal-name');
        const modalType = $(this).attr('data-modal-type');
        const modal = $(this).closest('.modal');
        const value = modal.find('input.name-class-js').val();
        const previousSelectorSelector = $(this).attr('data-previous-select-selector');
        const previousSelectorValue = previousSelectorSelector ? $(previousSelectorSelector).val() : null;
        const previousSelectorNameInDb = $(this).attr('data-previous-select-name-in-db');

        $.ajax({
            url: "{{ route('admin.store.new.modal.dynamic',['company'=>$company->id]) }}"
            , data: {
                "_token": "{{ csrf_token() }}"
                , "modalName": modalName
                , "modalType": modalType
                , "value": value
                , "previousSelectorNameInDb": previousSelectorNameInDb
                , "previousSelectorValue": previousSelectorValue
            }
            , type: "POST"
            , success: function(response) {
                $(that).attr('disabled', false);
                modal.find('input').val('');
                $('.modal').modal('hide')
                if (response.status) {
                    const allSelect = $('select[data-modal-name="' + modalName + '"][data-modal-type="' + modalType + '"]');
                    const allSelectLength = allSelect.length;
                    allSelect.each(function(index, select) {
                        var isSelected = '';
                        if (index == (allSelectLength - 1)) {
                            isSelected = 'selected';
                        }
                        $(select).append(`<option ` + isSelected + ` value="` + response.id + `">` + response.value + `</option>`).selectpicker('refresh').trigger('change')
                    })

                }
            }
            , error: function(response) {}
        });
    })

</script>
<script>
$(document).on('change','.js-toggle-how-many',function(){
	if(this.checked){
		$('.how-many-div-js').removeClass('d-none')
	}else{
		$('.how-many-div-js').addClass('d-none')
	}
})
$(document).on('change','select.main_item_js',function(){
	const selectedOption = $(this).find('option:selected');
	const nameId = $(this).attr('data-name-id');
	const abb = $(selectedOption).attr('data-abb');
	const code = $(selectedOption).attr('data-code');
	
	$(this).closest('tr').find('.code-js[data-name-id="'+nameId+'"]').val(code);
	$(this).closest('tr').find('.abb-js[data-name-id="'+nameId+'"]').val(abb);
	
})
$(function(){
	$('.js-toggle-how-many').trigger('change');
	$('select').trigger('change')
	
})
</script>
@endpush
