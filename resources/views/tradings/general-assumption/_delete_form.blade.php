@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>

{{-- <x-navigators-dropdown :navigators="$navigators"></x-navigators-dropdown> --}}

@endsection
@section('content')
<div class="row">
    <div class="col-md-12">

        <form id="form-id" class="kt-form kt-form--label-right" method="POST" enctype="multipart/form-data" action="{{  isset($disabled) && $disabled ? '#' :  $storeRoute  }}">

            @csrf
            <input type="hidden" name="company_id" value="{{ getCurrentCompanyId()  }}">
            <input type="hidden" name="creator_id" value="{{ \Auth::id()  }}">
            <input type="hidden" name="study_id" value="{{ $study->id }}">


            {{-- start of reserve assumption  --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 mb-4">

                                    <div class="d-flex align-items-center ">
                                        <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                            {{ __('Reserve Assumption') }}
                                        </h3>


                                    </div>
                                    <div class="row">
                                        <hr style="flex:1;background-color:lightgray">
                                    </div>

                                </div>


                                <div class="col-md-3 mb-4">
                                    <label class="form-label font-weight-bold">{{ __('Legal Reserve Rate %')  }} @include('star')</label>
                                    <div class="kt-input-icon">
                                        <div class="input-group">
                                            <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="legal_reserve_rate" value="{{ isset($model) ? $model->getLegalReserveRate() : 5 }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-3 mb-4">
                                    <label class="form-label font-weight-bold">{{ __('Max Legal Reserve Rate %') . ' ' . __(' ( From Paid Up Capital)')  }} @include('star')</label>
                                    <div class="kt-input-icon">
                                        <div class="input-group">
                                            <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="max_legal_reserve_rate" value="{{ isset($model) ? $model->getMaxLegalReserveRate() : 50 }}">
                                        </div>
                                    </div>
                                </div>




                                <div class="col-md-3 mb-4">
                                    <label class="form-label font-weight-bold">{{ __('Financial Regularity Authority Reserve (FRA %) ')  }} @include('star')</label>
                                    <div class="kt-input-icon">
                                        <div class="input-group">
                                            <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="financial_regulatory_authority_rate" value="{{ isset($model) ? $model->getFinancialRegulatoryAuthorityRate() : 0 }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-3 mb-4">
                                    <label class="form-label font-weight-bold">{{ __('Max Financial Regularity Authority Reserve (FRA %) ')  }} @include('star')</label>
                                    <div class="kt-input-icon">
                                        <div class="input-group">
                                            <input type="number" class="form-control only-greater-than-or-equal-zero-allowed " name="max_financial_regulatory_authority_rate" value="{{ isset($model) ? $model->getMaxFinancialRegulatoryAuthorityRate() : 0 }}">
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('Profit Distribution Assumption') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn active-style show-hide-repeater" data-query=".reserve-and-profit-distribution-assumption">{{ __('Show/Hide') }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row reserve-and-profit-distribution-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class="first-column-th-class-medium form-label font-weight-bold text-center align-middle interval-class header-border-down">{{ __('Item') }}</th>
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class="form-label font-weight-bold  text-center align-middle interval-class header-border-down"> {{$yearOrMonthFormatted}} </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $currentTotal = [];

                                    @endphp
									@if($isYearsStudy)
                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ __('Operating Months Per Year') }}" disabled="" class="form-control text-left mt-2" type="text">

                                            </div>

                                        </td>


                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            @endphp

                                            {{-- <x-repeat-right-dot-inputs :currentVal="$currentVal" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :name="'cbe_lending_corridor_rates['.$year.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs> --}}
                                            <div class="form-group three-dots-parent">
                                                <div class="input-group input-group-sm align-items-center justify-content-center div-for-percentage">
                                                    <input type="text" style="max-width: 60px;min-width: 60px;text-align: center" value="{{ sumNumberOfOnes($yearsWithItsMonths,$yearOrMonthAsIndex,$datesIndexWithYearIndex) }}" readonly onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control target_repeating_amounts only-percentage-allowed size" data-date="#" data-section="target" aria-describedby="basic-addon2">
                                                    <span class="ml-2">
                                                        <b style="visibility:hidden">%</b>
                                                    </span>
                                                </div>
                                            </div>

                                        </td>

                                        @endforeach

                                    </tr>
									@endif








                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                        <td class="td-classes">
                                            <div>

                                                <input value="{{ __('Employee Profit Share Rate') }}" disabled="" class="form-control text-left mt-2" type="text">
                                            </div>

                                        </td>

                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getEmployeeProfitShareRatesAtYearIndex($yearOrMonthAsIndex) : 10;
                                            @endphp
                                            <x-repeat-right-dot-inputs :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :name="'employee_profit_share_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ __('Board Of Directors Profit Share Rates') }}" disabled="" class="form-control text-left mt-2" type="text">
                                            </div>

                                        </td>

                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getBorderOfDirectorsProfitShareRateAtYearIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'border_of_directors_profit_share_rates['.$yearOrMonthAsIndex.']'" :currentVal="$currentVal" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>





                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ __('Shareholders First Dividend Portion') }}" disabled="" class="form-control text-left mt-2" type="text">

                                            </div>

                                        </td>


                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getShareholderFirstDividendPortionAtYearIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'shareholders_first_dividend_portions['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>



                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>

                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ __('Shareholders Dividend Payout Ratio %') }}" disabled="" class="form-control text-left mt-2" type="text">

                                            </div>

                                        </td>

                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getShareholderDividendPayoutRatioAtYearIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'shareholders_dividend_payout_ratios['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>



                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                        <td class="td-classes">
                                            <div>
                                                <input value="{{ __('Shareholders Dividend (In Cash Or Shares)') }}" disabled="" class="form-control text-left mt-2" type="text">

                                            </div>

                                        </td>

                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getShareholderDividendInCashOrSharesAtYear($yearOrMonthAsIndex) : 'in_cash';
                                            @endphp
                                            {{-- <x-repeat-right-dot-inputs :name="'shareholders_dividend_payout_ratios['.$year.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-zero-allowed'" :is-percentage="true"  :columnIndex="$columnIndex"></x-repeat-right-dot-inputs> --}}
                                            <div class="form-group three-dots-parent">

                                                <select class="form-control select-inside-repeating-table-css repeat-to-right-select text-center " name="shareholders_dividend_in_cash_or_shares[{{ $yearOrMonthAsIndex }}]" data-column-index="{{ $columnIndex}}">
                                                    @foreach(['in_cash'=>__('In Cash') , 'in_share'=>__('In Shares')] as $value => $title)
                                                    <option @if($value==$currentVal) selected @endif value="{{ $value }}"> {{ $title }} </option>
                                                    @endforeach
                                                </select>

                                                <i class="fa fa-ellipsis-h pull-left repeat-select-to-right row-repeater-icon " data-column-index="{{ $columnIndex}}" title="{{__('Repeat Right')}}"></i>
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
            {{-- end of reserve assumption  --}}



			@if($isYearsStudy)
            {{-- start of general assumption  --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">

                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('Salaries Annual Increase Rate') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn active-style show-hide-repeater" data-query=".general-assumption">{{ __('Show/Hide') }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row general-assumption">


                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class="first-column-th-class-medium form-label font-weight-bold  text-center align-middle interval-class header-border-down">{{ __('Item') }}</th>
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class="form-label font-weight-bold  text-center align-middle interval-class header-border-down"> {{$yearOrMonthFormatted}} </th>
                                        @endforeach
                                  
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $currentTotal = [];

                                    @endphp
									@if($isYearsStudy)
                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('Operating Months Per Year') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                    


                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            @endphp


                                            <div class="form-group three-dots-parent">
                                                <div class="input-group input-group-sm align-items-center justify-content-center div-for-percentage">
                                                    <input type="text" style="max-width: 60px;min-width: 60px;text-align: center" value="{{ sumNumberOfOnes($yearsWithItsMonths,$yearOrMonthAsIndex,$datesIndexWithYearIndex) }}" readonly onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control target_repeating_amounts only-percentage-allowed size" data-date="#" data-section="target" aria-describedby="basic-addon2">
                                                    <span class="ml-2">
                                                        <b style="visibility:hidden">%</b>
                                                    </span>
                                                </div>
                                            </div>

                                        </td>

                                        @endforeach

                                    </tr>
									@endif




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style >
									<td class="td-classes">
										<div>
										<input value="{{  __('Salaries Annual Increase Rate %')  }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                       
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getSalariesAnnualIncreaseRateAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :readonly="$columnIndex == 0" :removeThreeDots="$columnIndex == 0" :name="'salaries_annual_increase_rates['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>


                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>




                                    {{-- <tr data-repeat-formatting-decimals="2" data-repeater-style>
                                      
										
											<td class="td-classes">
										<div>
										<input value="{{ $isYearsStudy ? __('Expense Annual Increase Rate %') : __('Expense Monthly Increase Rate %') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getExpenseAnnualIncreaseRateAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'expense_annual_increase_rates['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>


                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr> --}}



                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
			@endif
            {{-- end of general assumption  --}}









            {{-- start of CBE Corridor & Banks Lending Margins & Interest Rates   --}}
            <div class="kt-portlet">
                <div class="kt-portlet__body">
                    <div class="row">

                        <div class="col-md-10">
                            <div class="d-flex align-items-center ">
                                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style="">
                                    {{ __('CBE Corridor & Banks Lending Margins & Interest Rates') }}
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn active-style show-hide-repeater" data-query=".general-assumption">{{ __('Show/Hide') }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <hr style="flex:1;background-color:lightgray">
                    </div>
                    <div class="row general-assumption">

{{-- {{ dd($yearOrMonthsIndexes) }} --}}
                        <div class="table-responsive">
                            <table class="table table-white repeater-class repeater ">
                                <thead>
                                    <tr>
                                        <th class="first-column-th-class-medium form-label font-weight-bold text-center align-middle interval-class header-border-down">{{ __('Item') }}</th>
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
                                        <th class="form-label font-weight-bold text-center align-middle interval-class header-border-down">{{$yearOrMonthFormatted}} </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $currentTotal = [];

                                    @endphp
									@if($isYearsStudy)
                                    <tr data-repeat-formatting-decimals="0" data-repeater-style>
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('Operating Months Per Year') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>




                                            <div class="form-group three-dots-parent">
                                                <div class="input-group input-group-sm align-items-center justify-content-center div-for-percentage">
                                                    <input type="text" style="max-width: 60px;min-width: 60px;text-align: center" value="{{ sumNumberOfOnes($yearsWithItsMonths,$yearOrMonthAsIndex,$datesIndexWithYearIndex) }}" readonly onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control target_repeating_amounts only-percentage-allowed size" data-date="#" data-section="target" aria-describedby="basic-addon2">
                                                    <span class="ml-2">
                                                        <b style="visibility:hidden">%</b>
                                                    </span>
                                                </div>
                                            </div>

                                        </td>

                                        @endforeach

                                    </tr>
									@endif




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style >
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('CBE Lending Corridor Rate %') }}" disabled="" class="form-control min-w-300 text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                       
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getCbeLendingCorridorRatesAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp


                                            <x-repeat-right-dot-inputs :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="'cbe_lending_corridor_rates['.$yearOrMonthAsIndex.']'" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>


                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('MTLs Banks Lending Margin Rate %') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                     
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getBankLendingMarginRatesAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'bank_lending_margin_rates['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>


                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>
									
									@if($study->hasMicrofinance())
									
									 <tr data-repeat-formatting-decimals="2" data-repeater-style>
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('ODAs MTLs Banks Lending Margin Rate %') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
										
                                     
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getOdasBankLendingMarginRatesAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'odas_bank_lending_margin_rates['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>


                                        </td>
                                        @php
                                        $columnIndex++ ;
                                        @endphp

                                        @endforeach


                                    </tr>
									
									
									@endif 




                                    <tr data-repeat-formatting-decimals="2" data-repeater-style>
									
									 <td class="td-classes">
										<div>
										<input value="{{ __('Credit Interest Rate For Surplus Cash %') }}" disabled="" class="form-control text-left mt-2" type="text">
										
										</div>
										
                                        </td>
									
                                        @php
                                        $columnIndex = 0 ;
                                        @endphp
                                        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)

                                        <td>

                                            @php
                                            $currentVal = $model ? $model->getCreditInterestRateForSurplusCashAtYearOrMonthIndex($yearOrMonthAsIndex) : 0;
                                            @endphp
                                            <x-repeat-right-dot-inputs :name="'credit_interest_rate_for_surplus_cash['.$yearOrMonthAsIndex.']'" :currentVal="number_format($currentVal,1)" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
            {{-- end of CBE Corridor & Banks Lending Margins & Interest Rates   --}}





































            <x-save-or-back :btn-text="__('Create')" />
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


</script>

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
   



  


</script>
<script>
    $(document).on('change', '[data-calc-adr-operating-date]', function() {
        const power = parseFloat($('#daysDifference').val());
        const roomTypeId = $(this).attr('data-room-type-id');
        let avgDailyRate = $('.avg-daily-rate[data-room-type-id="' + roomTypeId + '"]').val();
        avgDailyRate = number_unformat(avgDailyRate)
        let ascalationRate = $('.adr-escalation-rate[data-room-type-id="' + roomTypeId + '"]').val() / 100;

        const result = avgDailyRate * Math.pow(((1 + ascalationRate)), power)
        $('.value-for-adr_at_operation_date[data-room-type-id="' + roomTypeId + '"]').val(result)
        $('.html-for-adr_at_operation_date[data-room-type-id="' + roomTypeId + '"]').val(number_format(result))
    })
    $(document).on('change', '.add-sales-channels-share-discount', function() {
        let val = +$(this).attr('value');
        if (val) {
            $('[data-is-sales-channel-revenue-discount-section]').show();
        } else {
            $('[data-is-sales-channel-revenue-discount-section]').hide();

        }
    })
    $(document).on('change', '.occupancy-rate', function() {
        let val = $(this).attr('value');

        if (val == 'general_occupancy_rate') {
            $('[data-name="general_occupancy_rate"]').fadeIn(300)
            $('[data-name="occupancy_rate_per_room"]').fadeOut(300)
        } else {
            $('[data-name="general_occupancy_rate"]').fadeOut(300)
            $('[data-name="occupancy_rate_per_room"]').fadeIn(300)

        }
    })
    $(document).on('change', '.collection_rate_class', function() {
        let val = $(this).val();
        if (val == 'terms_per_sales_channel') {
            $('[data-name="per-sales-channel-collection"]').fadeIn(300)
            $('[data-name="general-collection-policy"]').fadeOut(300)
        } else {
            $('[data-name="per-sales-channel-collection"]').fadeOut(300)
            $('[data-name="general-collection-policy"]').fadeIn(300)

        }
    })

    $(document).on('change', '.seasonlity-select', function() {
        const mainSelect = $('.main-seasonality-select').val()
        const secondarySelect = $('.secondary-seasonality-select').val();
        $('.one-of-seasonality-tables-parent').addClass('d-none');
        $('[data-select-1*="' + mainSelect + '"][data-select-2*="' + secondarySelect + '"]').removeClass('d-none')

    })

    $(document).on('change', '.collection_rate_input', function() {
        let salesChannelName = $(this).attr('data-sales-channel-name')
        let total = 0;
        $('.collection_rate_input[data-sales-channel-name="' + salesChannelName + '"]').each(function(index, input) {
            total += parseFloat(input.value)
        })
        $('.collection_rate_total_class[data-sales-channel-name="' + salesChannelName + '"]').val(total)
    })


    $(function() {
        $('[data-calc-adr-operating-date]').trigger('change')
        $('.occupancy-rate:checked').trigger('change')
        $('.collection_rate_class:checked').trigger('change')
        $('.add-sales-channels-share-discount:checked').trigger('change')
        $('.main-seasonality-select').trigger('change')
        $('[data-repeater-create]').trigger('')
    })

    $(document).on('change keyup', '.recalc-avg-weight-total', function() {
        const order = this.getAttribute('data-order')
        let currentTotal = 0;
        $('.revenue-share-percentage[data-order="' + order + '"]').each(function(i, revenueSharePercentageInput) {
            var currentIndex = revenueSharePercentageInput.getAttribute('data-index');
            var revenueSharePercentageAtIndex = $(revenueSharePercentageInput).parent().find('input[type="hidden"]').val();
            revenueSharePercentageAtIndex = revenueSharePercentageAtIndex ? revenueSharePercentageAtIndex / 100 : 0;
            var discountSharePercentageAtIndex = $('.discount-commission-percentage[data-order="' + order + '"][data-index="' + currentIndex + '"]').parent().find('input[type="hidden"]').val();
            discountSharePercentageAtIndex = discountSharePercentageAtIndex ? discountSharePercentageAtIndex / 100 : 0;
            currentTotal += discountSharePercentageAtIndex * revenueSharePercentageAtIndex;
        })
        currentTotal = currentTotal * 100;
        $('.weight-avg-total-hidden[data-order="' + order + '"]').val(currentTotal);
        $('.weight-avg-total[data-order="' + order + '"]').val(number_format(currentTotal, 1)).trigger('keyup');
    })


    $(function() {



        $('.recalc-avg-weight-total').trigger('change')
    })
    $(function() {
        $('.choosen-currency-class').on('change', function() {
            $('.choosen-currency-class').val($(this).val())
        })
        $('.choosen-currency-class').trigger('change');
    })

</script>
<script src="/custom/js/non-banking-services/common.js"></script>
<script src="/custom/js/non-banking-services/select2.js"></script>

@endsection
