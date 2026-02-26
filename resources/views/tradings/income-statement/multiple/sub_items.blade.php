<tr class="hidden" data-is-sub-row data-main-index="{{ $tableIndex }}" data-repeat-formatting-decimals="0">
    <td class="fixed-column">
    </td>
    <td class="fixed-column">
        <div class="d-flex align-items-center justify-content-center flex-column ml-5" style="gap:10px">

            <div class="


									">
                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                    <div class="input-hidden-parent">
                        <input style="text-align:left !important;" readonly data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

										  expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-string-width  input-text-left  " type="text" value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  " value="{{ $subItemArr['options']['title']??$subItemId }}" data-column-index="-1">
                    </div>
                    <span class="ml-2 currency-class"> </span>
                </div>

            </div>

        </div>
    </td>


    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
    <td data-column-index="{{ $dateAsIndex }}">

        <div data-column-index="{{ $dateAsIndex }}" class="d-flex align-items-center justify-content-center flex-column" style="gap:10px">

            <div class="">
                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                    <div class="input-hidden-parent">
                        <input 
						@if(!isset($enabled))
						disabled 
						@endif
						data-number-of-decimals="0" readonly="" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control copy-value-to-his-input-hidden 

			 							 expandable-amount-input 			  repeat-to-right-input-formatted  custom-input-numeric-width  " type="text" value="{{ number_format($subItemArr['data'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  repeater-with-collapse-input" value="{{ $subItemArr['data'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                    </div>
                    <span class="ml-2 currency-class"> </span>
                </div>

            </div>
        </div>
    </td>
    @php
    $currentMonthNumber = explode('-',$dateAsString)[1];
    $currentYear= explode('-',$dateAsString)[0];
    @endphp

    @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
    <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
        <div class="d-flex align-items-center justify-content-center">

            <div class="

 

								">
                <div class="input-group input-group-sm align-items-center justify-content-center flex-nowrap">
                    <div class="input-hidden-parent">
                        <input
						
						@if(!isset($enabled))
						disabled 
						readonly
						@endif
						   data-number-of-decimals="0" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" class="form-control total-td-formatted copy-value-to-his-input-hidden 

										  expandable-amount-input 			  repeat-to-right-input-formatted   " type="text" value="{{ number_format($subItemArr['year_total'][$dateAsIndex]??0) }}" data-column-index="{{ $dateAsIndex }}">
                        <input data-number-of-decimals="0" type="hidden" data-name="" class="repeat-to-right-input-hidden input-hidden-with-name  total-td" value="{{ $subItemArr['year_total'][$dateAsIndex]??0 }}" data-column-index="{{ $dateAsIndex }}">
                    </div>
                    <span class="ml-2 currency-class"> </span>
                </div>

            </div>

        </div>

    </td>
    @php
    $currentYearRepeaterIndex++;
    @endphp
    @endif

    @endforeach


</tr>
