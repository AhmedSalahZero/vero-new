@php
	$tableClasses = 'col-md-12';
@endphp
@php
$currentYearRepeaterIndex = 0 ;
@endphp

<x-tables.repeater-table :table-class="$tableClasses" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
     <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down first-column-th-class max-250-w" :title="__('Item')"></x-tables.repeater-table-th>
        @foreach($yearOrMonthsIndexes as $yearOrMonthAsIndex=>$yearOrMonthFormatted)
		   <x-tables.repeater-table-th  data-column-index="{{ $yearOrMonthAsIndex }}" class="  header-border-down" :title="$yearOrMonthFormatted"></x-tables.repeater-table-th>
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

        <tr data-repeat-formatting-decimals="2" data-repeater-style>


            @php
            $currentExpenseType = 'cost-of-service';
            @endphp
            <td>
                <div class="min-w-255">
                    <input value="{{ __('Cost Of Service % / REV') }}" disabled class="form-control    text-left " type="text">
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
            $currentExpense = $formattedExpenses['cost-of-service']['total'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['cost-of-service'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
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

        <tr data-repeat-formatting-decimals="2" data-repeater-style>



            <td>
                <div class="min-w-255">
                    <input value="{{ __('Other OPEX % / REV.') }}" disabled class="form-control text-left " type="text">
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
            $currentExpense = $formattedExpenses['other-operation-expense']['total'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;

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
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['other-operation-expense'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
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







        <tr data-repeat-formatting-decimals="2" data-repeater-style>



            <td>
                <div class="min-w-255">
                    <input value="{{ __('Marketing Exp. % / REV.') }}" disabled class="form-control text-left " type="text">
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
            $currentExpense = $formattedExpenses['marketing-expense']['total'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['marketing-expense'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
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







        <tr data-repeat-formatting-decimals="2" data-repeater-style>



            <td>
                <div class="min-w-255">
                    <input value="{{ __('Sales Exp. % / REV.') }}" disabled class="form-control text-left " type="text">
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
            $currentExpense = $formattedExpenses['sales-expense']['total'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['sales-expense'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
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








        <tr data-repeat-formatting-decimals="2" data-repeater-style>



            <td>
                <div class="min-w-255">
                    <input value="{{ __('G&A Exp. % / REV.') }}" disabled class="form-control text-left " type="text">
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
            $currentExpense = $formattedExpenses['general-expense']['total'][$yearOrMonthAsIndex]??0;
            $currentSalesRevenue = $formattedResult['sales_revenue'][$yearOrMonthAsIndex]??0 ;
            $currentVal = $currentSalesRevenue ? $currentExpense / $currentSalesRevenue * 100 : 0 ;
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
                    <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="2" :mark="' %'" :currentVal="$formattedResult['percentage_of_revenues_per_years']['general-expense'][$yearOrMonthAsIndex]??0" :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
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
