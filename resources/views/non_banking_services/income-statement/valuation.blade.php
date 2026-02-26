@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
@endphp
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
<style>
    input.form-control[type="text"][readonly] {
        background-color: white !important;
        color: black !important;
        font-weight: 400 !important;
    }

    .fixed-column-table {
        width: 100%;
        overflow-x: auto;
        /* Enable horizontal scrolling */
        border-collapse: collapse;
    }

    .fixed-column {
        position: sticky;
        left: -15px;
        background: #f8f8f8;
        /* Optional: distinguish the fixed column */
        z-index: 1;
        /* Ensure it stays above other cells */
    }

    html body input.custom-input-string-width,
    .name-max-width-class {
        width: 400px !important;
        min-width: 400px !important;
        max-width: 400px !important;
    }

</style>
@endsection
@section('sub-header')
{{ $title }}
@endsection
@section('content')

<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == Study::STUDY ?'active':'' }}" data-toggle="tab" href="#{{Study::STUDY  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ $tableTitle }}
                    </a>
                </li>
            </ul>



        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = 'study' ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
                    @include('non_banking_services.income-statement._valuation',['formattedDcfMethod'=>$formattedDcfMethod])
                </div>
            </div>


            @if(isset($nextButton))
            <div class="text-right mt-4 cash-flow-btn">
                <a href="{{ $nextButton['link'] }}" class="btn btn-primary ">{{ $nextButton['title'] }}</a>
            </div>
            @endif



            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>

</div>

<div class="kt-portlet kt-portlet--tabs">
    <div class="kt-portlet__head">
        <div class="kt-portlet__head-toolbar justify-content-between flex-grow-1">
            <ul class="nav nav-tabs nav-tabs-space-lg nav-tabs-line nav-tabs-bold nav-tabs-line-3x nav-tabs-line-brand" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ !Request('active') || Request('active') == Study::STUDY ?'active':'' }}" data-toggle="tab" href="#{{Study::STUDY  }}" role="tab">
                        <i class="fa fa-money-check-alt"></i> {{ __('Multipliers Valuations') }}
                    </a>
                </li>
            </ul>



        </div>
    </div>
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">

            @php
            $currentType = 'study' ;
            @endphp
            <!--Begin:: Tab Content-->
            <div class="tab-pane {{  !Request('active') || Request('active') == $currentType ?'active':'' }}" id="{{ $currentType }}" role="tabpanel">
                <div class="kt-portlet kt-portlet--mobile">
                    @include('non_banking_services.income-statement._multipliers',['formattedDcfMethod'=>$formattedDcfMethod])
                </div>
            </div>


            @if(isset($nextButton))
            <div class="text-right mt-4 cash-flow-btn">
                <a href="{{ $nextButton['link'] }}" class="btn btn-primary ">{{ $nextButton['title'] }}</a>
            </div>
            @endif



            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>

</div>


@endsection
@section('js')

{{-- <script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script> --}}

<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript">
</script>


<script>
    $(document).on('click', '.js-close-modal', function() {
        $(this).closest('.modal').modal('hide');
    })

</script>

@endsection
@push('js')
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>
<script>
    $(function() {
        const studyDuration = $('#study-duration').attr('data-duration');
        if (studyDuration > 1) {
            $('.collapse-before-me').trigger('click')
        }
    })

    $(function() {})

</script>
@endpush
