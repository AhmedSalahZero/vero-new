	@php
$tableClasses =  'col-md-12 overflow-scroll';
@endphp

<x-tables.repeater-table :scrollable="true" :table-class="$tableClasses" :removeActionBtn="true" :removeRepeater="true" :initialJs="false" :repeater-with-select2="true" :canAddNewItem="false" :parentClass="'js-remove-hidden'" :hide-add-btn="true" :tableName="''" :repeaterId="''" :relationName="'food'" :isRepeater="$isRepeater=!(isset($removeRepeater) && $removeRepeater)">
    <x-slot name="ths">
        <x-tables.repeater-table-th class="  header-border-down max-column-th-class" :title="__('Item')"></x-tables.repeater-table-th>
		@foreach([
			'Revenues Multiplier',
			'EBITDA Multiplier',
			'Shareholder Equity Multiplier',
		] as $columnName)
        <x-tables.repeater-table-th class=" interval-class header-border-down " :title="$columnName"></x-tables.repeater-table-th>
		@endforeach 

    </x-slot>
    <x-slot name="trs">

        <tr data-repeat-formatting-decimals="0" data-repeater-style>
            @php
            $key ='ebit';
            $currentModalId = $key.'-modal-id';
            $currentModalTitle = __('+/- EBIT (Fig In Million)') ;
            @endphp
            <td>
                <div class="d-flex align-items-center ">
                    <input value="{{ __('Multiplier Value') }}" disabled class="form-control text-left min-w-300" type="text">
                </div>
            </td>


       
         @foreach([
			'Revenues Multiplier',
			'EBITDA Multiplier',
			'Shareholder Equity Multiplier',
		] as $columnName)
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <x-repeat-right-dot-inputs :disabled="true" :removeThreeDotsClass="true" :removeThreeDots="true" :number-format-decimals="0" :currentVal="0" :classes="'only-greater-than-or-equal-zero-allowed total-loans-hidden js-recalculate-equity-funding-value'" :is-percentage="false" :mark="' '" :name="''" :columnIndex="-1"></x-repeat-right-dot-inputs>
                </div>
            </td>
			@endforeach
            

    


        </tr>
		

    </x-slot>




</x-tables.repeater-table>
