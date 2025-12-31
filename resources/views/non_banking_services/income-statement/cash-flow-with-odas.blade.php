@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
@endphp
@section('css')
@php
$months = $study->getMicrofinanceMonths() ;
@endphp
{{-- {{ dd('e') }} --}}
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
<div id="study-duration" data-duration="{{ $study->duration_in_years }}"></div>
<div class="kt-portlet kt-portlet--tabs">
<form action="{{ route('save.manual.equity.injection',['company'=>$company->id,'study'=>$study->id]) }}" method="post">
@csrf
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
	
            @php
            $currentType = 'study' ;
            @endphp
			
	{{-- {{ dd($tableDataFormatteds) }} --}}
    @foreach($tableDataFormatteds as $title=> $tableDataFormatted)
		@if($title != $odasTitleStatement ||  ($title == $odasTitleStatement && $hasMicrofinanceWithOdas)  )
    <div class="kt-portlet__body">
        <div class="tab-content  kt-margin-t-20">
            <div class="row">

                <div class="col-md-10">
                    <div class="d-flex align-items-center ">
                        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                            {{ $title }}
                        </h3>
                    </div>
                </div>
                {{-- <div class="col-md-2 text-right">
                            <x-show-hide-btn :query="'.new-portfolio-funding'"></x-show-hide-btn>
                        </div> --}}
            </div>


		
            <!--Begin:: Tab Content-->
            @include('non_banking_services.income-statement._odas')
			
			












            <!--End:: Tab Content-->



            <!--End:: Tab Content-->
        </div>
    </div>
	@endif
	
    @endforeach


    <div class="kt-portlet">
        <div class="kt-portlet__body">
            <div class="row">

                <div class="col-md-10">
                    <div class="d-flex align-items-center ">
                        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                            {{ __('Manual Funding Structure') }}
                        </h3>
                    </div>
                </div>

            </div>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
			
            <div class="row new-portfolio-funding">
                @php
                $rowIndex = 0;
                @endphp
				
                <x-tables.repeater-table :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                    <x-slot name="ths">
                        <x-tables.repeater-table-th :subParentClass="'plus-max-width-class fixed-column'" class="  header-border-down plus-max-width-class" :title="__('+/-')"></x-tables.repeater-table-th>
                        <x-tables.repeater-table-th class=" category-selector-class header-border-down " :title="__('Item')"></x-tables.repeater-table-th>

                        @for($i = 0 ; $i<= $months ; $i++) @php $monthName=formatDateForView($studyDates[$i]); @endphp <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="$monthName">
                            </x-tables.repeater-table-th>
                            @endfor
                            <x-tables.repeater-table-th class=" interval-class header-border-down " :title="__('Total')"></x-tables.repeater-table-th>
                    </x-slot>
                    <x-slot name="trs">





                        <tr data-repeat-formatting-decimals="0" data-repeater-style total-row-tr data-row-total>

                            <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">


                            <td class="fixed-column">
							    <div class="col-md-12  text-left">
                                        <div class="mt-2 d-inline-block">
                                            <div class="kt-radio-inline">
                                                <label class="mr-3">

                                                </label>
                                                <label class="kt-radio kt-radio--success text-red font-size-14px font-weight-bold mb-0">

                                                    <input type="checkbox" value="1" name="has_manual_equity_injection" @if(  $cashflowStatementReport->hasManualEquityInjection()) checked @endisset
                                                    > {{ __('Apply') }}
                                                    <span></span>
                                                </label>


                                            </div>
                                        </div>
                                    </div>
									
						
                                <a href="#" class="btn 
								
									visibility-hidden
								
									 
									 btn-1-bg btn-sm btn-brand add-btn-class  text-center add-btn-js">
                                    <i class="fas fa-angle-double-down expand-icon   exclude-icon"></i>
                                </a>
                            </td>


                            <td>

                                <input readonly value="{{ __('Equity Injection Value') }}" class="form-control name-max-width-class text-left mt-2" type="text">

                            </td>
                            @php
                            $columnIndex = 0 ;
                            @endphp
                            @for($i = 0 ; $i<= $months ; $i++) <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <x-repeat-right-dot-inputs :readonly="false" :numberFormatDecimals="0" :currentVal="$cashflowStatementReport->getManualEquityInjectionAtMonthIndex($i)" :classes="'only-greater-than-or-equal-zero-allowed '" :formatted-input-classes="''" :is-percentage="false" :name="'manual_equity_injection['.$i.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                </div>
                                </td>
                                @php
                                $columnIndex++;
                                @endphp
                                @endfor

                                <td>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <input type="text" class="form-control expandable-amount-input sum-total-row sum-percentage-css" disabled value="0">
                                    </div>
                                </td>



                        </tr>
                        @foreach($study->getRevenuesTypesWithTitles() as $revenueTypeId => $revenueOptionArr)
                        @php
                        $leasingEclAndNewPortfolioFundingRate = $leasingEclAndNewPortfolioFundingRates[$revenueTypeId]??null;
                        if(is_null($leasingEclAndNewPortfolioFundingRate)){
                        continue;
                        }
						$routeName = $revenueOptionArr['routeName'];
                        @endphp

                        <tr data-repeat-formatting-decimals="2" data-repeater-style>
						
						  <td class="fixed-column">

                                <a href="#" class="btn 
									visibility-hidden
									 btn-1-bg btn-sm btn-brand add-btn-class  text-center add-btn-js">
                                    <i class="fas fa-angle-double-down expand-icon   exclude-icon"></i>
                                </a>
                            </td>
							
							
                            <td>
                                <input disabled value="{{'[ '.$revenueOptionArr['title'].' ] '. __('New Loans Funding Rate (%)') }}" class="form-control  text-left" type="text">
                            </td>
							@php
								
							@endphp
							{{-- <td> --}}
							  <td>
							      <a href="{{ route($routeName,['company'=>$company->id,'study'=>$study->id,'redirect-to-cashflow'=>1]) }}#loan-portfolio" class="btn btn-lg-width btn-2-bg btn-sm btn-brand btn-pill">{{ __('Adjust') }}</a>
                                </td>
							



                        </tr>

                        @endforeach



                    </x-slot>




                </x-tables.repeater-table>
				
                {{-- end of fixed monthly repeating amount --}}


            </div>
			{{-- @endif --}}

        </div>
      
		
	    @if(isset($nextButton))
        <div class="text-right mt-4 cash-flow-btn mr-2">
		   <button type="submit" name="recalculate-cashflow" href="{{ $nextButton['link'] }}" class="btn text-white bg-danger ">{{ __('Recalculate Cashflow') }}</button>
            <a href="{{ $nextButton['link'] }}" class="btn btn-primary ">{{ $nextButton['title'] }}</a>
        </div>
        @endif
		</form>
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

    $(function() {
        //	$('[data-group-index]').trigger('change');
    })

</script>
@endpush
