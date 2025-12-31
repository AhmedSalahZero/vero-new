@php
	$tableClasses =  'col-md-12';
@endphp
@php
$currentYearRepeaterIndex = 0 ;
@endphp
     <x-tables.repeater-table :table-class="$tableClasses" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
        
		   <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down first-column-th-class max-250-w" :title="__('Item')"></x-tables.repeater-table-th>
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
             <tr data-repeat-formatting-decimals="0" data-repeater-style>



                 <td>
                     <div class="max-w-250">
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


             <tr data-repeat-formatting-decimals="0" data-repeater-style>
                 @php
                 $key ='cost-of-service';
                 $currentModalId = $key.'-modal-id';
                 $currentModalTitle = __('Cost Of Service (Fig In Million)') ;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center ">
                         <input value="{{ __('Cost Of Service') }}" disabled class="form-control min-250-w text-left " type="text">
                         <div>
                             <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                             @include('non_banking_services.dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                         </div>

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
                 $currentVal = ($formattedExpenses['cost-of-service']['total'][$yearOrMonthAsIndex]??0) / 1000000;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center justify-content-center">
                         <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden '" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                        </div>

                                    </td>
                                    @php
                                    $currentYearRepeaterIndex++;
                                    $currentYearTotal = 0;
                                    @endphp
                                    @endif
                                    
                 @php
                 $columnIndex++ ;
                 @endphp

                 @endforeach


             </tr>







             <tr data-repeat-formatting-decimals="0" data-repeater-style>
                 @php
                 $key ='other-operation-expense';
                 $currentModalId = $key.'-modal-id';
                 $currentModalTitle = __('Other Operating Expenses (Fig In Million)' ) ;
                 @endphp

                 <td>
                     <div class="d-flex align-items-center ">
                         <input value="{{ __('Other OPEX') }}" disabled class="form-control min-250-w text-left " type="text">
                         <div>
                             <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                             @include('non_banking_services.dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])

                         </div>

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
                 $currentVal = ($formattedExpenses['other-operation-expense']['total'][$yearOrMonthAsIndex]??0) / 1000000;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center justify-content-center">
                         <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden '" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                        </div>

                                    </td>
                                    @php
                                    $currentYearRepeaterIndex++;
                                    $currentYearTotal = 0;
                                    @endphp
                                    @endif
									
                 @php
                 $columnIndex++ ;
                 @endphp

                 @endforeach


             </tr>





             <tr data-repeat-formatting-decimals="0" data-repeater-style>

                 @php
                 $key ='marketing-expense';
                 $currentModalId = $key.'-modal-id';
                 $currentModalTitle = __('Marketing Expenses (Fig In Million)') ;
                 @endphp

                 <td>
                     <div class="d-flex align-items-center  ">
                         <input value="{{ __('Marketing Expenses') }}" disabled class="form-control min-250-w text-left " type="text">
                         <div>
                             <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                             @include('non_banking_services.dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                         </div>

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
                 $currentVal = ($formattedExpenses['marketing-expense']['total'][$yearOrMonthAsIndex]??0) / 1000000;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center justify-content-center">
                         <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden '" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                        </div>

                                    </td>
                                    @php
                                    $currentYearRepeaterIndex++;
                                    $currentYearTotal = 0;
                                    @endphp
                                    @endif
									
                 @php
                 $columnIndex++ ;
                 @endphp

                 @endforeach


             </tr>





             <tr data-repeat-formatting-decimals="0" data-repeater-style>

                 @php
                 $key ='sales-expense';
                 $currentModalId = $key.'-modal-id';
                 $currentModalTitle = __('Sales Expense (Fig In Million)') ;
                 @endphp

                 <td>
                     <div class="d-flex align-items-center  ">
                         <input value="{{ __('Sales Expenses') }}" disabled class="form-control min-250-w text-left " type="text">
                         <div>
                             <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                             @include('non_banking_services.dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])
                         </div>

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
                 $currentVal = ($formattedExpenses['sales-expense']['total'][$yearOrMonthAsIndex]??0) / 1000000;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center justify-content-center">
                         <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden '" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                        </div>

                                    </td>
                                    @php
                                    $currentYearRepeaterIndex++;
                                    $currentYearTotal = 0;
                                    @endphp
                                    @endif
									
                 @php
                 $columnIndex++ ;
                 @endphp

                 @endforeach


             </tr>


             <tr data-repeat-formatting-decimals="0" data-repeater-style>
                 @php
                 $key ='general-expense';
                 $currentModalId = $key.'-modal-id';
                 $currentModalTitle = __('General Expenses (Fig In Million)') ;
                 @endphp

                 <td>
                     <div class="d-flex align-items-center  ">
                         <input value="{{ __('General Expenses') }}" disabled class="form-control min-250-w text-left " type="text">
                         <div>
                             <i data-toggle="modal" data-target="#{{ $currentModalId }}" class="flaticon2-information kt-font-primary exclude-icon ml-2 cursor-pointer "></i>
                             @include('non_banking_services.dashboard._expense-modal',['currentModalId'=>$currentModalId,'modalTitle'=>$currentModalTitle,'modalData'=>$formattedExpenses[$key] ?? []])

                         </div>
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
                 $currentVal = ($formattedExpenses['general-expense']['total'][$yearOrMonthAsIndex]??0) / 1000000;
                 @endphp
                 <td>
                     <div class="d-flex align-items-center justify-content-center">
                         <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="2" :currentVal="$currentVal" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden '" :is-percentage="false" :mark="' '" :name="''" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
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
                                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="1" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse exclude-from-trigger-change-when-repeat expandable-amount-input '" :classes="'exclude-from-total year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$yearOrMonthAsIndex"></x-repeat-right-dot-inputs>
                                        </div>

                                    </td>
                                    @php
                                    $currentYearRepeaterIndex++;
                                    $currentYearTotal = 0;
                                    @endphp
                                    @endif
									
                 @php
                 $columnIndex++ ;
                 @endphp

                 @endforeach


             </tr>














         </x-slot>




     </x-tables.repeater-table>
