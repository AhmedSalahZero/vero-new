@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Expense;
use App\Models\NonBankingService\FixedAssetName;
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
<form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{ $storeRoute }}">
    <div class="row">
        <div class="col-md-12">



            <input type="hidden" name="fixed_asset_type" value="{{ $fixedAssetType }}">
            @php
            $tableId = 'fixedAssets';
            $cardId = $tableId;
            $repeaterId = $tableId.'_repeater';
            @endphp
            @include('non_banking_services.new-branch-fixed-assets._repeater')
            <!--end::Form-->

            <!--end::Portlet-->

            <style>
                .max-w-btn {
                    max-width: 125px !important;
                    min-width: 125px !important;
                }

            </style>

            <div id="ffe-funding" class="kt-portlet " style="margin-bottom:5px;">


                <div class="kt-portlet__body">
                    <div class="row btn-for-submit--js ">
                        <div class="col-lg-6">

                        </div>
                        <div class="col-lg-6 kt-align-right">
                            <input data-save-and-add-new-department="0" type="submit" class="btn max-w-btn active-style save-form" value="{{ isset($text) ? $text : __('Save Changes') }}">

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>



</form>
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
<script src="/custom/js/non-banking-services/revenue-stream-breakdown.js"></script>
<script>


</script>
@endpush
