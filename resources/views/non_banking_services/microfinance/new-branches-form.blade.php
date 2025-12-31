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
    .expenses-table {
        min-height: 50vh !important;
    }

</style>
@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')
@php
$months = $study->getMicrofinanceMonths() ;
@endphp
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
            @php

            $tableId = 'newBranchMicrofinanceOpeningProjections';
            $repeaterId = $tableId.'_repeater';
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

                    <x-tables.repeater-table :hideByDefault="false" :font-size-class="'font-14px'" :append-save-or-back-btn="false" :repeater-with-select2="true" :parentClass="'js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
                        <x-slot name="ths">
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('New Branches <br> Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Start <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Operation <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="col-md-1 header-border-down" :title="__('Total <br> Branches')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                        </x-slot>
                        <x-slot name="trs">
                            @php

                            $rows = isset($model) ?$model->newBranchMicrofinanceOpeningProjections : [-1] ;
                            @endphp

                            @foreach( count($rows) ? $rows : [-1] as $subModel)
                            @php
                            if( !($subModel instanceof \App\Models\NonBankingService\NewBranchMicrofinanceOpeningProjection) ){
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
                                    <input value="{{ (isset($subModel) ? $subModel->getCounts() : 0) }}" class="form-control recalculate-total-branches text-center only-greater-than-or-equal-zero-allowed" type="text">
                                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getCounts() : 0) }}" @if($isRepeater) name="counts" @else name="{{ $tableId }}[0][counts]" @endif>

                                </td>
                                <td>
                                    @include('components.calendar-month-year',[
                                    'name'=>'start_date',
                                    'value'=>isset($subModel) ? $subModel->getStartDateYearAndMonth() : $study->getOperationStartDateYearAndMonth()
                                    ])


                                </td>

                                <td>
                                    @include('components.calendar-month-year',[
                                    'name'=>'operation_date',
                                    'value'=>isset($subModel) ? $subModel->getOperationDateYearAndMonth() : $study->getOperationStartDateYearAndMonth()
                                    ])


                                </td>

                                <td>
                                    <input readonly value="{{ (isset($subModel) ? number_format($subModel->getTotalBranches(),0) : 0) }}" class="form-control total-branches-text text-center only-greater-than-or-equal-zero-allowed" type="text">
                                    <input class="total-branches-hidden" type="hidden" value="{{ (isset($subModel) ? $subModel->getTotalBranches() : 0) }}" @if($isRepeater) name="total_branches" @else name="{{ $tableId }}[0][total_branches]" @endif>

                                </td>

                            </tr>
                            @endforeach

                        </x-slot>




                    </x-tables.repeater-table>

                </div>
            </div>



            {{-- start of New Branches Product Mix  --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('New Branches Product Mix') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Name') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Tenor <br> (Months)') !!} </th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Early Payment <br> Installments Count') !!}</th>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Avg <br> Amount') !!}</th>
                                        @if(!$model->durationIsLessThanOneOrEqualYear())
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Annual <br> Increase %') !!}</th>
                                        @endif
                                        <th class="min-w-90 form-label font-weight-bold text-center align-middle   header-border-down">{!! __('Funded <br> By') !!}</th>
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Product <br> Mix %') !!}</th>
                                        @endforeach

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $currentTotals=[];
                                    @endphp

                                    @foreach($products as $product)
                                    @php
                                    $subModel = $model->microfinanceProductSalesProjects->where('microfinance_product_id',$product->id)->where('type',$type)->first();
                                    @endphp
                                    <input type="hidden" name="microfinanceProductSalesProjects[{{ $product->id }}][id]" value="{{ $subModel  ? $subModel->id : 0 }}">
                                    <input type="hidden" name="microfinanceProductSalesProjects[{{ $product->id }}][type]" value="{{ $branchPlanningBaseType }}">
                                    <input type="hidden" name="microfinanceProductSalesProjects[{{ $product->id }}][microfinance_product_id]" value="{{ $product->id }}">
                                    <input type="hidden" name="microfinanceProductSalesProjects[{{ $product->id }}][company_id]" value="{{ $company->id }}">

                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>

                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getTenor() : 12;
                                            $tenorClass = 'tenor-class'.$product->id
                                            // $product->name
                                            @endphp

                                            <x-repeat-right-dot-inputs :formattedInputClasses="'min-w-90'" :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,0)" :classes="'only-greater-than-zero-allowed '.$tenorClass" :is-percentage="false" :name="'microfinanceProductSalesProjects['.$product->id.'][tenor]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>

 <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getEarlyPaymentInstallmentCounts() : 0 ;
                                            @endphp
                                            <x-repeat-right-dot-inputs  :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][early_payment_installment_counts]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>
										
                                        <td>
                                            @php
                                            $currentVal = $subModel ? $subModel->getAvgAmount() : 0 ;
                                            @endphp
                                            <x-repeat-right-dot-inputs :removeThreeDots="true" :removeCurrency="true" :currentVal="$currentVal" :classes="'only-greater-than-zero-allowed'" :is-percentage="false" :name="'microfinanceProductSalesProjects['.$product->id.'][avg_amount]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                        </td>

                                        @if(!$model->durationIsLessThanOneOrEqualYear())
                                        <td>
                                            <div class="d-flex align-items-center increase-rate-parent">
                                                <button class="btn btn-primary btn-md text-nowrap increase-rate-trigger-btn" type="button" data-toggle="modal">{{ __('Increase Rates') }}</button>
                                                <x-modal.increase-rates :name="'microfinanceProductSalesProjects['.$product->id.'][increase_rates]'" :study="$study" :subModel="isset($subModel) ? $subModel : null "></x-modal.increase-rates>
                                            </div>
                                        </td>
                                        @endif

                                        <td>
                                            <x-form.select :required="true" :label="''" :pleaseSelect="false" :selectedValue="isset($subModel) ? $subModel->getFundedBy():0" :options="getMicrofinanceFundingBySelector()" :add-new="false" class="select2-select min-w-120 repeater-select  " :all="false" name="microfinanceProductSalesProjects[{{ $product->id }}][funded_by]"></x-form.select>
                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $subModel ? $subModel->getProductMixAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp

                                            <x-repeat-with-calc :numberFormatDecimals="2" :formattedInputClasses="'calcField '" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="'product-input-class'" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][product_mixes]['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>
                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach




                                    </tr>
                                    @endforeach

                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                        <td>
                                            <div class="">
                                                <input value="{{ __('Total') }}" disabled class="form-control text-left mt-2 " type="text">
                                            </div>
                                        </td>
 @if(!$model->durationIsLessThanOneOrEqualYear())
                                        <td>
                                            <div class="text-center">
                                                -
                                            </div>
                                        </td>
@endif
                                        <td>
                                            <div class="text-center">
                                                -
                                            </div>
                                        </td> <td>
                                            <div class="text-center">
                                                -
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                -
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                -
                                            </div>
                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        @php
                                        $currentLoanTotal = 0 ;
                                        @endphp
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center">


                                                <div class="form-group three-dots-parent">
                                                    <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                        <div class="input-hidden-parent">
                                                            <input readonly class="form-control copy-value-to-his-input-hidden sum-total-row  expandable-percentage-input  repeat-to-right-input-formatted  " type="text" value="{{ number_format($currentTotals[$yearOrMonthAsIndex]??0,1)}}" data-column-index="{{ $columnIndex }}">
                                                        </div>

                                                        <span class="ml-2 currency-class">
                                                            %
                                                        </span>


                                                    </div>

                                                </div>



                                            </div>
                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach





                                    </tr>





                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
            {{-- end of New Branches Product Mix  --}}




            {{-- start of Product Mix Seasonality  --}}
            {{-- @if(!$model->isMonthlyStudy())
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Product Mix Seasonality') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Name') !!}</th>
                                        @for($i = 0 ; $i< 12 ; $i++ ) @php $monthName=\Carbon\Carbon::make('2010-01-01')->addMonth($i)->format('M');
                                            @endphp
                                            <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $monthName .' <br> ' . __('Seasonality %') !!}</th>
                                            @endfor
                                            <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{{ __('Total') }}</th>


                                    </tr>
                                </thead>
                                <tbody>


                                    @foreach($products as $product)
                                    @php
                                    $totalSeasonality=0;
                                    $subModel = $model->microfinanceProductSalesProjects->where('microfinance_product_id',$product->id)->where('type',$type)->first();
                                    @endphp
                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @for($i = 1 ; $i<= 12 ; $i++ ) @php $i=sprintf("%02d", $i); @endphp <td data-product-id="{{ $product->id }}">

                                            @php
                                            $currentVal = $subModel ? $subModel->getSeasonalityOfMonthIndex($i) : 100/12;
                                            $totalSeasonality+=$currentVal;
                                            $monthName = \Carbon\Carbon::make('2010-01-01')->addMonth($i-1)->format('M');
                                            @endphp

                                            <x-repeat-with-calc :showIcon="false" :numberFormatDecimals="2" :formattedInputClasses="'calcField'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,3)" :classes="'seasonality-class'" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][seasonality]['.$i.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>


                                            </td>
                                            @php
                                            $columnIndex++ ;
                                            @endphp

                                            @endfor


                                            <td>
                                                <div class="d-flex align-items-center justify-content-center">


                                                    <div class="form-group three-dots-parent">
                                                        <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                                                            <div class="input-hidden-parent">
                                                                <input readonly class="form-control copy-value-to-his-input-hidden sum-total-column  expandable-percentage-input  repeat-to-right-input-formatted  " type="text" value="{{ number_format($totalSeasonality,1)  }}" data-column-index="{{ $columnIndex }}">
                                                                <span style="visibility:hidden">..</span>

                                                            </div>

                                                            <span class="ml-2 currency-class">
                                                                %
                                                            </span>


                                                        </div>

                                                    </div>



                                                </div>
                                            </td>


                                    </tr>
                                    @endforeach




                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
            @endif --}}
            {{-- end of Product Mix Seasonality  --}}


            {{-- start of Products Mix Pricing (Flat Rates %)  --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                        {{ __('Products Mix Pricing (Flat Rates %)') }}
                    </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! __('Product <br> Name') !!}</th>
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class=" form-label font-weight-bold text-center align-middle  header-border-down">{!! $yearOrMonthFormatted .' <br> ' . __('Flat Rates %') !!}</th>
                                        @endforeach

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    @php
                                    $subModel = $model->microfinanceProductSalesProjects->where('type',$type)->where('microfinance_product_id',$product->id)->first();
                                    @endphp
                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ $product->getName() }}" disabled="" class="form-control text-left min-w-300" type="text">
                                            </div>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td data-product-id="{{ $product->id }}">


                                            @php
                                            $currentVal = $subModel ? $subModel->getFlatRateAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp


                                            @php
                                            $currentModalId = 'current-modal-id'.($columnIndex+1).$product->id
                                            @endphp
                                            <x-repeat-with-calc :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'microfinanceProductSalesProjects['.$product->id.'][flat_rates]['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc>
                                            {{-- <x-repeat-with-calc :currentModalId="$currentModalId" :showIcon="true" :numberFormatDecimals="2" :formattedInputClasses="'calcField flat-rate-input'" :mark="'%'" :removeThreeDots="false" :removeCurrency="true" :currentVal="number_format($currentVal,1)" :classes="''" :is-percentage="true" :name="'flat_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-with-calc> --}}




                                            <div class="modal fade " id="{{ $currentModalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                                    <div class="modal-content">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title" style="color:#0741A5 !important" id="exampleModalLongTitle"> {{ __('Decreasing Rate') }} % </h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="customize-elements">
                                                                <table class="table">
                                                                    <thead>

                                                                        <tr>


                                                                            <th class="text-center  text-capitalize th-main-color">{{ __('Flat Rate') }}</th>
                                                                            <th class="text-center  text-capitalize th-main-color">{{ __('Decreasing Rate') }}</th>





                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>


                                                                        <tr>
                                                                            <td class="">
                                                                                <div class="kt-input-icon ">
                                                                                    <div class="input-group">
                                                                                        <input disabled type="text" step="0.1" class="form-control ignore-global-style flat-rate-id" value="{{ 0 }}">
                                                                                    </div>
                                                                                </div>
                                                                            </td>

                                                                            <td class="">
                                                                                <div class="kt-input-icon ">
                                                                                    <div class="input-group">
                                                                                        <input disabled type="text" step="0.1" class="form-control ignore-global-style decreasing-rate-id" value="{{ 0 }}">
                                                                                    </div>
                                                                                </div>
                                                                            </td>

                                                                        </tr>




                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary " data-dismiss="modal">{{ __('Close') }} </button>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>
									
									 @include('non_banking_services.microfinance._setup-fees-trs')
									 
                                    @endforeach




                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
            {{-- end of Products Mix Pricing (Flat Rates %)  --}}


            <div class="kt-portlet " style="margin-bottom:5px;">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Loan Officers Hiring (Per New Branch) (From Branch Operation Date)') }} </h3>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    @php
                    $tableId = 'microfinanceLoanOfficerCases';
                    $repeaterId = $tableId.'_repeater';
                    @endphp
                    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
                    <x-tables.repeater-table :hideByDefault="false" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :repeater-with-select2="true" :parentClass="''" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=false">
                        <x-slot name="ths">
                            {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down" :title="__('Loan Officer <br> Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th> --}}
                            {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class="header-border-down" :title="__('Existing Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th> --}}
                            <x-tables.repeater-table-th :font-size-class="'font-14px'" class="max-w-200 header-border-down" :title="__('Position')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                            @for($i = 0 ; $i<= $months ; $i++) <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Mth-').$i . ' <br> ' .__('Hiring #')">
                                </x-tables.repeater-table-th>
                                @endfor
                        </x-slot>
                        <x-slot name="trs">
                            @php
                            $rows = isset($model) && count($model->{$tableId}->where('type',$type)->values()) ?$model->{$tableId}->where('type',$type)->values() : [null,null] ;

                            @endphp
                            @foreach($rows as $currentIndex=>$subModel)
                            @php
                            $isSeniors = [
                            0 => [
                            'is_senior'=>1 ,
                            'title'=> __('Senior Loan Officer')
                            ],
                            1=> [
                            'is_senior'=>0 ,
                            'title'=>__('Loan Officer')
                            ]
                            ][$currentIndex];
                            $isSenior = $isSeniors['is_senior'];
                            $title = $isSeniors['title'];
                            @endphp

                            <tr data-repeater-style>


                                <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][id]" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                                <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][type]" value="{{ $branchPlanningBaseType }}">
                                <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][company_id]" value="{{ $company->id }}">


                                {{-- <td>
                                    @php
                                    $currentVal = $subModel ? $subModel->getExistingCount() : 12;
                                    @endphp

                                    <x-repeat-right-dot-inputs :formattedInputClasses="'min-w-90'" :numberFormatDecimals="0" :removeThreeDots="true" :removeCurrency="true" :currentVal="number_format($currentVal,0)" :classes="'only-greater-than-zero-or-equal-allowed'" :is-percentage="false" :name="'microfinanceLoanOfficerCases['.$currentIndex.'][existing_count]'" :columnIndex="-1"></x-repeat-right-dot-inputs>
                                </td> --}}

                                <td>
                                    <input readonly value="{{ $title }}" class="form-control" type="text">

                                </td>
                                @php
                                $columnIndex = 0 ;
                                @endphp
                                @for($i = 0 ; $i<= $months ; $i++) @php $currentVal=isset($subModel) ? $subModel->getHiringAtYearOrMonthIndex($i) : 0 ;

                                    @endphp
                                    <td>
                                        <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="'microfinanceLoanOfficerCases['.$currentIndex.'][hiring]['.$i.']'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                    </td>
                                    @php
                                    $columnIndex++;
                                    @endphp
                                    @endfor



                            </tr>
                            @endforeach









                        </x-slot>




                    </x-tables.repeater-table>

                    {{-- </form> --}}
                    {{-- end of one time expense --}}




                </div>
            </div>


            {{-- <div class="kt-portlet " style="margin-bottom:5px;">
                <div class="kt-portlet__body">
                    <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('Existing Loan Officers Cases Projection') }} </h3>
            <div class="row">
                <hr style="flex:1;background-color:lightgray">
            </div>
            @php
            $tableId = 'microfinanceLoanOfficerCases';
            $repeaterId = $tableId.'_repeater';
            @endphp
            <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
            <x-tables.repeater-table :hideByDefault="false" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :repeater-with-select2="true" :parentClass="''" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=false">
                <x-slot name="ths">
                    <x-tables.repeater-table-th :font-size-class="'font-14px'" class="max-w-200 header-border-down" :title="__('Position')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                    @for($i = 0 ; $i<= $months ; $i++) <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="formatDateForView($dateIndexWithDate[$i]) . ' <br> ' .__('Cases #')">
                        </x-tables.repeater-table-th>
                        @endfor
                </x-slot>
                <x-slot name="trs">
                    @php
                    $rows = isset($model) && count($model->{$tableId}) ?$model->{$tableId} : [null,null] ;
                    @endphp
                    @foreach($rows as $currentIndex=>$subModel)
                    @php
                    $isSeniors = [
                    0 => [
                    'is_senior'=>1 ,
                    'title'=> __('Senior Loan Officer')
                    ],
                    1=> [
                    'is_senior'=>0 ,
                    'title'=>__('Loan Officer')
                    ]
                    ][$currentIndex];
                    $isSenior = $isSeniors['is_senior'];
                    $title = $isSeniors['title'];
                    @endphp

                    <tr data-repeater-style>
                        <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][id]" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                        <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][type]" value="{{ $branchPlanningBaseType }}">
                        <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][company_id]" value="{{ $company->id }}">

                        <td>
                            <input readonly value="{{ $title }}" class="form-control" type="text">

                        </td>
                        @php
                        $columnIndex = 0 ;
                        @endphp
                        @for($i = 0 ; $i<= $months ; $i++) @php $currentVal=isset($subModel) ? $subModel->getExistingLoanCasesAtYearOrMonthIndex($i) : 0 ;

                            @endphp
                            <td>
                                <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="'microfinanceLoanOfficerCases['.$currentIndex.'][existing_cases]['.$i.']'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                            </td>
                            @php
                            $columnIndex++;
                            @endphp
                            @endfor



                    </tr>
                    @endforeach







                </x-slot>




            </x-tables.repeater-table>


    </div>
</div> --}}


























<div class="kt-portlet " style="margin-bottom:5px;">
    <div class="kt-portlet__body">
        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Loan Officers Cases Projection') }} </h3>
        <div class="row">
            <hr style="flex:1;background-color:lightgray">
        </div>

        @php
        $tableId = 'microfinanceLoanOfficerCases';
        $repeaterId = $tableId.'_repeater';
        @endphp
        <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
        <x-tables.repeater-table :hideByDefault="false" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden overflow-scroll'" :hide-add-btn="true" :repeater-with-select2="true" :parentClass="''" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=false">
            <x-slot name="ths">
                {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" header-border-down" :title="__('Loan Officer <br> Count')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th> --}}
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="max-w-200 header-border-down" :title="__('Position')" :helperTitle="__('Please insert Cost Per Unit excluding VAT')"></x-tables.repeater-table-th>
                @for($i = 0 ; $i<= $months ; $i++) <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" interval-class header-border-down " :title="__('Mth-').$i . ' <br> ' .__('Cases #')">
                    </x-tables.repeater-table-th>
                    @endfor
            </x-slot>
            <x-slot name="trs">
                @php
                $rows = isset($model) && count($model->{$tableId}->where('type',$type)->values()) ?$model->{$tableId}->where('type',$type)->values() : [null,null] ;
                @endphp
                @foreach($rows as $currentIndex=>$subModel)
                @php
                $isSeniors = [
                0 => [
                'is_senior'=>1 ,
                'title'=> __('Senior Loan Officer')
                ],
                1=> [
                'is_senior'=>0 ,
                'title'=>__('Loan Officer')
                ]
                ][$currentIndex];
                $isSenior = $isSeniors['is_senior'];
                $title = $isSeniors['title'];
                @endphp

                <tr data-repeater-style>
                    <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][id]" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                    <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][type]" value="{{ $branchPlanningBaseType }}">
                    <input type="hidden" name="microfinanceLoanOfficerCases[{{ $currentIndex }}][company_id]" value="{{ $company->id }}">

                    <td>
                        <input readonly value="{{ $title }}" class="form-control" type="text">

                    </td>
                    @php
                    $columnIndex = 0 ;
                    @endphp
                    @for($i = 0 ; $i<= $months ; $i++) @php $currentVal=isset($subModel) ? $subModel->getNewLoanCasesAtYearOrMonthIndex($i) : 0 ;

                        @endphp
                        <td>
                            <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="'microfinanceLoanOfficerCases['.$currentIndex.'][new_cases]['.$i.']'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                        </td>
                        @php
                        $columnIndex++;
                        @endphp
                        @endfor



                </tr>
                @endforeach


                {{-- <tr data-repeater-style>
                                <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">

                <td>
                    <input readonly value="{{ __('Loan Officer') }}" class="form-control" type="text">

                </td>
                @php
                $columnIndex = 0 ;
                @endphp
                @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                @php
                $currentVal=isset($subModel) ? $subModel->getCountsAtMonthIndex($yearOrMonthAsIndex) : 0 ;

                @endphp
                <td>
                    <x-repeat-right-dot-inputs :numberFormatDecimals="0" :multiple="true" :removeCurrency="true" :name="'counts'" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </td>
                @php
                $columnIndex++;
                @endphp
                @endforeach



                </tr> --}}






            </x-slot>




        </x-tables.repeater-table>

        {{-- </form> --}}
        {{-- end of one time expense --}}




    </div>
</div>


{{-- <div class="kt-portlet">


                <div class="kt-portlet__body hight-200 exclude">
                    <x-calc-btn />

                </div>
            </div> --}}


<div class="kt-portlet">


    <div class="kt-portlet__body">
        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Branches Manpower Hiring Projection (From Branch Start Date)') }} </h3>
        <div class="row">
            <hr style="flex:1;background-color:lightgray">
        </div>

        {{-- @for($i = 0 ; $i<= $months ; $i++) --}}
        @include('non_banking_services.manpower._department_card',[
        'studyMonthsForViews'=>formatMonths($months),
        'remove_months'=>true ,
        'allow_existing'=>false
        ])
        {{-- @include('.manpower._department_card',['cardId'=>'card-id']) --}}


    </div>
</div>





<div class="kt-portlet">


    <div class="kt-portlet__body">
        <div class="row">
            <div class="col-md-10">
                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ __('New Branches Expense (Please Insert Avg. Expense Per Branch)') }} </h3>

            </div>
            {{-- <div class="col-md-2">
                            <div class="d-flex align-items-center column-gap-10">
                                <label class="form-label label ">{{ __('Existing Branches Counts') }}</label>
            <input name="existing_branches_counts" value="{{ $model->getExistingBranchCounts() }}" class="form-control " type="text">
        </div>
    </div> --}}
</div>
<div class="row">
    <hr style="flex:1;background-color:lightgray">
</div>



<div class="form-group row justify-content-center">
    @php
    $index = 0 ;
    @endphp




    {{-- start of fixed monthly repeating amount --}}
    @php
    $tableId = 'fixed_monthly_repeating_amount';
    $repeaterId = 'fixed_monthly_repeating_amount_repeater';

    @endphp
    <input type="hidden" name="tableIds[]" value="{{ $tableId }}">
    <x-tables.repeater-table :triggerInputChangeWhenAddNew="true" :hideByDefault="false" :removeRepeater="false" :repeater-with-select2="true" :parentClass="'expenses-table js-toggle-visibility'" :tableName="$tableId" :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
        <x-slot name="ths">
            <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Expense <br> Category')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-2 header-border-down" :title="__('Expense <br> Name')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-2 header-border-down  " :title="__('Start <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-1 header-border-down" :title="__('Monthly <br> Amount')" :helperTitle="__('Please insert amount excluding VAT')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-1 hidden header-border-down" :title="__('End <br> Date')" :helperTitle="__('Default date is Income Statement start date, if else please select a date')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-1 header-border-down" :title="__('Payment <br> Terms')" :helperTitle="__('You can either choose one of the system default terms (cash, quarterly, semi-annually, or annually), if else please choose Customize to insert your payment terms')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-1 header-border-down rate-class" :title="__('VAT <br> Rate')"></x-tables.repeater-table-th>
            <x-tables.repeater-table-th class="col-md-1 header-border-down rate-class" :title="__('Withhold <br> Tax Rate')" :helperTitle="__('Withhold Tax rate will be calculated based on Monthly Amount excluding VAT')"></x-tables.repeater-table-th>
            @if(!$model->durationIsLessThanOneOrEqualYear())
            <x-tables.repeater-table-th class="col-md-1 header-border-down rate-class" :title="__('Annual <br> Increase%')"></x-tables.repeater-table-th>
            @endif
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
                <td class="text-center">
                    <div class="">
                        <i data-repeater-delete="" class="btn-sm btn btn-danger m-btn m-btn--icon m-btn--pill trash_icon fas fa-times-circle">
                        </i>
                    </div>
                </td>


                <input type="hidden" name="id" value="{{ isset($subModel) ? $subModel->id : 0 }}">
                <td>
                    <x-form.select :selectedValue="isset($subModel) ? $subModel->getExpenseCategory() : 'cash'" :options="getBranchExpenseCategoriesForSelect2()" :add-new="false" class="select2-select repeater-select expense_category " :all="false" name="@if($isRepeater) expense_category @else {{ $tableId }}[0][expense_category] @endif"></x-form.select>
                </td>

                <td>
                    <x-form.select data-current-selected="{{ isset($subModel) ? $subModel->getExpenseNameId() : '' }}" :selectedValue="isset($subModel) ? $subModel->getExpenseNameId() : ''" :options="[]" :add-new="false" class="select2-select repeater-select expense_name_id " :all="false" name="@if($isRepeater) expense_name_id @else {{ $tableId }}[0][expense_name_id] @endif"></x-form.select>
                </td>

                <td>

                    <x-form.select :required="true" :label="''" :pleaseSelect="false" :selectedValue="isset($subModel) ? $subModel->getStartDateType():0" :options="getMicrofinanceNewBranchesFixedExpenseSelector()" :add-new="false" class="select2-select repeater-select  " :all="false" name="@if($isRepeater) start_date_type @else {{ $tableId }}[0][start_date_type] @endif"></x-form.select>


                    {{-- <div class="max-w-150">
                                            @include('components.calendar-month-year',[
                                            'name'=>'start_date',
                                            'value'=>isset($subModel) ? $subModel->getStartDateYearAndMonth() : $study->getOperationStartDateYearAndMonth()
                                            ])

                                        </div> --}}
                </td>
                <td>
                    <input value="{{ (isset($subModel) ? number_format($subModel->getAmount(),0) : 0) }}" class="form-control text-center only-greater-than-or-equal-zero-allowed" type="text">
                    <input type="hidden" value="{{ (isset($subModel) ? $subModel->getAmount() : 0) }}" @if($isRepeater) name="amount" @else name="{{ $tableId }}[0][amount]" @endif>
                </td>
                <td style="display:none">
                    <div class="">
                        @include('components.calendar-month-year',[
                        'name'=>'end_date',
                        'value'=>isset($subModel) ? $subModel->getEndDateYearAndMonth() : $study->getStudyEndDateYearAndMonth()
                        ])
                    </div>

                </td>
                <td>
                    <div class="">
                        <x-form.select :selectedValue="isset($subModel) ? $subModel->getPaymentTerm() : 'cash'" :options="getPaymentTerms()" :add-new="false" class="select2-select payment_terms repeater-select  " :all="false" name="@if($isRepeater) payment_terms @else {{ $tableId }}[0][payment_terms] @endif"></x-form.select>
                        <x-modal.custom-collection :size="'sm'" :title="__('Payment Terms')" :subModel="isset($subModel) ? $subModel : null " :subModel="isset($subModel) ? $subModel : null " :tableId="$tableId" :isRepeater="$isRepeater" :id="$repeaterId.'test-modal-id'"></x-modal.custom-collection>

                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getVatRate(),PERCENTAGE_DECIMALS):0  }}" type="text">
                        <span style="margin-left:3px	">%</span>
                        <input type="hidden" value="{{ (isset($subModel) ? $subModel->getVatRate() : 0) }}" @if($isRepeater) name="vat_rate" @else name="{{ $tableId }}[0][vat_rate]" @endif>

                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input class="form-control only-percentage-allowed text-center" value="{{ isset($subModel) ? number_format($subModel->getWithholdTaxRate(),PERCENTAGE_DECIMALS) : "0.00" }}" type="text">
                        <span style="margin-left:3px	">%</span>
                        <input type="hidden" value="{{ (isset($subModel) ? $subModel->getWithholdTaxRate() : 0) }}" @if($isRepeater) name="withhold_tax_rate" @else name="{{ $tableId }}[0][withhold_tax_rate]" @endif>
                    </div>
                </td>
                @if(!$model->durationIsLessThanOneOrEqualYear())
                <td>
                    <div class="d-flex align-items-center increase-rate-parent">
                        <button class="btn btn-primary btn-md text-nowrap increase-rate-trigger-btn" type="button" data-toggle="modal">{{ __('Increase Rates') }}</button>
                        <x-modal.increase-rates :study="$study" :subModel="isset($subModel) ? $subModel : null " :tableId="$tableId" :isRepeater="$isRepeater" :id="$repeaterId.'test-modal-id'"></x-modal.increase-rates>
                    </div>
                </td>
                @endif
            </tr>
            @endforeach

        </x-slot>




    </x-tables.repeater-table>
    {{-- end of fixed monthly repeating amount --}}

</div>


</div>
</div>








<x-save-and-next-btn />

{{-- <x-save-or-continue-btn /> --}}




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

            let form = document.getElementById('form-id');
            var formData = new FormData(form);
            $('.save-form').prop('disabled', true);
            var saveAndContinue = $(this).attr('data-save-and-continue');
            formData.append('saveAndContinue', saveAndContinue);
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


    //  $(document).on('change', '#expense_type', function() {
    //      $('.js-parent-to-table').hide();
    //      let tableId = '.' + $(this).val();
    //      $(tableId).closest('.js-parent-to-table').show();
    //
    //  }) 



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
            url: "{{ route('get.expense.name.for.category.only.in.branch',['company'=>$company->id,'study'=>$study->id]) }}"
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
    $(document).on('change', '.recalculate-total-branches', function() {
        var totalBranchesCount = 0;
        $('.recalculate-total-branches').each(function(index, element) {
            var currentBranchCount = parseInt(number_unformat($(element).val()));
            totalBranchesCount += currentBranchCount;
            $(element).closest('tr').find('.total-branches-text').val(totalBranchesCount).trigger('change');
        })
    })
    $(function() {
        //		$('.recalculate-total-branches:eq(0)').trigger('change')
    })

</script>


<script>
    $('.product-input-class').on('change', function() {
        const columnIndex = $(this).attr('data-column-index');
        let total = 0;
        $(this).closest('table').find('input[type="hidden"][data-column-index="' + columnIndex + '"]').each(function(index, currentInput) {
            total += parseFloat($(currentInput).val());
        })
        $(this).closest('table').find('input.sum-total-row[data-column-index="' + columnIndex + '"]').val(total);
    })

    $(document).on('change', 'input.seasonality-class', function() {
        let total = 0;
        $(this).closest('tr').find('input[type="hidden"]').each(function(index, currentInput) {
            total += parseFloat($(currentInput).val());
        })
        $(this).closest('tr').find('input.sum-total-column').val(number_format(total, 1));
    })

</script>

@endpush
