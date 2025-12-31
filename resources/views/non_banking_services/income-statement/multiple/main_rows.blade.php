<tr 
@if($hasParent)
data-is-main-row
class="hidden"
@else
data-is-parent-row
@endif 
 data-repeat-formatting-decimals="0" 
 data-main-index="{{ $tableIndex }}"
 data-repeater-style>
    <td class="fixed-column
	
	">
        @if($hasSubItems)
        <a href="#" class="btn
		@if($hasParent)
			ml-4
		@endif
	
		 btn-1-bg btn-sm btn-brand add-btn-class  text-center 
		 @if($hasParent)
		 add-btn-js
		 @else
		 add-btn-parent-js
		 @endif
		 
		 ">
            <i class="fas fa-angle-double-down expand-icon   exclude-icon"></i>
        </a>
        @endif
    </td>
    <td class="fixed-column">
        <div class="d-flex
		@if(!$hasParent)
		ml--2
		@endif
		 align-items-center justify-content-center flex-column name-max-width-class" style="gap:10px">
            @php
            $currentIndex = 0 ;
            @endphp
            @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
			@php
				$currentTitle = $mainItemArr['options']['title']??$mainItemId;
				if($hasParent){
					$currentTitle = $study->getName() . ' [ ' .  $currentTitle .' ]';
				}
			@endphp
            <div class="input-hidden-parent">
                <input readonly data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control exclude-from-collapse text-left copy-value-to-his-input-hidden 

						 						  			  repeat-to-right-input-formatted  exclude-from-collapse custom-input-string-width input-text-left  text-left" type="text" value="{{ $currentTitle }}" data-column-index="-1">
            </div>
            @php
            $currentIndex++;
            @endphp
            @endforeach

        </div>
    </td>

    @php
    $currentYearRepeaterIndex = 0 ;
    @endphp

    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)

    <td data-column-index="{{ $dateAsIndex }}">

        <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">
            @php
            $currentIndex = 0 ;
            @endphp
            @foreach($currentTableData['main_items'] as $mainItemTitle => $mainItemArr)
            @php
            $isPercentage = $mainItemArr['options']['is-percentage']??$defaultClasses[$currentIndex]['is-percentage'] ;
            @endphp
            @if($isPercentage)
            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                <div class="input-hidden-parent">
                    <input disabled data-number-of-decimals="2" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

											  expandable-percentage-input  			  repeat-to-right-input-formatted   " type="text" value="{{ number_format($mainItemArr['data'][$dateAsIndex]??0,2) }}" data-column-index="{{ $dateAsIndex }}">
                    <input data-number-of-decimals="2" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $mainItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                </div>
                <span class="ml-2 currency-class">%</span>
            </div>
            @else
            <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                <div class="input-hidden-parent">
                    <input disabled data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($mainItemArr['data'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                    <input data-number-of-decimals="0" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $mainItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                </div>
            </div>
            @endif


            {{-- <x-repeat-right-dot-inputs :readonly="false" :classes="$mainItemArr['options']['classes']??$defaultClasses[$currentIndex]['classes']" data-group-index="{{ $currentIndex ==0   ? $currentYearRepeaterIndex : -1 }}" :formattedInputClasses="$mainItemArr['options']['formatted-input-classes']??$defaultClasses[$currentIndex]['formatted-input-classes']" :removeThreeDots="true" :removeCurrency="true" :mark="$isPercentage ? '%' : ''" :is-number="true" :removeThreeDotsClass="true" :numberFormatDecimals="$mainItemArr['options']['number-format-decimals']??$defaultClasses[$currentIndex]['number-format-decimals']" :currentVal="$mainItemArr['data'][$dateAsIndex]??0" :is-percentage="$isPercentage" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
            @php
            $currentIndex++;
            @endphp
            @endforeach

        </div>




    </td>

    @php
    $currentMonthNumber = explode('-',$dateAsString)[1];
    $currentYear= explode('-',$dateAsString)[0];
    @endphp
    @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
    <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
        @php
        $currentIndex =0 ;
        @endphp
        @foreach($currentTableData['main_items'] as $mainItemId => $mainItemArr)
        <div class="d-flex align-items-center justify-content-center">
            @if($currentIndex == 0)
            <div class="

								form-group 
								three-dots-parent
 

								">
                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                    <div class="input-hidden-parent">
                        <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

							  expandable-amount-input 			  repeat-to-right-input-formatted  exclude-from-collapse repeat-group-year " type="text" value="{{ number_format($mainItemArr['year_total'][$dateAsIndex]??0,0) }}" data-column-index="{{ $dateAsIndex }}">
                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  year-repeater-index-{{ $currentYearRepeaterIndex }}  exclude-from-collapse" value="{{ $mainItemArr['year_total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                    </div>

                    <span class="ml-2 currency-class">
                        EGP
                    </span>

                </div>




            </div>
            @else
            <div class="

											form-group 
											three-dots-parent
						

								">
                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                    <div class="input-hidden-parent">
                        <input disabled data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

								  expandable-percentage-input  			  repeat-to-right-input-formatted  exclude-from-collapse repeat-group-year " type="text" value="{{ number_format($mainItemArr['year_total'][$dateAsIndex]??0,2) }}" data-column-index="{{ $dateAsIndex }}">
                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  year-repeater-index-{{ $currentYearRepeaterIndex }}  exclude-from-collapse" value="{{ $mainItemArr['year_total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                    </div>
                    <span class="ml-2">%</span>
                </div>



                <i class="fa fa-ellipsis-h pull-left repeat-to-right row-repeater-icon visibility-hidden"></i>

            </div>
            @endif
            {{-- <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="''" :currentVal="$mainItemArr['total'][$dateAsIndex]??0 " :formattedInputClasses="'exclude-from-collapse repeat-group-year'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .' exclude-from-collapse'" :is-percentage="$mainItemArr['options']['is-percentage']??$defaultClasses[$currentIndex]['is-percentage']" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs> --}}
        </div>
        @php
        $currentIndex++;
        @endphp
        @endforeach



    </td>
    @php
    $currentYearRepeaterIndex++;
    @endphp
    @endif

    @endforeach

</tr>
