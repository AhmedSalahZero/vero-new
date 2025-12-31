@php
	$removeMonths = isset($remove_months) ;
	$allowExistingCount = !isset($allow_existing) ;
	
@endphp
@foreach(count($departments)? $departments : [null] as $department)

@php
$tableId = 'departments';
$cardId = $tableId;
$repeaterId = $tableId.'_repeater';
@endphp


       
        @include('non_banking_services.manpower._input-hidden')
        {{-- start of fixed monthly repeating amount --}}
		<div class="row">
		<div class="col-md-10 mt-4 mb-3">
                        <div class="d-flex align-items-center ">
                            <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5" style=""> {{ $department->getName() . ' - ' . $department->getExpenseTypeName() }} </h3>
                        </div>
                    </div>
		</div>

        <input type="hidden" name="tableIds[]" value="{{ $tableId }}">

        <x-tables.repeater-table :hideByDefault="false" :addExpenseType="true" :initEmpty="false" :removeActionBtn="true" :first-element-deletable="false" :font-size-class="'font-14px'" :department="$department" :append-save-or-back-btn="false" :repeater-with-select2="false" :parentClass="''" :tableName="$department ? $tableId.$department->id : $tableId " :repeaterId="$repeaterId" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
            <x-slot name="ths">
                {{-- <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down first-column-th-class" :title="__('')"></x-tables.repeater-table-th> --}}
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class="  header-border-down " :title="__('Position')"></x-tables.repeater-table-th>
				@if($allowExistingCount)
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down " :title="__('Existing <br> Count')"></x-tables.repeater-table-th>
				@endif
                <x-tables.repeater-table-th :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down " :title="__('Monthly Net <br> Salary')"></x-tables.repeater-table-th>
                @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)
                @php
				
                $currentMonthNumber =  explode('-',$dateAsString)[1];
                $currentYear= explode('-',$dateAsString)[0];
                $currentYearRepeaterIndex = 0 ;
                $title = $removeMonths ? $dateAsString :  dateFormatting($dateAsString, 'M\' Y') ;
				@endphp
		
                <x-tables.repeater-table-th data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" interval-class header-border-down " :title=" $title . ' <br> ' .__('Hiring #')"></x-tables.repeater-table-th>
                @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last )
				@if(!$removeMonths)
                <x-tables.repeater-table-th :icon="true" data-column-index="{{ $dateAsIndex }}" :font-size-class="'font-14px'" class=" tenor-selector-class header-border-down {{ 'year-repeater-index-'.$currentYearRepeaterIndex }} collapse-before-me exclude-from-collapse" :title="__('Total Yr.').' <br> '. $currentYear"></x-tables.repeater-table-th>
				
				@endif 
                @php
                $currentYearRepeaterIndex ++;
                @endphp
                @endif

                @endforeach
            </x-slot>
			@php
				$branchId = isset($branchId) ? $branchId : null;
			@endphp
            <x-slot name="trs">
                @foreach($department ? $department->positions : [] as $rowIndex=>$position )
                @php
                $manpower = $position->manpowers->where('type',$manpowerType)->where('study_id',$study->id)->where('branch_id',$branchId)->first();
                $positionId = $position->id;
                @endphp
                <tr data-repeat-formatting-decimals="2" data-repeater-style>



                    <input type="hidden" name="manpowers[{{ $positionId }}][id]" value="{{  $position->id}}">
                    <input type="hidden" name="manpowers[{{ $positionId }}][study_id]" value="{{ $study->id }}">

                    <td>
                        <div class="max-w-250">
                            <input readonly value="{{  $position->getName()  }}" class="form-control text-left mt-2" type="text">

                        </div>
                    </td>
					@if($allowExistingCount)
                    <td>


                        <div class="">
                            <input value="{{  $manpower ? $manpower->getExistingCount():0 }}" name="manpowers[{{ $positionId }}][existing_count]" class="form-control expandable-percentage-input text-left mt-2" type="text">

                        </div>
                    </td>
					@endif
                    <td>


                        <div class="">
                            <input value="{{ $manpower ? $manpower->getMonthlyNetSalary() : 0 }}" name="manpowers[{{ $positionId }}][monthly_net_salary]" class="form-control expandable-amount-input text-left mt-2" type="text">
                        </div>
                    </td>
                    @php
                    $columnIndex = 0 ;
                    $currentVal = 0 ;
                    $currentYearRepeaterIndex = 0 ;
                    $currentYearTotal = 0 ;
                    @endphp

                    @foreach($studyMonthsForViews as $dateAsIndex=>$dateAsString)

                    <td data-column-index="{{ $dateAsIndex }}">
                        <div class="d-flex align-items-center justify-content-center">
                            @php
                            $name = "manpowers[$positionId][hiring_counts][$dateAsIndex]";
                            $currentValue = $manpower ? $manpower->getHiringCountsAtDateIndex($dateAsIndex) : 0;
                            $currentYearTotal+=$currentValue;
                            @endphp
                            <x-repeat-right-dot-inputs :formattedInputClasses="$currentValue > 0 ? 'bg-manpower' :''" :number-format-decimals="0" :mark="' '" :currentVal="$currentValue" data-group-index="{{ $currentYearRepeaterIndex }}" :classes="'repeater-with-collapse-input only-greater-than-or-equal-zero-allowed  '  " :is-percentage="true" :name="$name" :columnIndex="$columnIndex"></x-repeat-right-dot-inputs>
                        </div>
                    </td>
                    @php
                    $currentMonthNumber = explode('-',$dateAsString)[1];
                    $currentYear= explode('-',$dateAsString)[0];
                    @endphp


                    @if($financialYearEndMonthNumber == $currentMonthNumber || $loop->last)
						@if(!$removeMonths)
                    <td data-column-index="{{ $dateAsIndex }}" class="exclude-from-collapse">
                        <div class="d-flex align-items-center justify-content-center">
                            <x-repeat-right-dot-inputs :readonly="true" :removeThreeDots="true" :number-format-decimals="0" :mark="' '" :currentVal="$currentYearTotal " :formattedInputClasses="'exclude-from-collapse'" :classes="'year-repeater-index-'.$currentYearRepeaterIndex.' ' .'only-greater-than-or-equal-zero-allowed exclude-from-collapse'" :is-percentage="true" :name="''" :columnIndex="$dateAsIndex"></x-repeat-right-dot-inputs>
                            @php
                            $currentYearTotal = 0 ;
                            @endphp
                        </div>

                    </td>
					@endif
                    @php
                    $currentYearRepeaterIndex++;
                    @endphp
                    @endif

                    @php
                    $columnIndex++;
                    @endphp
                    @endforeach



                </tr>
                @endforeach
            </x-slot>





        </x-tables.repeater-table>






   

@endforeach
