@extends('layouts.dashboard')
@section('css')
<style>
    table {
        white-space: nowrap;

    }

    input.form-control[readonly] {
        background-color: #CCE2FD !important;
        font-weight: bold !important;
    }

</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />

<style>
    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    thead * {
        text-align: center !important;
    }

</style>
{{-- <link href="{{ url('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> --}}
@endsection
@section('sub-header')
Sales Section
@endsection
@section('content')
@php
$user = auth()->user();
@endphp
<div class="row">
    <div class="col-lg-12">
        @if (session('warning'))
        <div class="alert alert-warning">
            <ul>
                <li>{{ session('warning') }}</li>
            </ul>
        </div>
        @endif
    </div>
</div>

<form action="{{ route('admin.store-cash-and-banks',['company'=>$company->id]) }}" method="post">
<input type="hidden" value="{{ $cashFlowStatementId }}" name="cash_flow_statement_id">
<input type="hidden" value="{{ $subItemType }}" name="subItemType">
 @foreach($datesFormatted as $dateAsIndex => $dateAsString)
					<input type="hidden" name="dates[]" value="{{ $dateAsIndex }}">
					@endforeach
@csrf
<div class="kt-portlet">
    <div class="kt-portlet__body d-flex " style="flex-direction:row !important;flex-wrap:nowrap !important;">
        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5 col-3" style="white-space:nowrap"> {{ __('Cash & Banks Beginning Balance') }} </h3>
        <div class="kt-input-icon">
            <div class="input-group">
                <input type="text" class="form-control col-3 only-greater-than-or-equal-zero-allowed date-value-element" value="{{ number_format(isset($model) ? $model->getCashAndBanksBeginningBalance($dateAsIndex) : old('cash_and_banks_beginning_balance',0) ) }}" >
                <input class="date-value-element-hidden" type="hidden" name="cash_and_banks_beginning_balance" value="{{ (isset($model) ? $model->getCashAndBanksBeginningBalance() : old('cash_and_banks_beginning_balance',0)) }}">
            </div>
        </div>
    </div>
</div>
@php
	$index=0;
@endphp
@foreach(['receivable'=>__('Receivables & Debtors Opening Balances - [Cash In]'),'payment'=>__('Payments & Creditors Opening Balances - [Cash Out]')] as $namePrefix=>$title)
<div class="kt-portlet">
    <div class="kt-portlet__body">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group row " style="flex:1">
                        <div class="col-md-4 text-left">

                            {{-- <label class="form-label font-weight-bold ">{{ __('Cash & Banks Begining Balance') }} </label> --}}

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="d-flex align-items-center ">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ $title }} </h3>
                </div>
            </div>

        </div>
        <div class="row">
            <hr style="flex:1;background-color:lightgray">
        </div>
        <div class="row">

            <div class="form-group row" style="flex:1;">
                <div class="col-md-12 mt-3">



                    <div class="" style="width:100%;overflow:scroll">

                        <div id="m_repeater_{{ $index+4 }}" class="cash-and-banks-repeater">
                            <div class="form-group  m-form__group row  ">
                                <div data-repeater-list="opening_{{ $namePrefix }}" class="col-lg-12">
                                    @if(isset($receivables_and_payments) && ($namePrefix == 'receivable' && $hasReceivables || $namePrefix == 'payment' && $hasPayments) )
                                    @foreach($receivables_and_payments as $receivable_and_payment)
									@if($receivable_and_payment->getType() == $namePrefix )
										@include('admin.cash-flow-statement.cash-opening-balance.repeater' , [
										'receivable_and_payment'=>$receivable_and_payment,
										'namePrefix'=>$namePrefix
										])
									@endif 
									@php
										unset($receivable_and_payment);
									@endphp
                                    @endforeach
                                    @else
                                    @include('admin.cash-flow-statement.cash-opening-balance.repeater' , [
										'namePrefix'=>$namePrefix
									])

                                    @endif






                                </div>
                            </div>
                            <div class="m-form__group form-group row">

                                <div class="col-lg-6">
                                    <div data-repeater-create="" class="btn btn btn-sm btn-success m-btn m-btn--icon m-btn--pill m-btn--wide {{__('right')}}" id="add-row">
                                        <span>
                                            <i class="fa fa-plus"> </i>
                                            <span>
                                                {{ __('Add') }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>



                </div>

            </div>


        </div>


    </div>
</div>
@php
	$index++;
@endphp
@endforeach

<div class="kt-portlet">
    <div class="kt-portlet__body">
        <div class="text-right">
            <a href="{{ route('admin.create.cash.flow.statement.forecast.report',['cashFlowStatement'=>$cashFlowStatementId,'company'=>$company->id]) }}" class="btn btn-primary mr-2">{{ __('Skip And Go To Cash Flow') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save And Go To Cash Flow') }}</button>
        </div>
    </div>
</div>
</form>


@endsection

@section('js')
@include('js_datatable')
{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/lib.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/jquery.input.js') }}" type="text/javascript">
</script>
<script src="{{ url('assets/vendors/general/jquery.repeater/src/repeater.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/form-repeater.js') }}" type="text/javascript"></script>
{{-- <script src="{{ url('assets/vendors/general/select2/dist/js/select2.full.js') }}" type="text/javascript"></script> --}}
{{-- <script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/select2.js') }}" type="text/javascript"></script> --}}
<script src="{{asset('assets/form-repeater.js')}}" type="text/javascript"></script>
<script>


 
	//$('#add-row').click(function(){
	//	$('input').trigger('change')
	//})
</script>
@endsection

@push('js_end')

<script>
	let oldValForInputNumber = 0;
        $('input:not([placeholder]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([readonly]):not(.exclude-text):not(.date-input)').on('focus', function() {
            oldValForInputNumber = $(this).val();
            $(this).val('')
        })
        $('input:not([placeholder]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([readonly]):not(.exclude-text):not(.date-input)').on('blur', function() {

            if ($(this).val() == '') {
                $(this).val(oldValForInputNumber)
            }
        })

        $(document).on('change', 'input:not([placeholder])[type="number"],input:not([placeholder])[type="password"],input:not([placeholder])[type="text"],input:not([placeholder])[type="email"],input:not(.exclude-text)', function() {
			if(!$(this).hasClass('exclude-text')){
            let val = $(this).val()
            val = number_unformat(val)
            $(this).parent().find('input[type="hidden"]:not([name="_token"])').val(val)
				
			}
        })
	
	</script>
	
<script>
   $(document).on('change', '.date-value-element', function() {
        let total = 0;
        const parent = $(this).closest('.date-element-parent');
        parent.find('.date-value-element-hidden').each(function(index, hiddenInput) {
            var currentValue = $(hiddenInput).val();
            currentValue = currentValue ? currentValue : 0;
            total += parseFloat(currentValue);
        })
        parent.find('.date-element-total-input').val(number_format(total, 0));



    })
    $('.date-value-element:first-of-type').trigger('change')
	
</script>
	
@endpush
