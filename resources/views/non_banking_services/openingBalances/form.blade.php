@extends('layouts.dashboard')

@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/leasing-revenue-stream-breakdown.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    .positions_repeater {
        max-width: 50% !important;
    }

</style>
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

@endsection
@section('content')


                <div class="row opening-balances-row">

                    <div class="form-group row" style="flex:1;">
                        <div class="col-md-12 mt-3" data-repeater-row=".opening-balances-row">

                            <form class="kt-form kt-form--label-right" action="{{ route('store.opening.balances.for.non.banking',['company'=>$company->id , 'study'=>$study->id]) }}" method="POST">
                                {{ csrf_field() }}
							@include('non_banking_services.openingBalances._content',$study->getOpeningBalancesViewVars())

                                <x-save-without-ajax-btn :submitByAjax=false />
                            </form>


                        </div>


                    </div>

                </div>
      

@endsection
@push('js')
<x-js.commons></x-js.commons>


<script>
    $(document).on('click', '.save-form', function(e) {
        e.preventDefault(); {

            const hasSalesChannel = $('#add-sales-channels-share-discount-id:checked').length

            let canSubmitForm = true;
            let errorMessage = '';
            let messageTitle = 'Oops...';



            if (!canSubmitForm) {
                Swal.fire({
                    icon: "warning"
                    , title: messageTitle
                    , text: errorMessage
                , })

                return;
            }

            let formId = $(this).closest('form').attr('id')

            let form = document.getElementById(formId);
            var formData = new FormData(form);
            formData.append('submitBtnType', formId)

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
                        , title: res.message,

                    });

                    window.location.href = res.redirectTo;




                }
                , complete: function() {
                    $('#enter-name').modal('hide');
                    $('#name-for-calculator').val('');

                }
                , error: function(res) {

                    let title = '{{ __("Something Went Wrong") }}';
                    if (res.responseJSON && res.responseJSON.message) {
                        title = res.responseJSON.message;
                    }
                    $('.submit-form-btn,.save-form').prop('disabled', false)
                    let message = null;
                    if (res.responseJSON && res.responseJSON.errors) {
                        message = res.responseJSON.errors[Object.keys(res.responseJSON.errors)[0]][0]
                    }
                    Swal.fire({
                        icon: 'error'
                        , title: title
                        , text: message

                    })

                }
            });
        }
    })

</script>



<script>
   



   



</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script src="/custom/js/non-banking-services/revenue-stream-breakdown.js"></script>





{{-- <script></script> --}}
@endpush
