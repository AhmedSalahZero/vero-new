@php
$tableClass = 'col-md-12';
@endphp
@php
$currentYearRepeaterIndex = 0 ;
@endphp
<x-tables.repeater-table :table-class="$tableClass" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
    <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down first-column-th-class min-250-w" :title="__('Item')"></x-tables.repeater-table-th>
        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
        <x-tables.repeater-table-th data-column-index="{{ $yearOrMonthAsIndex }}" class="  header-border-down" :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
		
		@php
        $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
        $currentMonthNumber = explode('-',$dateAsString)[1];
        $currentYear= explode('-',$dateAsString)[0];
        @endphp

        @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
        <x-tables.repeater-table-th :icon="true" data-column-index="{{ $yearOrMonthAsIndex }}" :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse" :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
        @php
        $currentYearRepeaterIndex ++;
        @endphp
        @endif
		
        @endforeach
    </x-slot>
    <x-slot name="trs">
        {{-- @if($isYearsStudy)
                                    <tr data-repeat-formatting-decimals="1" data-repeater-style>



                                        <td>
                                            <div class="min-250-w">
                                                <input value="{{ __('Operating Months') }}" disabled class="form-control text-left " type="text">
        </div>


        </td>
        @php
        $columnIndex = 0 ;
        @endphp
        @foreach($yearsWithItsMonths as $year=>$monthsForThisYearArray)

        <td>
            <div class="form-group three-dots-parent">
                <div class="input-group input-group-sm align-items-center justify-content-center div-for-percentage">
                    <input type="text" style="max-width: 60px;min-width: 60px;text-align: center" value="{{ sumNumberOfOnes($yearsWithItsMonths,$year,$datesIndexWithYearIndex) }}" readonly onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control target_repeating_amounts only-percentage-allowed size" data-date="#" data-section="target" aria-describedby="basic-addon2">
                    <span class="ml-2">
                        <b style="visibility:hidden">%</b>
                    </span>
                </div>
            </div>
        </td>
        @php
        $columnIndex++;
        @endphp
        @endforeach


        </tr>
        @endif --}}







        <tr data-repeat-formatting-decimals="1" data-repeater-style>



            <td>
                <div class="">
                    <input value="{{ __('Revenues Growth Rate %') }}" disabled class="form-control min-250-w text-left " type="text">
                </div>


            </td>
            @php
            $columnIndex = 0 ;

            @endphp

            @php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp

            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['growth_rate'][$yearOrMonthAsIndex] ?? 0;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                </div>
            </td>

            @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">

                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' %'" :currentVal="$formattedResult['sales_revenue_growth_rate_per_years'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif

            @php
            $columnIndex++;
            @endphp
            @endforeach



        </tr>
















        <tr data-repeat-formatting-decimals="1" data-repeater-style>



            <td>
                <div class="">
                    <input value="{{ __('Gross Profit % / REV.') }}" disabled class="form-control min-250-w text-left " type="text">
                </div>


            </td>
            @php
            $columnIndex = 0 ;


            @endphp

            @php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp

            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['gross_profit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </div>
            </td>
            @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['gross_profit'][$yearOrMonthAsIndex]" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif

            @php
            $columnIndex++;
            @endphp
            @endforeach



        </tr>












        <tr data-repeat-formatting-decimals="1" data-repeater-style>



            <td>
                <div class="">
                    <input value="{{ __('EBITDA % / REV.') }}" disabled class="form-control min-250-w  text-left " type="text">
                </div>


            </td>
            @php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp

            @php
            $columnIndex = 0 ;
            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['ebitda_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </div>
            </td>

            @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['ebitda'][$yearOrMonthAsIndex]" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif
            @php
            $columnIndex++;
            @endphp
            @endforeach



        </tr>







        <tr data-repeat-formatting-decimals="1" data-repeater-style>

            <td>
                <div class="">
                    <input value="{{ __('EBIT % / REV.') }}" disabled class="form-control min-250-w  text-left " type="text">
                </div>


            </td>
			
			 @php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp
			
            @php
            $columnIndex = 0 ;

            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['ebit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </div>
            </td>
			
            @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['ebit'][$yearOrMonthAsIndex]" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif
			
            @php
            $columnIndex++;
            @endphp
            @endforeach



        </tr>







        <tr data-repeat-formatting-decimals="1" data-repeater-style>



            <td>
                <div class="">
                    <input value="{{ __('EBT % / REV.') }}" disabled class="form-control min-250-w  text-left " type="text">
                </div>


            </td>
			
			
			@php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp
			
            @php
            $columnIndex = 0 ;


            @endphp
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['ebt_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
            @endphp
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </div>
            </td>
               @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['ebt'][$yearOrMonthAsIndex]" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif
			
			
			@php
			
            $columnIndex++;
            @endphp
            @endforeach



        </tr>








        <tr data-repeat-formatting-decimals="1" data-repeater-style>



            <td>
                <div class="">
                    <input value="{{ __('Net Profit % / REV.') }}" disabled class="form-control min-250-w  text-left " type="text">
                </div>


            </td>
            @php
            $columnIndex = 0 ;


            @endphp
			@php
            $columnIndex = 0 ;
            $currentYearRepeaterIndex = 0 ;
            $currentYearTotal = 0 ;
            $currentRowTotal = 0;
            @endphp
			
            @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
            @php
            $currentVal = $formattedResult['net_profit_percentage_of_sales'][$yearOrMonthAsIndex] ?? 0;
            @endphp

            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :numberFormatDecimals="1" :removeThreeDotsClass="true" :removeThreeDots="true" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed'" :is-percentage="true" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>

                </div>
            </td>
			
			  @php
            $dateAsString = $dateIndexWithDate[$yearOrMonthAsIndex];
            $currentMonthNumber = explode('-',$dateAsString)[1];
            $currentYear= explode('-',$dateAsString)[0];
            $currentYearTotal+=$currentVal;
            $currentRowTotal+=$currentVal;
            @endphp

            @if($study->isMonthlyStudy() && ($study->getFinancialYearEndMonthNumber() == $currentMonthNumber || $loop->last))
            <td data-column-index="{{ $yearOrMonthAsIndex }}" class="exclude-from-collapse">
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['net_profit'][$yearOrMonthAsIndex]" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                </div>

            </td>
            @php
            $currentYearRepeaterIndex++;
            $currentYearTotal = 0;
            @endphp
            @endif
			
            @php
            $columnIndex++;
            @endphp
            @endforeach



        </tr>







    </x-slot>




</x-tables.repeater-table>
