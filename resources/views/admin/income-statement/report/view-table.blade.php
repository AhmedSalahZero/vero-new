@php
$tableId = 'kt_table_1';
@endphp
<style>
    html body table tr.edit-info-row[data-sub-item-name][data-percentage-value][data-is-quantity="1"] td {
        background-color: antiquewhite !important;
    }
	.custom-modal-w-h{
		min-width:93% !important;
	}
	.dropdown-menu{
		max-width:340px !important;
		min-width:340px !important;
	}
    .checkboxes-vat {
        margin-top: 15px;
    }
	#kt_table_1_filter{
		display:none;
	}

    .max-w-actions {
        width: 55px !important;
        max-width: 55px !important;
        min-width: 55px !important;
    }

    th:first-of-type,
    td:first-of-type {
        width: 135px !important;
        max-width: 135px !important;
        min-width: 135px !important;
    }

    .financial-income-or-expense-id {
        justify-content: flex-start !important;
    }

    td.editable-date,
    th.editable-date,
    input.editable-date {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        overflow: hidden;
    }

    td.editable-text,
    th.editable-text,
    .is-name-cell {
        width: 285px !important;
        min-width: 285px !important;
        max-width: 285px !important;
    }

    th.total-row,
    td.total-row {
        width: 110px !important;
        min-width: 110px !important;
        max-width: 110px !important;
    }

    .blured-item {
        padding-right: 0 !important;
    }

    .basis-100 {
        flex-basis: 100%;
    }

    .checkboxes-for-quantity {
        display: flex;
    }

    .checkboxes-for-quantity>* {
        margin-right: 20px;
    }

    .pl-25 {
        padding-left: 17px;
        padding-right: 17px;
    }

    .how-many-item {
        flex-wrap: wrap !important;
        flex-direction: column;
        justify-content: flex-start !important;
        align-items: flex-start !important;
    }

    .flex-checkboxes {
        width: 100% !important;
    }

    .w-160px {
        width: 160px !important
    }

    .bootstrap-select {
        width: 100% !important;
    }

    .margin-left-auto {
        margin-left: auto
    }

    .width-66 {
        width: 75% !important;
    }

    .repeating-fixed-sub {
        width: 100%
    }

    .border-bottom-popup {
        border-bottom: 1px solid #d6d6d6;
        padding-bottom: 20px;
    }

    .flex-self-start {
        align-self: flex-start;
    }

    .flex-checkboxes {
        margin-top: 1rem;
        flex: 1;
        width: 100% !important;
    }

    .flex-checkboxes>div:not(.modal-footer) {
        width: 100%;
        width: 100% !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .custom-divs-class {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
    }

    /* table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control::before, table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control::before,
    .dataTables_wrapper table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td:first-child::before
    {
        content:none ;
    } */
    .modal-backdrop {
        display: none !important;
    }

    .modal-content {
        min-width: 600px !important;
    }

    .form-check {
        padding-left: 0 !important;

    }

    .main-with-no-child {
        background-color: white !important;
        font-weight: bold;
    }

    .is-sub-row td.sub-text-bg {
        background-color: #aedbed !important;
        color: black !important;

    }

    .sub-numeric-bg {
        text-align: center;

    }

    .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        border: 1.5px solid white !important;
        background-color: #0e96cd !important;
        color: white !important;


        background-color: #E2EFFE !important;
        color: black !important
    }

    th.dtfc-fixed-left {
        background-color: #074FA4 !important;
        color: white !important;
    }

    .header-tr,
        {
        background-color: #046187 !important;
    }

    .dt-buttons.btn-group {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .is-sales-rate,
    .is-sales-rate td,
    .is-sales-growth-rate,
    .is-sales-growth-rate td {
        background-color: #046187 !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTable th,
    .dataTables_wrapper .dataTable td {
        font-weight: bold;
        color: black;
    }

    a[data-toggle="modal"] {
        color: #046187 !important;
    }

    a[data-toggle="modal"].text-white {
        color: white !important;
    }

    .btn-border-radius {
        border-radius: 10px !important;
    }

</style>
@csrf
<input type="hidden" id="is-actual-table" value="{{ (int) in_array('actual-report',Request()->segments()) }}">
<input type="hidden" id="editable-by-btn" value="1">
@if(in_array($reportType,['adjusted']))
<input type="hidden" id="fixed-column-number" value="2">
@else
<input type="hidden" id="fixed-column-number" value="3">
@endif
<input type="hidden" id="sub-item-type" value="{{ $reportType }}">
<input type="hidden" id="income_statement_id" value="{{ $incomeStatement->id }}">

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" >
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Upload Actual Data For ')  . $incomeStatement->getName() }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span >&times;</span>
                </button>
            </div>
            <form enctype="multipart/form-data" action="{{ route('admin.import.excel.template',['company'=>$company->id,'incomeStatement'=>$incomeStatement->id]) }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="upload__excel " style="height:100px;display:flex;align-items:center;">

                        <input name="excel_file" type="file" value="{{ __('Choose File') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="table-custom-container position-relative  ">
    <input type="hidden" value="{{ $incomeStatement->id }}" id="model-id">
    <input type="hidden" id="income-statement-duration-type" value="{{ $incomeStatement->duration_type ?? '' }}">

    <input type="hidden" id="cost-of-goods-id" value="{{ \App\Models\IncomeStatementItem::COST_OF_GOODS_ID }}">
    <input type="hidden" id="sales-growth-rate-id" value="{{ \App\Models\IncomeStatementItem::SALES_GROWTH_RATE_ID }}">
    <input type="hidden" id="sales-revenue-id" value="{{ \App\Models\IncomeStatementItem::SALES_REVENUE_ID }}">
    <input type="hidden" id="gross-profit-id" value="{{ \App\Models\IncomeStatementItem::GROSS_PROFIT_ID }}">
    <input type="hidden" id="market-expenses-id" value="{{ \App\Models\IncomeStatementItem::MARKET_EXPENSES_ID }}">
    <input type="hidden" id="sales-and-distribution-expenses-id" value="{{ \App\Models\IncomeStatementItem::SALES_AND_DISTRIBUTION_EXPENSES_ID }}">
    <input type="hidden" id="general-expenses-id" value="{{ \App\Models\IncomeStatementItem::GENERAL_EXPENSES_ID }}">
    <input type="hidden" id="earning-before-interest-taxes-depreciation-amortization-id" value="{{ \App\Models\IncomeStatementItem::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID }}">
    <input type="hidden" id="earning-before-interest-taxes-id" value="{{ \App\Models\IncomeStatementItem::EARNING_BEFORE_INTEREST_TAXES_ID }}">
    <input type="hidden" id="financial-income-or-expenses-id" value="{{ \App\Models\IncomeStatementItem::FINANCIAL_INCOME_OR_EXPENSE_ID }}">
    <input type="hidden" id="earning-before-taxes-id" value="{{ \App\Models\IncomeStatementItem::EARNING_BEFORE_TAXES_ID }}">
    <input type="hidden" id="corporate-taxes-id" value="{{ \App\Models\IncomeStatementItem::CORPORATE_TAXES_ID }}">
    <input type="hidden" id="net-profit-id" value="{{ \App\Models\IncomeStatementItem::NET_PROFIT_ID }}">
    <input type="hidden" id="sales-rate-maps" value="{{ json_encode(\App\Models\IncomeStatementItem::salesRateMap()) }}">
    <input type="hidden" id="vat-rates-maps" value="{{ json_encode(\App\Models\IncomeStatementItem::vatRatesMap()) }}">
    <script>
		let datesWithIsActual = @json($actualDates);
        let sales_rates_maps = document.getElementById('sales-rate-maps').value;
        const sales_rate_maps = JSON.parse(sales_rates_maps);
        let opens = [];
        let globalTable = null;
    
        let lastInputValue = 0;
        let salesRevenueModalTdData = {}
        let nonRepeatingModalTdData = {}
        const vatRateMaps = JSON.parse(document.getElementById('vat-rates-maps').value);

      
        const domElements = {
            salesRevenueId: document.getElementById('sales-revenue-id').value
            , salesGrowthRateId: document.getElementById('sales-growth-rate-id').value
            , growthProfitId: document.getElementById('gross-profit-id').value
            , corporateTaxesId: document.getElementById('corporate-taxes-id').value
            , costOfGoodsId: document.getElementById('cost-of-goods-id').value
            , financialIncomeOrExpensesId: document.getElementById('financial-income-or-expenses-id').value
            , marketExpensesId: document.getElementById('market-expenses-id').value
            , generalExpensesId: document.getElementById('general-expenses-id').value
            , salesAndDistributionExpensesId: document.getElementById('sales-and-distribution-expenses-id').value
            , earningBeforeInterestTaxesId: document.getElementById('earning-before-interest-taxes-id').value
            , earningBeforeInterestTaxesDepreciationAmor: document.getElementById('earning-before-interest-taxes-depreciation-amortization-id').value
            , earningBeforeTaxesId: document.getElementById('earning-before-taxes-id').value
            , netProfitId: document.getElementById('net-profit-id').value



        }
		
        const vars = {
            subItemType: document.getElementById('sub-item-type').value
        }

    </script>



    <x-tables.basic-view :redirect-route="route('admin.view.financial.statement', getCurrentCompanyId())" :save-and-return="true" :form-id="'store-report-form-id'" :wrap-with-form="true" :form-action="route('admin.store.income.statement.report',['company'=>getCurrentCompanyId()])" class="position-relative table-with-two-subrows main-table-class" id="{{ $tableId }}">
        <x-slot name="filter">
            @include('admin.income-statement.report.filter' , [
            'type'=>'filter'
            ])
        </x-slot>

        <x-slot name="export">
            @include('admin.income-statement.report.export' , [
            'type'=>'export'
            ])
        </x-slot>


        <x-slot name="headerTr">
            <input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
            <input type="text" style="height:0;overflow:hidden;width:0;background-color:transparent;border:none;color:transparent;" id="pdf_only" name="opens">
            <input type="text" style="height:0;overflow:hidden;width:0;background-color:transparent;border:none;color:transparent;" id="export_type" name="dynamic_rows_shown">
            <tr class="header-tr " data-model-name="{{ $modelName }}">
                <th class="view-table-th header-th trigger-child-row-1 expand-all is-open-parent text-nowrap">
                    {{ __('Expand') }}
                    <span>+</span>
                </th>

                <th style="max-w-actions" class="view-table-th header-th" data-db-column-name="id" data-is-relation="0" class="header-th" data-is-json="0">
                    {{ __('Actions') }}
                </th>
                <th class="view-table-th header-th" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="BussinessLineName" data-is-relation="1" class="header-th" data-is-json="0">
                    {{ __('Name') }}
                    {{-- {!!  !!} --}}
                </th>
                <input type="hidden" name="dates" data-date-formatted="{{ json_encode((collect($incomeStatement->getIntervalFormatted())->map(function($date){return formatDateForView($date);})->toArray())) }}" data-formatted="{{ json_encode(($incomeStatement->getIntervalFormatted())) }}" value="{{ json_encode(array_keys($incomeStatement->getIntervalFormatted())) }}" id="dates">
                @foreach($incomeStatement->getIntervalFormatted() as $dateAsIndex=>$dateAsString)
				
                <th data-is-actual="{{ (int)isActualDate($dateAsString) }}" data-date="{{ $dateAsIndex }}"  class="view-table-th header-th text-wrap" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="ServiceCategory" data-is-relation="1" class="header-th" data-is-json="0">
                    {{ formatDateForView($dateAsString) }}
                    @if(isActualDate($dateAsString) && $reportType != 'forecast')
                    <br>({{ __('Actual') }})
                    @elseif($reportType != 'forecast')
                    <br>({{ __('Forecast') }})
                    @endif
                    @if((int)isActualDate($dateAsString))
                    <div class="is-actual-dates" data-date="{{ $dateAsIndex }}" style="visibility:hidden"></div>
                    @endif
                </th>


                @endforeach
                <th class="view-table-th header-th">
                    {{ __('Total') }}
                </th>

                <div hidden type="hidden" id="cols-counter" data-value="0"> </div>
                <script>
                    countHeadersInPage('.main-table-class th', '#cols-counter');

                </script>

            </tr>

        </x-slot>

        <x-slot name="js">

            <script>
                let inAddOrEditModal = false;
                window.addEventListener('DOMContentLoaded', function() {
					
                    (function($) {
						// console.log('DOMContentLoaded')
                        window.addEventListener('scroll', function() {
							// console.log('scroll')
                            const top = window.scrollY > 140 ? window.scrollY : 140;

                            $('.arrow-nav').css('top', top + 'px')
                        })
                        if ($('.kt-portlet__body').length) {

                            $('.kt-portlet__body').append(`
								<i class="cursor-pointer text-dark arrow-nav  arrow-left fa fa-arrow-left"></i>
								<i class="cursor-pointer text-dark arrow-nav arrow-right fa  fa-arrow-right"></i>
								`)

						
                            $(document).on('click', '.arrow-nav', function() {
								// console.log('arrow nave');
                                const scrollLeftOfTableBody = document.querySelector('.kt-portlet__body').scrollLeft
                                const scrollByUnit = 50
                                if (this.classList.contains('arrow-right')) {
                                    document.querySelector('.dataTables_scrollBody').scrollLeft += scrollByUnit

                                } else {
                                    document.querySelector('.dataTables_scrollBody').scrollLeft -= scrollByUnit

                                }
                            })

                        }
                        $(document).on('click', '.import-modal-class', function() {
						// console.log('import-modal-class')
                            $('#exampleModalCenter').modal('show')

                        })
                        $(document).on('change', '.trim-when-key-up', function() {
							// console.log('trim-when-key-up')
                            $(this).val($(this).val().trim())
                        })
                      //  $(document).on("hidden.bs.modal", '.modal', function(e) {
						//	// console.log('hidden.bs.modal')
                        //    if ($('.modal:visible').length) {
                        //        $('body').addClass('modal-open');
                       //     }
                       // });
                        $(document).on('click', '.repeat-row', function() {
// console.log('repeat now 1')
                            const parentQuery = this.getAttribute('data-parent-query')
                            const columnIndex = this.getAttribute('data-column-index')
                            const rowIndex = this.getAttribute('data-row-index')
                            const parent = $(this).parent()
                            const tr = $(this).closest(parentQuery)

                            const currentVal = filterNumericUserInput(parent.find('input[type="text"]').val(), false)
                            tr.find('input[type="text"]').not('[readonly]').each(function(i, inputText) {
                                var date = $(inputText).attr('data-date')
                                if (Date.parse(date) > Date.parse(columnIndex)) {
                                    $(inputText).val(currentVal).trigger('blur')
                                }
                            })
                        })

                        $(document).on('change', 'input[name*="is_depreciation_or_amortization"]', function() {
							// console.log('input[name*="is_depreciation_or_amortization"]')
                            const val = $(this).is(':checked');
							const modal = $(this).closest('.modal'); 
                            if (val) {
                                modal.find('.collection-policy').addClass('d-none').removeClass('d-flex')
                                modal.find('.collection-policy input').prop('readonly', true)
                                modal.find('.collection-policy select').prop('disabled', true)
                                modal.find('.checkboxes-vat').addClass('d-none').removeClass('d-flex')
                                modal.find('.checkboxes-vat input').prop('readonly', true)
                                modal.find('.checkboxes-vat select').prop('disabled', true)
                            } else {
                                modal.find('.collection-policy').removeClass('d-none').addClass('d-flex')
                                modal.find('.collection-policy input').prop('readonly', false)
                                modal.find('.collection-policy select').prop('disabled', false)
                                modal.find('.checkboxes-vat').removeClass('d-none').addClass('d-flex')
                                modal.find('.checkboxes-vat input').prop('readonly', false)
                                modal.find('.checkboxes-vat select').prop('disabled', false)
                            }
                        })

                        $(document).on('change', '.collection_rate_input', function() {

                            let percentage = filterNumericUserInput($(this).val())
                            percentage = parseFloat(percentage)
                            if (percentage > 100) {
                                $(this).val(0)
                                Swal.fire({
                                    text: 'Percentage Can Not Be Greater Than 100'
                                    , icon: 'warning'
                                })
                            }
                            let total = 0;

                            $(this).closest('.collection-policy').find('.collection_rate_input').each(function(index, input) {
                                total += parseFloat(input.value ? input.value : 0)
                            })

                            if (total > 100) {
                                total = total - percentage
                                $(this).val(0)
                                Swal.fire({
                                    text: 'Total Can Not Be Greater Than 100'
                                    , icon: 'warning'
                                })

                            }
                            $(this).closest('.collection-policy').find('.collection_rate_total_class').val(number_format(total, 2) + ' %')
                        })

                        $(document).on('change', '.can-trigger-quantity-modal', function() {
							// console.log('can-trigger-quantity-modal')
                            let quantityOrPrice = $(this).val()
                            let currentIndex = $(this).closest('.how-many-item').attr('data-index')
                            currentIndex = currentIndex == undefined ? 0 : currentIndex

                            let inEditMode = $(this).attr('data-in-edit-mode')
                            let subItemName = $(this).attr('data-sub-item-name')
                            let subItemId = $(this).attr('data-sub-item-id')
                            $(this).closest('.quantity-section').find('[data-index]').attr('data-index', currentIndex)
                            let currentCheckedItem = $(this).parent().parent().find('input[name="sub_items[' + currentIndex + '][is_quantity]"]:checked')
                            if (currentCheckedItem.length >= 1) {
                                let firstCheckboxValue = currentCheckedItem[0].value

                                if (currentCheckedItem.length == 1 && firstCheckboxValue != 'value') {
                                    return;
                                }
                                let firstCheckboxTr = salesRevenueModalTdData[inEditMode][subItemName][firstCheckboxValue]
                                let secondCheckboxValue = currentCheckedItem[1] ? currentCheckedItem[1].value : null
                                let secondCheckboxTr = secondCheckboxValue ? salesRevenueModalTdData[inEditMode][subItemName][secondCheckboxValue] : null


                                let checkedItems = [firstCheckboxValue, secondCheckboxValue]
                                let autocaulationItemValue = null;
                                if (!checkedItems.includes('value')) {
                                    autocaulationItem = 'value'
                                }
                                if (!checkedItems.includes('price')) {
                                    autocaulationItem = 'price'
                                }
                                if (!checkedItems.includes('quantity')) {
                                    autocaulationItem = 'quantity'
                                }

                                let thirdCheckboxTr = salesRevenueModalTdData[inEditMode][subItemName][autocaulationItem]
                                const onlyValueChecked = currentCheckedItem.length == 1;
                                if (onlyValueChecked) //only value allowed
                                {
                                    thirdCheckboxTr = '';
                                }
                                let trs = firstCheckboxTr + secondCheckboxTr + thirdCheckboxTr
                                let quantitySection = $(this).closest('.quantity-section')

                                trs = trs.replaceAll('sub_items[0]', 'sub_items[' + currentIndex + ']')
                                trs = trs.replaceAll('data-index="0"', 'data-index="' + currentIndex + '"')
                                trs = trs.replaceAll('modal-for-quantity-0', 'modal-for-quantity-' + currentIndex)

                                quantitySection.find('tbody.append-sales-revenue-modal-table-body').empty().append(trs)
                                if (!onlyValueChecked) {

                                    quantitySection.find('tbody.append-sales-revenue-modal-table-body tr:last-of-type input').prop('readonly', true)
                                }

                                var checkedItemsAsString = '';
                                checkedItems.forEach(function(element, index) {
                                    if (index) {
                                        checkedItemsAsString += ('_' + element);
                                    } else {
                                        checkedItemsAsString += (element);
                                    }
                                })
                                const name = "sub_items[" + currentIndex + "][is_value_quantity_price]";
                                const selector = '[name="' + name + '"]';
                                $(selector).val(checkedItemsAsString);
                                if (!onlyValueChecked) {

                                    quantitySection.find('tbody.append-sales-revenue-modal-table-body tr:last-of-type i.repeat-row').remove();
                                }
                                quantitySection.find('.modal-for-quantity[data-index="' + currentIndex + '"]').attr('id', 'modal-for-quantity-' + currentIndex)
                                quantitySection.find('.modal-for-quantity[data-index="' + currentIndex + '"]').attr('data-id', 'modal-for-quantity-' + currentIndex)
                                // to update total 
							//	quantitySection.find('.modal-for-quantity tr td:nth-of-type(2) input.hidden-for-popup:first-of-type').trigger('blur')

                                //$('.hidden-for-popup').trigger('blur')
                            //    $('.modal-for-quantity').addClass('d-none').addClass('fade').removeClass('d-block')
						
                                $('.modal-for-quantity[data-index="' + currentIndex + '"][data-sub-id="'+subItemId+'"]').removeClass('fade').removeClass('d-none').addClass('d-block').modal('show')
                            } else {

                                const name = "sub_items[" + currentIndex + "][is_value_quantity_price]";
                                const selector = '[name="' + name + '"]';
                                $(selector).val('value');
                            }

                        })

                        $(document).on('change', '.can-trigger-non-repeating-modal', function() {
							// console.log('can-trigger-non-repeating-modal')
                            // return false ;

                            let currentIndex = $(this).closest('.how-many-item').attr('data-index')
                            currentIndex = currentIndex == undefined ? 0 : currentIndex
                            let inEditMode = +$(this).attr('data-in-edit-mode')
                            let subItemName = $(this).attr('data-sub-item-name')
                            let subItemId = $(this).attr('data-sub-item-id')
                            $(this).closest('.non-repeating-section').find('[data-index]').attr('data-index', currentIndex)

                            let currentCheckedItem = $(this).parent().parent().find('input[name="sub_items[' + currentIndex + '][percentage_or_fixed]"]:checked')
                            if (currentCheckedItem.length >= 1) {
                                let firstCheckboxValue = 'value'

                                let firstCheckboxTr = nonRepeatingModalTdData[inEditMode][subItemName][firstCheckboxValue];

                                let checkedItems = [firstCheckboxValue]
                                let autocaulationItemValue = null;
                                let trs = firstCheckboxTr
                                let nonRepeatingSection = $('#modal-for-non-repeating-' + currentIndex)
                                trs = trs.replaceAll('sub_items[0]', 'sub_items[' + currentIndex + ']')
                                trs = trs.replaceAll('data-index="0"', 'data-index="' + currentIndex + '"')
                                trs = trs.replaceAll('modal-for-non-repeating-0', 'modal-for-non-repeating-' + currentIndex)
                                nonRepeatingSection.find('tbody.append-non-repeating-modal-table-body').empty().append(trs)
                                nonRepeatingSection.find('.modal-for-non-repeating[data-index="' + currentIndex + '"]').attr('id', 'modal-for-non-repeating-' + currentIndex)
                                nonRepeatingSection.find('.modal-for-non-repeating[data-index="' + currentIndex + '"]').attr('data-id', 'modal-for-non-repeating-' + currentIndex)

                                // to update total 
                              //  $('.append-non-repeating-modal-table-body tr td:nth-of-type(2) input.hidden-for-popup-non-repeating:first-of-type').trigger('blur')
                         	 //      $('.modal-for-non-repeating').addClass('d-none').addClass('fade').removeClass('d-block')
                                $('.modal-for-non-repeating[data-index="' + currentIndex + '"][data-sub-id="'+subItemId+'"]').removeClass('fade').removeClass('d-none').addClass('d-block').modal('show')
                            }

                        })


                        $(document).on('change', '.only-one-checked', function() {
							// console.log('only-one-checked')
                            const parent = $(this).closest('.only-one-checked-parent')
                            parent.find('.only-one-checked').prop('checked', false)
                            parent.find('.for-only-one-checked').addClass('d-none').find('input').prop('readonly', true)
                            parent.find('.for-only-one-checked').addClass('d-none').find('select').prop('disabled', true)
                            $(this).prop('checked', true)
                            const checkBoxValue = $(this).val()
							// console.log(checkBoxValue);
                            parent.find('.for-only-one-checked[data-item="' + checkBoxValue + '"]').removeClass('d-none').find('input').prop('readonly', false)
                            parent.find('.for-only-one-checked[data-item="' + checkBoxValue + '"]').removeClass('d-none').find('select').prop('disabled', false)

                        })





                        $(document).on('change', '.only-one-checkbox', function() {
							// console.log('only-one-checkbox')
                            const parent = $(this).closest('.only-one-checkbox-parent')
                            parent.find('.only-one-checkbox').prop('checked', false)
                            $(this).prop('checked', true)
                        })

                        $(document).on('change', '.only-two-checkbox', function() {
							// console.log('only-two-checkbox')
                            const parent = $(this).closest('.only-two-checkbox-parent')
                            let currentCheckedLength = parent.find('.only-two-checkbox:checked').length
                            if (currentCheckedLength > 2) {
                                $(this).prop('checked', false)
                            }
                        })





                        $(document).on('focus', '.editable-date', function() {
							// console.log('editable-date')
                            lastInputValue = $(this).html()
                            $(this).html('<br>')
                        })

                        $(document).on('blur', '.blured-item', function() {
						// console.log('.blured-item')
                            const date = this.getAttribute('data-date')
                            const type = this.getAttribute('data-type')
                            const parentElement = this.parentElement.parentElement
                            const tbody = this.parentElement.parentElement.parentElement

                            const currentIndex = $(this).closest('[data-index]').attr('data-index')
                            const unformattedValue = this.parentElement.querySelector('input[type="text"]').value

                            const numericValue = filterNumericUserInput(unformattedValue, false)

                            $(parentElement).find('input[data-date="' + date + '"]').val(numericValue)
                            const onlyValueChecked = $(tbody).find('tr').length == 1;
                            if (onlyValueChecked) {
                                recalculateTotalForSalesRevenuePopup(parentElement);
                                return;
                            }
                            const autoCalculationRow = $(tbody).find('tr:last-of-type')

                            let equation = autoCalculationRow.attr('data-equation')
                            let numberFormatDigit = autoCalculationRow.attr('data-number-format')
                            const numberFormatDigits = autoCalculationRow.attr('data-number-format')
                            equation = equation.split(/([-+*\/])/g)
                            const firstClassName = equation[0].trim()
                            const mathOperator = equation[1].trim()
                            const secondClassName = equation[2].trim()

                            const firstVal = filterNumericUserInput($(tbody).find('.' + firstClassName + ' input[data-date="' + date + '"]').val(), false)
                            const secondVal = filterNumericUserInput($(tbody).find('.' + secondClassName + ' input[data-date="' + date + '"]').val(), false)

                            let result = secondVal == 0 && mathOperator == '/' ? 0 : firstVal + mathOperator + secondVal
                            result = eval(result)
                            $(autoCalculationRow).find('input[type="hidden"][data-date="' + date + '"]').val(result, numberFormatDigits)
                            $(autoCalculationRow).find('input[type="text"][data-date="' + date + '"]').val(number_format(result, numberFormatDigits)).trigger('change')
                            $(autoCalculationRow).find('i').remove();
                            recalculateTotalForSalesRevenuePopup(parentElement)
                        })
						
						 $(document).on('blur', '.blured-item-non-repeating', function() {
							// console.log('.blured-item-non-repeating')
                            const date = this.getAttribute('data-date')
                            const type = this.getAttribute('data-type')
                            const parentElement = this.parentElement.parentElement
                            const tbody = this.parentElement.parentElement.parentElement

                            const currentIndex = $(this).closest('[data-index]').attr('data-index')
                            const unformattedValue = this.parentElement.querySelector('input[type="text"]').value
                            const numericValue = filterNumericUserInput(unformattedValue, false)
                            $(parentElement).find('input[data-date="' + date + '"]').val(numericValue)
                                recalculateTotalForNonRepeatingPopup(parentElement);
							
                        })

                        function recalculateTotalForSalesRevenuePopup(parentElement) {
                            // total quantity and value
							// console.log('recalculateTotalForSalesRevenuePopup')
							parentElement = parentElement.closest('tbody');
                            const totalPerType = {};
                            let numberOfDigit = 0;
                            for (var type of ['quantity', 'value', 'price']) {
                                var currentRowTotal = 0;
                                if (type != 'price') {
									
									$(parentElement).find('input[type="hidden"][data-type="'+type+'"]').each(function(index, input) {
                                        currentRowTotal += parseFloat($(input).val());
                                    })
                                
                                }
                                totalPerType[type] = currentRowTotal
                                if (type == 'price') {
						
                                    currentRowTotal = totalPerType.quantity != 0 ? totalPerType.value / totalPerType.quantity : 0;

                                }
                                if (currentRowTotal < 1000) {
                                    numberOfDigit = 2;
                                }
								
                                $(parentElement).find('input.total-for-' + type).val(number_format(currentRowTotal, numberOfDigit)).trigger('change');
                                //$('input.total-for-' + type).val(number_format(currentRowTotal, numberOfDigit)).trigger('change');
                            }





                        }
						
						 function recalculateTotalForNonRepeatingPopup(parentElement) {
							
                            // total quantity and value
                            const totalPerType = {};
                            let numberOfDigit = 0;
                            for (var type of [ 'value']) {
                                var currentRowTotal = 0;
                                 $(parentElement).find('input[type="hidden"][data-type-non-repeating="' + type + '"]').each(function(index, input) {
                                        currentRowTotal += parseFloat($(input).val());
                                    })
                                totalPerType[type] = currentRowTotal
                                if (currentRowTotal < 1000) {
                                    numberOfDigit = 2;
                                }
                                $(parentElement).find('input.total-for-non-repeating-' + type).val(number_format(currentRowTotal, numberOfDigit))
								// cause slow down
								//.trigger('change');
                            }





                        }

                      
                  



                    

                        
                        function filterNumericUserInput(value, isFinancialExpense = false) {
                            if (!value) {
                                return 0;
                            }
                            value = value.replace(/(<([^>]+)>)/gi, "").replace(/,/g, "").replace(/[%]/g, '')


                            return isFinancialExpense == 1 && value > 0 ? value * -1 : value
                        }



                    

                        // Add event listener for opening and closing details
                        $(document).on('click', '.edit-btn', function() {
                            const target = $(this).attr('data-target');
                            if (target) {
								if($(target).find('.can-trigger-quantity-modal:checked:first-of-type').length){
									// console.log('from if');
                                	$(target).find('.can-trigger-quantity-modal:checked:first-of-type').trigger('change');
								}else{
									// console.log('from else');
                                $(target).find('.can-trigger-non-repeating-modal:checked:first-of-type').trigger('change');
								}
                            }


                        })
                    
						$(document).on('change','.has-collection-policy-class',function(){
							$(this).prop('checked',true)
						})
                        $(document).on('change', '.has-collection-policy-class', function() {
							// console.log('.has-collection-policy-class')
                            const hasCollectionPolicy = this.checked

                            const collectionPolicyContent = $(this).closest('.collection-policy').find('.collection-policy-content')
                            $(this).closest('.collection-policy').find('.has_collection_policy_input').val(hasCollectionPolicy ? 1 : 0)

                            if (hasCollectionPolicy) {
                                collectionPolicyContent.removeClass('d-none')
                            } else {
                                collectionPolicyContent.addClass('d-none')
                            }



                        })

                        $(document).on('click', '.can_be_percentage_or_fixed_class', function() {
							// console.log('can_be_percentage_or_fixed_class')
                            let val = $(this).val();
                            $(this).closest('.how-many-item').find('.non-repeating-fixed-sub,.repeating-fixed-sub,.percentage-sub,.cost-of-unit-sub').removeClass('d-flex').addClass('d-none');
                            $(this).closest('.how-many-item').find('.can_be_percentage_or_fixed_class').prop('checked', false);
                            $(this).prop('checked', true);
                            let classNameToShow = '.' + val.replaceAll(/[_]/g, '-') + '-sub';
                            $(this).closest('.how-many-item').find(classNameToShow).addClass('d-flex').removeClass('d-none');

                        });
                     
                        $(document).on('click', '.redirect-btn', function(e) {
							// console.log('redirect btn')
                            e.preventDefault();
                            window.location.href = $(this).data('redirect-to');
                        })
                   


                        $(document).on('click', '.trigger-child-row-1', function(e) {
							// console.log('.trigger-child-row-1')
                            const parentId = $(e.target.closest('tr')).data('model-id');
                            var parentRow = $(e.target).parent();
                            var subRows = parentRow.nextAll('tr.add-sub.maintable-1-row-class' + parentId);

                            subRows.toggleClass('d-none');
                            if (subRows.hasClass('d-none')) {
                                parentRow.find('td.trigger-child-row-1').removeClass('is-open').addClass('is-close').html('+');
                                var closedId = parentRow.attr('data-financial-statement-able-item-id')
                                const index = opens.indexOf(closedId);
                                //
                                opens.splice(index, 1);

                              
                            } else if (!subRows.length) {
                                // if parent row has no sub rows then remove + or - 
                                parentRow.find('td.trigger-child-row-1').html('×');
                            } else {
                                parentRow.find('td.trigger-child-row-1').addClass('is-open').removeClass('is-close').html('-');
                                opens.push(parentRow.attr('data-financial-statement-able-item-id'));



                            }

                        });



                        $(document).on('click', '.expand-all', function(e) {
							// console.log('expenad all')
                            e.preventDefault();
                            if ($(this).hasClass('is-open-parent')) {
                                $(this).addClass('is-close-parent').removeClass('is-open-parent')
                                $(this).find('span').html('-')

                                $('.is-main-with-sub-items .is-close').trigger('click')
                            } else {
                                $(this).addClass('is-open-parent').removeClass('is-close-parent')
                                $(this).find('span').html('+')

                                $('.is-main-with-sub-items .is-open').trigger('click')
                            }

                        })

                        "use strict";
                        var KTDatatablesDataSourceAjaxServer = function() {
                            function getFixedColumnNumbers() {
								// console.log('.getFixedColumnNumbers')
                                return $('#fixed-column-number').val()
                            }
                            var initTable1 =
                                function() {
										// console.log('.initTable1')
                                    var tableId = '#' + "{{ $tableId }}";
                                    var salesGrowthRateId = domElements.salesGrowthRateId
                                    var table = $(tableId);
                                    let data = $('#dates').val();
                                    let datesFormatted = $('#dates').attr('data-date-formatted');
                                    data = JSON.parse(data);
                                    datesFormatted = JSON.parse(datesFormatted);
                                    window['dates'] = data;
                                    window['datesFormatted'] = datesFormatted;
									// console.log('init 1')
                                    const columns = [];
                                    columns.push({
                                        data: 'id'
                                        , searchable: false
                                        , orderable: false
                                        , className: 'trigger-child-row-1 cursor-pointer sub-text-bg text-capitalize  is-close '
                                        , render: function(d, b, row) {
											// console.log('render 2')
                                            if (!row.isSubItem && row.has_sub_items) {
                                                return '+';
                                            } else if (row.isSubItem && row.pivot && row.pivot.can_be_percentage_or_fixed) {

                                                var subItemViewName = '';
                                                if (row.pivot.sub_item_type != 'actual' ) {
                                                    subItemViewName = row.pivot.percentage_or_fixed.replaceAll(/[_]/g, ' ');
                                                    if (subItemViewName == 'non repeating fixed') {
                                                        subItemViewName = 'non repeating';
                                                    }
                                                }
                                                return subItemViewName
                                            }
                                            return '';
                                        }
                                    });
                                    columns.push({
                                        render: function(d, b, row) {
											// console.log('render 3')
                                            let modelId = $('#model-id').val();
                                            if (!row.isSubItem && row.has_sub_items) {
                                                elements = `<a data-is-subitem="0"    class=" ${row.id == domElements.corporateTaxesId ? 'd-none' :'d-block' } add-btn mb-2" href="#" data-toggle="modal" data-target="#add-sub-modal${row.id}">{{ __('Add') }}</a> `;
                                                return elements;
                                            } else if (row.isSubItem || vars.subItemType == 'modified' && row.pivot) {
												if(row.pivot.financial_statement_able_item_id == domElements.corporateTaxesId){
													return '';
												}
                                                var deleteItem = vars.subItemType == 'modified' && row.pivot ? '' : `<a   class="${row.pivot.financial_statement_able_item_id == domElements.corporateTaxesId ? 'd-none' :'d-block' }  delete-btn text-white mb-2 text-danger" href="#" data-toggle="modal" data-target="#delete-sub-modal${row.pivot.financial_statement_able_item_id + convertStringToClass(row.pivot.sub_item_name) }">
													<i class="fas fa-trash-alt ${vars.subItemType =='actual' && row.pivot.exist_in_forecast  || vars.subItemType =='modified' ? 'hidden':''}"></i></a>`;
                                                return `
											<div class="d-flex align-items-center justify-content-between">
												<a  data-is-subitem="1"   class="${row.pivot.financial_statement_able_item_id == domElements.corporateTaxesId ? 'd-none' :'d-block' } edit-btn mb-2 text-white " href="#" data-toggle="modal"   data-target="#edit-sub-modal${row.pivot.financial_statement_able_item_id + convertStringToClass(row.pivot.sub_item_name) }"> 
												<i data-id="${row.pivot.financial_statement_able_item_id}" class="fa fa-pen-alt edit-modal-icon"></i>  
												</a> 
												${deleteItem}
													</div>
											`
                                            }
                                            return ``;
                                        }
                                        , data: 'id'
                                        , className: 'cursor-pointer sub-text-bg max-w-actions'
                                    , });
                                    columns.push({
                                        render: function(d, b, row) {
												// console.log('render 4')
                                            if (row.isSubItem) {
                                                return row.pivot.sub_item_name;
                                            }
                                            return row['name']

                                        }
                                        , data: 'id'
                                        , className: 'sub-text-bg  editable editable-text is-name-cell'
                                    });
                                    for (let i = 0; i < data.length; i++) {
									
                                        columns.push({
                                            render: function(d, b, row, setting) {
												// console.log('render 5')
                                                date = data[i];
                                                if (row.isSubItem && row.pivot.payload) {
                                                    var payload = JSON.parse(row.pivot.payload);
                                                    return payload[date] ? number_format(payload[date]) : 0;
                                                }

                                                if (row.main_rows && row.main_rows[0]) {
                                                    var isPercentageRow = row.is_sales_rate || row.id == salesGrowthRateId
                                                    var noDecimals = isPercentageRow ? 2 : 0;
                                                    var percentageMarket = isPercentageRow ? ' %' : '';

                                                    var autoCalculatedValue = row.main_rows[0].pivot.payload;
                                                    return autoCalculatedValue ? number_format(JSON.parse(autoCalculatedValue)[date], noDecimals) + percentageMarket : 0

                                                }
                                                return 0;

                                            }
                                            , data: 'id'
                                            , className: 'sub-numeric-bg  editable editable-date date-' + data[i]

                                        });




                                    }

                                    columns.push({
                                        render: function(d, b, row, setting) {
											// console.log('render 7')
                                            return  row.main_rows && row.main_rows[0] ? row.main_rows[0].pivot.total : 0
                                        }
                                        , data: 'id'
                                        , className: 'sub-numeric-bg  total-row'

                                    })
								
                                    const isActualTable = +$('#is-actual-table').val();
									// console.log(columns);
                                    // begin first table
                                    table.DataTable({




                                            dom: 'Bfrtip',
                                            // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                                            "ajax": {
                                                "url": "{{ $getDataRoute }}"
                                                , "type": "post"
                                                , "dataSrc": "data", // they key in the jsom response from the server where we will get our data
                                                "data": function(d) {
													// console.log('go get data')
                                                    d.search_input = $(getSearchInputSelector(tableId)).val();
                                                    d.sub_item_type = vars.subItemType
                                                    d.income_statement_id = $('#income_statement_id').val()
                                                }

                                            }
                                            , "processing": false
                                            , "scrollX": true
                                            , "scrollY": true
                                            , "ordering": false
                                            , 'paging': false
                                            , "fixedColumns": {
                                                left: getFixedColumnNumbers()
                                            }
                                            , "fixedHeader": {
                                                headerOffset: 60
                                            }
                                            , "serverSide": true
                                            , "responsive": false
                                            , "pageLength": 25
                                            , "columns": columns
                                            , columnDefs: [{
                                                targets: 0
                                                , defaultContent: 'salah'
                                                , className: 'red reset-table-width text-nowrap'
                                            }]
                                            , buttons: [{
                                                    "attr": {
                                                        'data-table-id': tableId.replace('#', ''),
                                                        // 'id':'test'
                                                    }
                                                    , "text": '<svg style="margin-right:10px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3"/><rect id="Rectangle" fill="#000000" opacity="0.3" transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) " x="11" y="2" width="2" height="12" rx="1"/><path d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/></g></svg>' + '{{ __("Upload Actual Template") }}'
                                                    , 'className': isActualTable ? 'btn btn-bold btn-secondary ml-2 filter-table-btn  flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away import-modal-class' : 'd-none'

                                                }
                                                , {
                                                    "attr": {
                                                        'data-table-id': tableId.replace('#', ''),
                                                        // 'id':'test'
                                                    }
                                                    , "text": '<span style="margin-right:10px;position:relative" class="svg-icon kt-svg-icon svg-icon-2x"><!--begin::Svg Icon | path:/var/www/preview.keenthemes.com/metronic/releases/2021-05-14-112058/theme/html/demo8/dist/../src/media/svg/icons/Files/Import.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"/><rect fill="#000000" opacity="0.3" transform="translate(12.000000, 7.000000) rotate(-180.000000) translate(-12.000000, -7.000000) " x="11" y="1" width="2" height="12" rx="1"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/><path d="M14.2928932,10.2928932 C14.6834175,9.90236893 15.3165825,9.90236893 15.7071068,10.2928932 C16.0976311,10.6834175 16.0976311,11.3165825 15.7071068,11.7071068 L12.7071068,14.7071068 C12.3165825,15.0976311 11.6834175,15.0976311 11.2928932,14.7071068 L8.29289322,11.7071068 C7.90236893,11.3165825 7.90236893,10.6834175 8.29289322,10.2928932 C8.68341751,9.90236893 9.31658249,9.90236893 9.70710678,10.2928932 L12,12.5857864 L14.2928932,10.2928932 Z" fill="#000000" fill-rule="nonzero"/></g></svg></span>' + '{{ __("Download Actual Template") }}'
                                                    , 'className': isActualTable ? 'btn btn-bold btn-secondary ml-2 filter-table-btn  flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away' : 'd-none'
                                                    , "action": function() {
                                                        window.location.href = "{{ route('admin.export.excel.template',['company'=>$company->id,'incomeStatement'=>$incomeStatement->id]) }}"
                                                    }
                                                }, {
                                                    "attr": {
                                                        'data-table-id': tableId.replace('#', ''),
                                                        // 'id':'test'
                                                    }
                                                    , "text": '<svg style="margin-right:10px;position:relative;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M5,4 L19,4 C19.2761424,4 19.5,4.22385763 19.5,4.5 C19.5,4.60818511 19.4649111,4.71345191 19.4,4.8 L14,12 L14,20.190983 C14,20.4671254 13.7761424,20.690983 13.5,20.690983 C13.4223775,20.690983 13.3458209,20.6729105 13.2763932,20.6381966 L10,19 L10,12 L4.6,4.8 C4.43431458,4.5790861 4.4790861,4.26568542 4.7,4.1 C4.78654809,4.03508894 4.89181489,4 5,4 Z" id="Path-33" fill="#000000"/></g></svg>' + '{{ __("Analysis") }}'
                                                    , 'className': 'btn btn-bold btn-secondary filter-table-btn ml-2  flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away'
                                                    , "action": function() {
                                                        window.location.href = "{{ route('dashboard.breakdown.incomeStatement',[$company->id,$reportType,$incomeStatement->id]) }}"
                                                    }
                                                },

                                                {
                                                    "attr": {
                                                        'data-table-id': tableId.replace('#', ''),
                                                        // 'id':'test'
                                                    }
                                                    , "text": '<svg style="margin-right:10px;position:relative;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M5,4 L19,4 C19.2761424,4 19.5,4.22385763 19.5,4.5 C19.5,4.60818511 19.4649111,4.71345191 19.4,4.8 L14,12 L14,20.190983 C14,20.4671254 13.7761424,20.690983 13.5,20.690983 C13.4223775,20.690983 13.3458209,20.6729105 13.2763932,20.6381966 L10,19 L10,12 L4.6,4.8 C4.43431458,4.5790861 4.4790861,4.26568542 4.7,4.1 C4.78654809,4.03508894 4.89181489,4 5,4 Z" id="Path-33" fill="#000000"/></g></svg>' + '{{ __("Interval View") }}'
                                                    , 'className': 'btn btn-bold btn-secondary filter-table-btn ml-2 flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away'
                                                    , "action": function() {
                                                        $('#filter_form-for-' + tableId.replace('#', '')).toggleClass('d-none');
                                                    }
                                                }
                                                , {
                                                    "text": '<svg style="margin-right:10px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3"/><rect id="Rectangle" fill="#000000" opacity="0.3" transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) " x="11" y="2" width="2" height="12" rx="1"/><path d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/></g></svg>' + '{{ __("Export [Excel]") }}'
                                                    , 'className': 'btn btn-bold btn-secondary  flex-1 flex-grow-0 btn-border-radius ml-2 do-not-close-when-click-away'
                                                    , "action": function() {
                                                        let form = $('form#store-report-form-id');
                                                        $('#export_type').val(0);
                                                        let oldFormAction = form.attr('action');
                                                        let exportFormAction = "{{ route('admin.export.income.statement.report',[$company->id,$incomeStatement->id,$reportType]) }}";
                                                        form.attr('action', exportFormAction);
                                                        form.submit();
                                                        form.attr('action', oldFormAction);

                                                        // $('#export_form-for-'+tableId.replace('#','')).toggleClass('d-none');
                                                    }
                                                },

                                                {
                                                    "text": '<svg style="margin-right:10px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3"/><rect id="Rectangle" fill="#000000" opacity="0.3" transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) " x="11" y="2" width="2" height="12" rx="1"/><path d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/></g></svg>' + '{{ __("Export [Pdf]") }}'
                                                    , 'className': 'btn btn-bold btn-secondary  flex-1 flex-grow-0 btn-border-radius ml-2 do-not-close-when-click-away'
                                                    , "action": function() {
                                                        let form = $('form#store-report-form-id');
                                                        let oldFormAction = form.attr('action');
                                                        $('#export_type').val(1);
                                                        $('#pdf_only').val(JSON.stringify(opens))
                                                        let exportFormAction = "{{ route('admin.export.income.statement.report.pdf',[$company->id,$incomeStatement->id,$reportType]) }}";
                                                        form.attr('action', exportFormAction);
                                                        form.submit();
                                                        form.attr('action', oldFormAction);

                                                        // $('#export_form-for-'+tableId.replace('#','')).toggleClass('d-none');
                                                    }
                                                },


                                            ]
                                            , createdRow: function(row, data, dataIndex, cells) {
                                                let reportType = vars.subItemType;
                                                let subOfSelect = ''
												let editableCells = $(cells).filter('.editable.editable-date')
                                                let salesGrowthRateId = domElements.salesGrowthRateId;
                                                let costOfGoodsId = domElements.costOfGoodsId;
                                                let corporateTaxesId = domElements.corporateTaxesId;
                                                let salesReveueId = domElements.salesRevenueId;
											// console.log('creator row')
                                                if (data.id == salesReveueId&& !data.duration) {
											
                                                    sales_revenues_sub_items_names = [{id:'all',name:'{{ __("All") }}'}];
                                                    sales_revenues_quantity_sub_items_names = [{id:'all',name:"{{ __('All') }}"}];

                                                    if (data.sub_items ) {
													
                                                        data.sub_items.forEach(function(subItemParent) {

                                                            if (subItemParent.pivot.is_quantity == 0) {
                                                                sales_revenues_sub_items_names.push({id:subItemParent.pivot.id , name:subItemParent.pivot.sub_item_name})
                                                            } else if (subItemParent.pivot.is_quantity && subItemParent.pivot.is_quantity != 0) {
                                                                sales_revenues_quantity_sub_items_names.push({id:subItemParent.pivot.id , name:subItemParent.pivot.sub_item_name})

                                                            }
                                                        });
														sales_revenues_sub_items_names.sort((a, b) => {
															// Ensure 'all' stays at the top by treating it separately
															if (a.id === 'all') return -1;
															if (b.id === 'all') return 1;
															return a.id - b.id;
															});
													sales_revenues_quantity_sub_items_names.sort((a, b) => {
															// Ensure 'all' stays at the top by treating it separately
															if (a.id === 'all') return -1;
															if (b.id === 'all') return 1;
															return a.id - b.id;
															});
											
														
														
                                                    }

                                                    window['sales_revenues_sub_items_names'] = sales_revenues_sub_items_names;
                                                    window['sales_revenues_quantity_sub_items_names'] = sales_revenues_quantity_sub_items_names;
                                                }
                                                if (data.id == costOfGoodsId) {
                                                    cost_of_goods_sub_items_names = [];
                                                    if (data.sub_items) {
                                                        data.sub_items.forEach(function(subItemParent) {
                                                            if (subItemParent.pivot.is_quantity == 0) {
                                                                cost_of_goods_sub_items_names.push({id:subItemParent.pivot.id,name:subItemParent.pivot.sub_item_name})
                                                            }
                                                        });
                                                    }

                                                    window['cost_of_goods_sub_items_names'] = cost_of_goods_sub_items_names;
                                                }
                                            //    var totalOfRowArray = [];
                                                var incomeStatementId = data.isSubItem ? data.pivot.financial_statement_able_id : $('#model-id').val();
                                                var incomeStatementItemId = data.isSubItem ? data.pivot.financial_statement_able_item_id : data.id;
                                         //       var subItemName = data.isSubItem ? data.pivot.sub_item_name : '';
                                        //        let is_quantity = false;
										//		let percentageValue = data.isSubItem && data.pivot.percentage_or_fixed == 'percentage' ? data.pivot.percentage_value : 0;
											//	if(data.isSubItem && data.pivot && data.pivot.financial_statement_able_item_id == corporateTaxesId){
											//		percentageValue = data.pivot.percentage_value;
											//	}
                                           //     $(cells).filter(".editable")
                                              //      .attr('data-income-statement-id', incomeStatementId)
                                                  //  .attr('title', "{{ __('Click To Edit') }}")
                                               //     .attr('data-main-model-id', incomeStatementId)
                                              //      .attr('data-income-statement-item-id', incomeStatementItemId)
                                          //          .attr('data-financial-statement-able-item-id', incomeStatementItemId)
                                                 //   .attr('data-main-row-id', incomeStatementItemId)
                                         //           .attr('data-sub-item-name', subItemName)
                                          //          .attr('data-table-id', "{{$tableId}}")
                                         //           .attr('data-is-quantity', data.isSubItem ? data.pivot.is_quantity : false)
                                                //    .attr('data-is-financial-expense', data.isSubItem ? data.pivot.is_financial_expense : false)
                                                //    .attr('data-vat-rate', data.isSubItem ? data.pivot.vat_rate : 0)
                                                //    .attr('data-is-deductible', data.isSubItem ? data.pivot.is_deductible : false)
                                         //           .attr('data-is-financial-income', data.isSubItem ? data.pivot.is_financial_income : false)
                                           //         .attr('data-percentage-value', percentageValue)
                                             //       .attr('data-cost-of-unit-value', data.isSubItem && data.pivot.percentage_or_fixed == 'cost_of_unit' ? data.pivot.cost_of_unit_value : 0)
                                                if (data.isSubItem) {
                                                    let has_percentage_or_fixed_sub_items = '';
                                                    if (data.pivot.can_be_percentage_or_fixed 
												//	&& reportType != 'actual'
													) {
                                                        sub_items_options = '';
                                                        sub_items_quantity_options = '';
                                                        var checkedPercentages = [];
                                                        var checkedCostOfUnit = [];
                                                        if (data.pivot.percentage_value) {
                                                            checkedPercentages = JSON.parse(data.pivot.is_percentage_of);
                                                        }
                                                        if (data.pivot.cost_of_unit_value) {
                                                            checkedCostOfUnit = JSON.parse(data.pivot.is_cost_of_unit_of) ? JSON.parse(data.pivot.is_cost_of_unit_of) : [];
                                                        }
                                                        if (data.pivot.financial_statement_able_item_id == corporateTaxesId) {
                                                            sub_items_options = '<option selected value="Earning Before Taxes - EBT" selected>Earning Before Taxes - EBT</option>'

                                                        } else {
                                                            window['sales_revenues_sub_items_names'].forEach(function(MainItemObject) {
                                                                var isCurrentChecked = checkedPercentages && (checkedPercentages.includes(MainItemObject.id.toString()) || checkedPercentages.includes(MainItemObject.name) )  ? ' selected' : ' ';
                                                                sub_items_options += '<option ' + isCurrentChecked + ' value="' + MainItemObject.id + '">' + MainItemObject.name + '</option>'
                                                            })

                                                            window['sales_revenues_quantity_sub_items_names'].forEach(function(MainItemObject) {
                                                                var isCurrentChecked = checkedCostOfUnit.includes(MainItemObject.id.toString()) || checkedCostOfUnit.includes(MainItemObject.name) ? ' selected' : ' ';
                                                                sub_items_quantity_options += '<option ' + isCurrentChecked + ' value="' + MainItemObject.id + '">' + MainItemObject.name + '</option>'
                                                            })

                                                        }
                                                        var nonRepeatingFixedisChecked = '';
                                                        var repeatingFixedisChecked = '';
                                                        var percentageIsChecked = '';
                                                        var costOfUnitIsChecked = '';
                                                        var nonRepeatingFixedDisplay = 'd-none';
                                                        var repeatingFixedDisplay = 'd-none';
                                                        var costOfUnitDisplay = 'd-none';
                                                        var percentageDisplay = 'd-none';
                                                        if (data.pivot.percentage_or_fixed == 'non_repeating_fixed') {
                                                            nonRepeatingFixedisChecked = 'checked';
                                                            nonRepeatingFixedDisplay = '';

                                                        } else if (data.pivot.percentage_or_fixed == 'repeating_fixed') {
                                                            repeatingFixedisChecked = 'checked';
                                                            repeatingFixedDisplay = '';
                                                        } else if (data.pivot.percentage_or_fixed == 'percentage') {
                                                            percentageIsChecked = 'checked';
                                                            percentageDisplay = ''

                                                        } else if (data.pivot.percentage_or_fixed == 'cost_of_unit') {
                                                            costOfUnitIsChecked = 'checked'
                                                            costOfUnitDisplay = ''

                                                        }

                                                        var repeating = `<div class="form-group custom-divs-class">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ">
																	<label >{{ __('Non-Repeating Amount') }}</label>
															
															<input data-sub-item-name="${data.pivot.sub_item_name}" data-sub-item-id="${data.pivot.id}" data-in-edit-mode="1" ${nonRepeatingFixedisChecked} class="can_be_percentage_or_fixed_class non-repeating-fixed can-trigger-non-repeating-modal" type="checkbox" value="non_repeating_fixed" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">	
															</div>
															</div>
															<div class="form-group custom-divs-class ${reportType == 'actual' || reportType =='modified'  ?'hidden' : ''}">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap">
																	<label >{{ __('Repeating Fixed Amount') }}</label>
																	<input ${repeatingFixedisChecked}  class="can_be_percentage_or_fixed_class repeating-fixed" type="checkbox" value="repeating_fixed" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
																	</div>
															</div>`;



                                                        var hideRepeating = false;

                                                        if (parseInt(data.pivot.financial_statement_able_item_id) == parseInt(corporateTaxesId.trim())) {
                                                            hideRepeating = true
                                                        }
                                                        if (hideRepeating) {
                                                            repeating = ''
                                                        }
                                                        const canViewPercentageOfSalesAndCostOfUnit = data.pivot.financial_statement_able_item_id != domElements.corporateTaxesId
                                                        subOfSelect = canViewPercentageOfSalesAndCostOfUnit ? `<div class="mt-2">
														<label>{{ __('Sub Of') }}</label>
														<select  name="sub_of_id" class="form-control main-row-select" data-selected-main-row="${data.pivot.financial_statement_able_item_id}">
															
														</select>
													
												</div>` : `<input type="hidden" name="sub_of_id" value="${domElements.corporateTaxesId}"> `
                                                        const percentageAndCostOfUnitAndRepeatingDivs = data.pivot.financial_statement_able_item_id != domElements.corporateTaxesId ? `<div class="form-group custom-divs-class">
															<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ${reportType == 'actual' || reportType =='modified' ?'hidden' : ''}">
																<label >{{ __('% Of Sales') }}</label>
															<input ${percentageIsChecked} class="can_be_percentage_or_fixed_class percentage-of-sales" type="checkbox" value="percentage" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
															</div>
															</div>
																<div class="form-group custom-divs-class ${reportType == 'actual' || reportType =='modified' ?'hidden' : ''}">
															<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ">
																<label >{{ __('Cost Per Unit') }}</label>
															<input ${costOfUnitIsChecked} class="can_be_percentage_or_fixed_class cost-of-unit" type="checkbox" value="cost_of_unit" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
															</div>
															</div>
															
															
															
															
															</div>
															
															<div class="non-repeating-fixed-sub ${nonRepeatingFixedDisplay}">
															</div>
															<div class="repeating-fixed-sub ${repeatingFixedDisplay}">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap">
																	<label class="form-label flex-self-start">{{ __('Amount') }}</label>
																	<input type="text" class="form-control" name="sub_items[0][repeating_fixed_value]" value="${data.pivot.repeating_fixed_value ? data.pivot.repeating_fixed_value : 0}">
																</div>
															</div>
															
															<div class="percentage-sub w-100 ${percentageDisplay}">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap" style="width:60% !important">
																	<div class="d-flex parent-for-select flex-column align-items-center justify-content-center flex-wrap" style="width:100% !important">
																		<label class="form-label flex-self-start">{{ __('% Of') }}</label>
																	
																	<select multiple
																	data-width="auto"
																class="form-select select select2-select2 sub_select"   name="sub_items[0][is_percentage_of][]">
																	${sub_items_options}
																</select>
																	</div>
																	</div>
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap margin-left-auto w-160px">
																		<label class="flex-self-start">{{ __('Percentage Value') }}</label>
																		<div>
																			<input value="${data.pivot.percentage_value?data.pivot.percentage_value:0}" type="text" class="form-control" name="sub_items[0][percentage_value]">
																			</div>	
																	</div>
															</div>
															
																
															
															
															
															
															<input type="hidden" name="sub_items[0][can_be_percentage_or_fixed]" value="1">
															
															<div class="cost-of-unit-sub w-100 ${costOfUnitDisplay}">
																<div class="d-flex align-items-center justify-content-between" style="flex:1">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap" style="width:60% !important">
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap " style="width:100% !important">
																		<label class="form-label flex-self-start">{{ __('Cost Per Unit Of') }}</label>
																	<select multiple
																class="form-select select select2-select2 sub_select"   name="sub_items[0][is_cost_of_unit_of][]">
																	${sub_items_quantity_options}
																</select>
																	</div>
																	</div>
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap margin-left-auto w-160px">
																		<label class="flex-self-start">{{ __('Cost Per Unit Value') }}</label>
																		<div>
																			<input value="${data.pivot.cost_of_unit_value?data.pivot.cost_of_unit_value:0}" type="text" class="form-control" name="sub_items[0][cost_of_unit_value]"
																			</div>	
																	</div>
															</div>
																</div>
																
																</div>
																
																
																
																` : `
															<input type="hidden" name="sub_items[0][is_percentage_of][]" value="Earning Before Taxes - EBT">
															
															<input type="hidden" name="sub_items[0][percentage_or_fixed]" value="percentage">
																<input type="hidden" name="sub_items[0][can_be_percentage_or_fixed]" value="1">
															<div class="d-flex flex-column align-items-center justify-content-center flex-wrap w-160px">
																		<label class="flex-self-start">{{ __('Percentage Value') }}</label>
																		<div>
																			<input value="${data.pivot.percentage_value?data.pivot.percentage_value:0}" type="text" class="form-control" name="sub_items[0][percentage_value]">
																			</div>	
																	</div>
															</div>
															`













                                                        has_percentage_or_fixed_sub_items =
                                                            `
															<br>
															<div class="flex-checkboxes how-many-item" data-id="0">
																<div class="form-check mt-2">
															
															${repeating}
															
															${percentageAndCostOfUnitAndRepeatingDivs}
																
																
																`;



                                                    }

                                                    let Depreciation = '';

                                                    var quantity = '';
                                                    if (data.pivot && data.pivot.can_be_quantity) {
                                                        if (data.pivot.is_quantity) {
                                                        }

                                                    }
                                                    if (data.pivot && data.pivot.can_be_depreciation) {
                                                        let checkedDepreciation = '';
                                                        if (data.pivot.is_depreciation_or_amortization) {
                                                            checkedDepreciation = ' checked ';
                                                        }
                                                        Depreciation = `
																<label>{{ __('Is Depreciation Or Amortization ? ') }}</label>
										
															<input ${checkedDepreciation} class="" type="checkbox" value="1" name="is_depreciation_or_amortization"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">`
                                                    }

                                                    $(row).append(
                                                        `
										<div class="modal fade edit-sub-modal-class" id="edit-sub-modal${data.pivot.financial_statement_able_item_id + convertStringToClass(data.pivot.sub_item_name) }" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" >
											<div class="modal-dialog" role="document">
												<div class="modal-content custom-modal-w-h">
												<div class="modal-header">
													<h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Sub Item For') }} ${data.pivot.sub_item_name} </h5>
													<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span >&times;</span>
													</button>
												</div>
												<div class="modal-body">
													<form method="post" data-financial-statement-able-item-id="${data.pivot.financial_statement_able_item_id}" id="edit-sub-item-form${data.pivot.financial_statement_able_item_id + convertStringToClass(data.pivot.sub_item_name)  }" class="edit-submit-sub-item" action="{{ route('admin.update.income.statement.report',['company'=>getCurrentCompanyId()]) }}">
														<input type="hidden" name="_token" value="{{ csrf_token() }}">
														<input type="hidden" name="in_add_or_edit_modal" value="1">
														
														<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
														<input type="hidden" name="financial_statement_able_item_id"  value="${data.pivot.financial_statement_able_item_id}">
														<input  type="hidden" name="financial_statement_able_id"  value="{{ $incomeStatement->id }}">
														<input  type="hidden" name="income_statement_id"  value="{{ $incomeStatement->id }}">
														<input  type="hidden" name="in_edit_mode"  value="1">
														<input  type="hidden" name="was_financial_income"  value="${data.pivot && data.pivot.is_financial_income!=null ? data.pivot.is_financial_income :''}">
														<input  type="hidden" name="was_financial_expense"  value="${data.pivot && data.pivot.is_financial_expense!=null ? data.pivot.is_financial_expense :''}">
														<input  type="hidden" name="sub_item_name"  value="${data.pivot.sub_item_name}">
														<div class="d-flex align-items-center">
														
													<div style="width:75%">
														<label>{{ __('Name') }}</label>
														<input ${data.pivot.financial_statement_able_item_id == domElements.corporateTaxesId ? 'readonly':'' } name="new_sub_item_name"  class="form-control mb-2" type="text" value="${data.pivot.sub_item_name}">
														
														</div>
														<div style="margin-left: 15px;
															display: flex;
															flex-direction: column;
															align-items: center;">
															
														${Depreciation}
														</div>
														${data.pivot.financial_statement_able_item_id == domElements.salesRevenueId ? getSalesRevenueModal(true ,data.pivot,data.pivot.financial_statement_able_item_id) : ''}
														</div>
														
														${has_percentage_or_fixed_sub_items}
														${subOfSelect}
														${getNonRepeatingModal(true ,data.pivot,data.pivot.financial_statement_able_item_id)}
														${getVatRate(true ,data.pivot,data )}
														
														${getFinancialIncomeOrExpenseCheckBoxes(true ,data.pivot,data.pivot.financial_statement_able_item_id )}
														${getCollectionPolicyHtml(true,data.pivot,data.pivot.financial_statement_able_item_id)}
														</div>

														<div class="modal-footer" style="border-top:0 !important">
													<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
													<button type="button" class="btn btn-primary save-sub-item-edit" data-id="${data.pivot.financial_statement_able_item_id}" data-sub-item-name="${data.pivot.sub_item_name }">{{ __('Edit') }}</button>
												</div>
													</form>
												
												</div>
											</div>
												</div>
												`
                                                    )


                                                    $(row).append(
                                                        `
                            
                            
											<div class="modal fade delete-item-modal" data-item-id="${data.pivot.financial_statement_able_item_id}" data-sub-name="${data.pivot.sub_item_name}" id="delete-sub-modal${data.pivot.financial_statement_able_item_id + convertStringToClass(data.pivot.sub_item_name)}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" >
													<div class="modal-dialog" role="document">
														<div class="modal-content ">
														<div class="modal-header">
															<h5 class="modal-title" id="exampleModalLongTitle">{{ __('Delete Sub Item ') }} ${data.pivot.sub_item_name} </h5>
															<button type="button" class="close" data-dismiss="modal" aria-label="Close">
															<span >&times;</span>
															</button>
														</div>
														<div class="modal-body">
															<form id="delete-sub-item-form${data.pivot.financial_statement_able_item_id+convertStringToClass(data.pivot.sub_item_name) }" class="delete-submit-sub-item" action="{{ route('admin.destroy.income.statement.report',['company'=>getCurrentCompanyId()]) }}">
																<input type="hidden" value="1" name="in_delete_modal" >
																<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
																<input type="hidden" name="income_statement_id" value="{{$incomeStatement->id}}">
																<input type="hidden" name="is_financial_income" value="${data.pivot.is_financial_income }">
																<input type="hidden" name="is_financial_expense" value="${data.pivot.is_financial_expense}">
																<input type="hidden" name="financial_statement_able_item_id"  value="${data.pivot.financial_statement_able_item_id}">
																<input  type="hidden" name="financial_statement_able_id"  value="{{ $incomeStatement->id }}">
																<input  type="hidden" name="sub_item_name"  value="${data.pivot.sub_item_name}">
																<p>{{ __('Are You Sure To Delete ') }} ${data.pivot.sub_item_name}  ? </p>
															</form>
														</div>
														<div class="modal-footer" style="border-top:0 !important">
															<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
															<button type="button" class="btn btn-primary save-sub-item-delete" data-id="${data.pivot.financial_statement_able_item_id}" data-sub-item-name="${data.pivot.sub_item_name }" >{{ __('Delete') }}</button>
														</div>
														</div>
													</div>
														</div>
														
														<input type="hidden" class="input-hidden-for-total" name="subTotals[${data.pivot.financial_statement_able_id}][${data.pivot.financial_statement_able_item_id}][${data.pivot.sub_item_name}]"   value="0" >
														
														`
                                                    )

                                                    $(row).addClass('edit-info-row').addClass('add-sub maintable-1-row-class' + (incomeStatementItemId))
                                                    $(row).addClass('d-none is-sub-row ');
                                                    $(row).attr('data-sub-item-name', data.pivot.sub_item_name)

                                                    $(row).attr('data-percentage-value', data.pivot && data.pivot.percentage_or_fixed == 'percentage' ? data.pivot.percentage_value || 0 : 0)
                                             //       $(row).attr('data-cost-of-unit-value', data.pivot && data.pivot.percentage_or_fixed == 'cost_of_unit' ? data.pivot.cost_of_unit_value || 0 : 0)
                                                //    $(row).attr('data-is-financial-expense', data.pivot && data.pivot.is_financial_expense == '1' ? 1 : 0)
                                                    $(row).attr('data-is-quantity', data.pivot && data.pivot.is_quantity == '1' ? 1 : 0)
                                               //     $(row).attr('data-is-financial-income', data.pivot && data.pivot.is_financial_income == '1' ? 1 : 0)
                                                //    $(row).attr('data-vat-rate', data.pivot ? data.pivot.vat_rate : 0)
                                                 //   $(row).attr('data-is-deductible', data.pivot ? data.pivot.is_deductible : 0)

                                                    if (data.pivot.can_be_percentage_or_fixed) {
                                                //        $(row).attr('data-can-be-percentage-or-fixed', data.pivot.can_be_percentage_or_fixed)
                                                    //    $(row).attr('data-is-percentage-of', data.pivot.is_percentage_of)
                                                //        $(row).attr('data-is-cost-of-unit-of', data.pivot.is_cost_of_unit_of)
                                                //        $(row).attr('data-percentage-or-fixed', data.pivot.percentage_or_fixed)
                                                //        $(row).attr('data-is-percentage', data.pivot.percentage_or_fixed == 'percentage')
                                                      //  $(row).attr('data-is-cost-of-unit', data.pivot.percentage_or_fixed == 'cost_of_unit')
                                                     //   $(row).attr('data-is-repeating-fixed', data.pivot.percentage_or_fixed == 'repeating_fixed')
                                                      //  $(row).attr('data-is-none-repeating-fixed', data.pivot.percentage_or_fixed == 'non_repeating_fixed')
                                                    //    $(row).attr('data-financial-statement-able-item-id', -999)

                                                    }



                                                //    if (data.pivot && data.pivot.is_depreciation_or_amortization) {
                                               //         $(row).addClass('is-depreciation-or-amortization')
                                               //     }


                                                //    editableCells.each(function(index, dateDt) {
                                                      
                                                   //   totalOfRowArray.push(parseFloat(filterNumericUserInput($(dateDt).html(), data.pivot ? data.pivot.is_financial_expense : 0)));

                                                  //      var hiddenInput = `<input type="hidden" name="value[${incomeStatementId}][${incomeStatementItemId}][${subItemName}][${index}]" data-date="${index}" data-is-quantity="${data.pivot ? data.pivot.is_quantity : 0}" data-is-cost-of-unit="${data.pivot ? data.pivot.is_cost_of_unit_of : 0}" data-parent-model-id="${incomeStatementItemId}" value="${(filterNumericUserInput($(dateDt).html(), data.pivot ? data.pivot.is_financial_expense : 0 ))}" >
													//	<input type="hidden" name="is_financial_income[${incomeStatementId}][${incomeStatementItemId}][${subItemName}]"  value="${data.pivot && data.pivot.is_financial_income!= null? data.pivot.is_financial_income :0}" >
														
													//	 `;
                                                   //     $(dateDt).after(hiddenInput);

                                                  //  });
										//		// console.log(row)
                                               //     $(row).append(
                                              //          `<input type="hidden" class="input-hidden-for-total" name="subTotals[${incomeStatementId}][${incomeStatementItemId}][${subItemName}]"   value="0" >`
                                              //      );


                                               //     $(cells).filter('.editable.editable-text').each(function(index, textDt) {
//
                                                  //      var hiddenInput = `<input type="hidden" class="text-input-hidden"  name="financialStatementAbleItemName[${incomeStatementId}][${incomeStatementItemId}][${subItemName}]" value="${$(textDt).html()}" > `;
                                                   //     $(textDt).after(hiddenInput);
                                                   // })

                                                } else {
                                                    if (!data.has_sub_items) {

                                                        $(row).addClass('main-with-no-child').attr('data-model-id', data.id).attr('data-financial-statement-able-item-id', data.id);
														// console.log('eeeeee')
                                                     //   editableCells.each(function(index, dateDt) {
                                                     //     var hiddenInput = `<input type="hidden" name="valueMainRowWithoutSubItems[${incomeStatementId}][${incomeStatementItemId}][${index}]" data-date="${index}"  value="${(filterNumericUserInput($(dateDt).html(),data.pivot ? data.pivot.is_financial_expense : false))}" > `;
                                                     //       $(dateDt).after(hiddenInput);
                                                  //      });
                                                        var subTotal = data.main_rows && data.main_rows[0] ? data.main_rows[0].pivot.total : 0
                                             //           totalOfRowArray.push(parseFloat(subTotal))
                                                        $(row).append(`
											<input type="hidden" class="input-hidden-for-total" name="totals[${incomeStatementId}][${incomeStatementItemId}]" value="${subTotal}">
										`);


                                                        let dependOn = data.depends_on ? JSON.parse(data.depends_on) : [];
                                                        if (dependOn.length) {
                                                            $(row).attr('data-depends-on', dependOn.join(','))
                                                        }
                                                      //  $(cells).each(function(index, cell) {
                                                      //      $(cell).removeClass('editable').removeClass('editable-text').attr('title', '')
                                                     //   });


                                                        if (data.is_sales_rate) {
                                                            $(row).addClass('is-sales-rate ');
                                                        }

                                                        if (data.is_sales_rate || data.id == salesGrowthRateId) {
                                                            if (data.id == salesGrowthRateId) {
                                                                $(row).addClass('is-sales-growth-rate')
                                                            }
                                                            $(row).addClass('is-rate');
                                                        } else {


                                                        }

                                                    } else {
                                                        $(row).addClass('is-main-with-sub-items').attr('data-financial-statement-able-item-id', data.id);
                                                        if (data.is_main_for_all_calculations) {
                                                            $(row).addClass('is-main-for-all-calculations');
                                                        }
														// console.warn('ffffff')
                                                  //     editableCells.each(function(index, dateDt) {
                                                           
                                                      //      totalOfRowArray.push(parseFloat(filterNumericUserInput($(dateDt).html(), data.pivot ? data.pivot.is_financial_expense : 0)));

                                                        //    var hiddenInput = `<input type="hidden" class="main-row-that-has-sub-class" name="valueMainRowThatHasSubItems[${incomeStatementId}][${incomeStatementItemId}][${index}]" data-date="${index}"  value="${(filterNumericUserInput($(dateDt).html(),data.pivot ? data.pivot.is_financial_expense : 0))}" > `;
                                                        //    $(dateDt).after(hiddenInput);
                                                     //   });
                                                        var subTotal = data.main_rows && data.main_rows[0] ? data.main_rows[0].pivot.total : 0

                                                        $(row).append(
                                                            `<input type="hidden" class="input-hidden-for-total" name="totals[${incomeStatementId}][${incomeStatementItemId}]"  value="${subTotal}" >`
                                                        );

                                                      //  $(cells).each(function(index, cell) {
                                                     //      $(cell).removeClass('editable').removeClass('editable-text').attr('title', '')
                                                     //   });


                                                        let has_percentage_or_fixed_sub_items = '';
                                                        if (data.has_percentage_or_fixed_sub_items 
														
														) {
                                                            sub_items_options = '';
                                                            sub_items_of_unit_options = '';
                                                            let corporateTaxesId = $('#corporate-taxes-id').val();

                                                            if (data.id == corporateTaxesId) {
                                                                sub_items_options = '<option value="Earning Before Taxes - EBT" selected>Earning Before Taxes - EBT</option>'
                                                            } else {
														
                                                                window['sales_revenues_sub_items_names'].forEach(function(MainItemObject) {
                                                                    sub_items_options += '<option value="' + MainItemObject.id.toString() + '">' + MainItemObject.name + '</option>'
                                                                })
															
																
                                                                window['sales_revenues_quantity_sub_items_names'].forEach(function(MainItemObject) {
                                                                    sub_items_of_unit_options += '<option value="' + MainItemObject.id.toString() + '">' + MainItemObject.name + '</option>'
                                                                })
                                                            }
                                                            var hideRepeating = false;

                                                            if (parseInt(data.id) == parseInt(corporateTaxesId.trim())) {
                                                                hideRepeating = true
                                                            }
															

                                                            var repeating = `<div class="form-group custom-divs-class">
															
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap">
																	<label >{{ __('Non-Repeating Amount') }}</label>
															
															<input data-sub-item-name="new" data-in-edit-mode="0" data-sub-item-id="0"  class="can_be_percentage_or_fixed_class non-repeating-fixed can-trigger-non-repeating-modal" type="checkbox" value="non_repeating_fixed" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">	
															<input   type="hidden" value="1" name="in_add_mode"  >	
															</div>
															</div>
															<div class="form-group custom-divs-class ${reportType == 'actual' || reportType =='modified' ? 'hidden' : ''}" >
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap">
																	<label >{{ __('Repeating Fixed Amount') }}</label>
																	
																	<input class="can_be_percentage_or_fixed_class repeating-fixed" type="checkbox" value="repeating_fixed" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
																	</div>
															</div>`;
                                                            if (hideRepeating) {

                                                                repeating = ''
                                                            }
                                                            has_percentage_or_fixed_sub_items =
                                                                `
															<br>
															<div class="flex-checkboxes">
															
																<div class="form-check mt-2">
															
															${repeating}
															
														<div class="form-group custom-divs-class ${reportType == 'actual' || reportType =='modified' ? 'hidden' : ''}">
															<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ">
																<label >{{ __('% Of Sales') }}</label>
															<input class="can_be_percentage_or_fixed_class percentage-of-sales" type="checkbox" value="percentage" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
															</div>
															</div>
															
															<div class="form-group custom-divs-class ${reportType == 'actual' || reportType =='modified' ? 'hidden' : ''}">
															<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ">
																<label >{{ __('Cost Per Unit') }}</label>
															<input class="can_be_percentage_or_fixed_class cost-of-unit" type="checkbox" value="cost_of_unit" name="sub_items[0][percentage_or_fixed]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
															</div>
															</div>
															</div>
															
															
															
															</div>
															
															<div class="non-repeating-fixed-sub d-none">
															</div>
															<div class="repeating-fixed-sub d-none">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap">
																	<label class="form-label flex-self-start">{{ __('Amount') }}</label>
																	<input type="text" class="form-control" name="sub_items[0][repeating_fixed_value]" value="0">
																</div>
															</div>
															
															<div class="percentage-sub d-none w-100">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap" style="width:60% !important">
																	<div class="d-flex parent-for-select flex-column align-items-center justify-content-center flex-wrap" style="width:100% !important">
																		<label class="form-label flex-self-start">{{ __('% Of') }}</label>
																	
																	<select multiple
																	data-width="auto"
																class="form-select select select2-select2 sub_select"   name="sub_items[0][is_percentage_of][]">
																	${sub_items_options}
																</select>
																	</div>
																	</div>
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap margin-left-auto w-160px">
																		<label class="flex-self-start">{{ __('Percentage Value') }}</label>
																		<div>
																			<input type="text" class="form-control" name="sub_items[0][percentage_value]"
																			</div>	
																	</div>
															</div>
															
														
															
															<input type="hidden" name="sub_items[0][can_be_percentage_or_fixed]" value="1">
																</div>
																
																
																	<div class="cost-of-unit-sub d-none w-100">
																<div class="d-flex flex-column align-items-center justify-content-center flex-wrap" style="width:60% !important">
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap" style="width:100% !important">
																		<label class="form-label flex-self-start">{{ __('Cost Per Unit Of') }}</label>
																	
																	<select multiple
																class="form-select select select2-select2 sub_select"   name="sub_items[0][is_cost_of_unit_of][]">
																	${sub_items_of_unit_options}
																</select>
																	</div>
																	</div>
																	<div class="d-flex flex-column align-items-center justify-content-center flex-wrap margin-left-auto w-160px">
																		<label class="flex-self-start">{{ __('Cost Per Unit Value') }}</label>
																		<div>
																			<input type="text" class="form-control" name="sub_items[0][cost_of_unit_value]"
																			</div>	
																	</div>
															</div>
															
															`;
                                                        }


                                                        var increaseNameWidth = null;
                                                        if (data.has_percentage_or_fixed_sub_items) {
                                                            $(row).addClass('has-percentage-or-fixed-sub-items').attr('data-financial-statement-able-item-id', incomeStatementItemId)
                                                        } else {
                                                            increaseNameWidth = true
                                                        }
                                                        if (data.has_depreciation_or_amortization) {
                                                            $(row).addClass('has-depreciation-or-amortization');
                                                            nameAndDepreciationIfExist = ` <div class="append-names mt-2" data-id="${data.id}">

											<div class="form-group how-many-item d-flex flex-wrap text-nowrap justify-content-between align-items-center border-bottom-popup" data-id="${data.id}" data-index="0">
											<div style="display:flex;align-items:center;justify-content:space-between;width:100%">
												<div style="width:60%">
													<label class="form-label">{{ __('Name') }}</label>
													<input  name="sub_items[0][name]" type="text" value="" class="form-control names-items-names trim-when-key-up" required>
												</div>
												
												<div class="form-check mt-2 text-center ">
												<label class="form-check-label"  style="margin-top:3px;display:block" >
													{{ __('Is Depreciation Or Amortization ?') }}
												</label>

												<input class="" type="checkbox" value="1" name="sub_items[0][is_depreciation_or_amortization]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
												</div>
											</div>
												` + has_percentage_or_fixed_sub_items + `
											  	</div> 
															 `;
                                                        } else {
                                                            if (data.id == domElements.financialIncomeOrExpensesId) {
                                                                // quantityCheckbox = ''
                                                                increaseNameWidth = true
                                                            }
                                                            nameAndDepreciationIfExist = ` <div class="append-names mt-2" data-id="${data.id}">

															<div class="form-group how-many-item d-flex flex-wrap text-nowrap justify-content-between align-items-center border-bottom-popup" data-id="${data.id}" data-index="0">
																<div style="display:flex;align-items:center;width:100%;justify-content:space-between; ">
																<div class="${increaseNameWidth ? 'width-66' : ''}"><label class="form-label">{{ __('Name') }}</label>
																<input name="sub_items[0][name]" type="text" value="" class="form-control trim-when-key-up  names-items-names" required></div>
															
																` + `` + `</div>` +
                                                                `
																` + has_percentage_or_fixed_sub_items + `
															
															${data.id == domElements.salesRevenueId ? getSalesRevenueModal(false , null , data.id):''}
															${data.id == domElements.salesRevenueId ? getVatRate(false , null , data) : '' }
															${data.id == domElements.salesRevenueId ? getCollectionPolicyHtml(false,null,data.id):''}
															</div> ` + '' + `
														 `;

                                                        }

                                                        $(row).addClass('edit-info-row').addClass('add-sub maintable-1-row-class' + (data.id)).attr('data-model-id', data.id).attr('data-model-name', '{{ $modelName }}')
                                                            .append(`
																								<div class="modal fade add-sub-item-modal" id="add-sub-modal${data.id}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" >
																			<div class="modal-dialog" role="document">
																				<div class="modal-content custom-modal-w-h">
																				<div class="modal-header">
																					<h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Sub Item For') }} ${data.name} </h5>
																					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																					<span >&times;</span>
																					</button>
																				</div>
																				<div class="modal-body">
																					<form  data-financial-statement-able-item-id="${data.id}" id="add-sub-item-form${data.id}" class="submit-sub-item" action="{{ route('admin.store.income.statement.report',['company'=>getCurrentCompanyId()]) }}" method="post">

																						
																						<label class="label ">{{ __('How Many Items ?') }}</label>
																																	<input type="hidden" name="in_add_or_edit_modal" value="1">

																						<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
																						<input type="hidden" name="financial_statement_able_item_id"  value="${data.id}">
																						<input  type="hidden" name="financial_statement_able_id"  value="{{ $incomeStatement->id }}">
																						<input  type="hidden" name="income_statement_id"  value="{{ $incomeStatement->id }}">
																						<input  type="hidden" name="_token"  value="{{ csrf_token() }}">
																						

																						<input data-id="${data.id}" class="form-control how-many-class only-greater-than-zero-allowed" name="how_many_items" type="number" value="1">
																					
																					${nameAndDepreciationIfExist}
																																		${data.id != domElements.salesRevenueId ? getVatRate(false , null , data) : '' }
																																		${data.id != domElements.salesRevenueId ? getNonRepeatingModal(false , null , data.id) : '' }
																																		
																					${getFinancialIncomeOrExpenseCheckBoxes(false ,null, data.id)}
																					${data.id != domElements.salesRevenueId ? getCollectionPolicyHtml(false,null,data.id) :'' }
																					</div>
																					<div class="modal-footer ddd-1" style="border-top:0 !important">
																					<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
																					<button type="submit" class="btn btn-primary save-sub-item" data-redirect-to='' data-id="${data.id}">{{ __('Save') }}</button>
																				</div>
																				</form>
																				</div>
																				
																					
																				</div>
																			</div>
																				</div>
																			

																								`)

                                                    }


                                                };


                                                if (data.is_sales_rate || data.id == salesGrowthRateId) {
                                                    if (data.id != salesGrowthRateId) {
                                                        var subTotal = data.main_rows && data.main_rows[0] ? data.main_rows[0].pivot.total : 0
                                                        $(row).find('td.total-row').html(number_format(subTotal, 2) + ' %');
                                                        $(row).find('.input-hidden-for-total').val(subTotal)
                                                    } else {
                                                        $(row).find('td.total-row').html('-')

                                                    }
                                                } else {
												
                                                   // var totals =-150;
											       var totals = data.pivot ? data.pivot.total : subTotal ;
                                            //      var totals = array_sum(totalOfRowArray);
									//			   // console.log('total',totals,data,'---end');
                                                    $(row).find('td.total-row').html(number_format(totals));
                                                    $(row).find('.input-hidden-for-total').val(totals)
                                                }

                                            }
                                            , drawCallback: function(settings) {
												// console.log('draw callback')
										
                                                const reportType = vars.subItemType;
                                                let corporateTaxesId = document.getElementById('corporate-taxes-id').value;
                                                let options = '';
                                                document.querySelectorAll('.is-main-with-sub-items').forEach(function(row, index) {
                                                    let modelId = row.getAttribute('data-model-id');
                                                    var name = row.querySelector('.is-name-cell').innerHTML;
                                                    options += ` <option value="${modelId}"> ${name} </option> `
                                                })
                                                document.querySelectorAll('.main-row-select').forEach(function(mainRowSelect, index) {
                                                    var selectedRowId = mainRowSelect.getAttribute('data-selected-main-row');
                                                    var replace = `value="${selectedRowId}"`;
                                                    var replaceWith = `selected value="${selectedRowId}"`
                                                    options = options.replace(replace, replaceWith);
                                                    mainRowSelect.innerHTML = options;
                                                });
                                                document.querySelectorAll('.main-row-select[data-selected-main-row="' + corporateTaxesId + '"] option').forEach((option) => {
                                                    if (option.value != corporateTaxesId) {
                                                        option.remove();
                                                    }
                                                })

                                              //  var addBtnForCorporateTaxes = document.querySelector('.add-btn[data-income-statement-item-id="' + corporateTaxesId + '"]')
                                              //  var deleteBtnForCorporateTaxes = document.querySelector('.delete-btn[data-income-statement-item-id="' + corporateTaxesId + '"]')
                                            //    if (addBtnForCorporateTaxes) {
                                           //         addBtnForCorporateTaxes.classList.add('d-none')
                                            //        addBtnForCorporateTaxes.classList.remove('d-block')
                                            //    }
                                            //    if (deleteBtnForCorporateTaxes) {
                                            //        deleteBtnForCorporateTaxes.classList.add('d-none')
                                            //        deleteBtnForCorporateTaxes.classList.remove('d-block')
                                            //    }
                                            //    if (true
											//	) {
                                               //     const netProfitId = domElements.netProfitId;
                                              // //     const corporateTaxesPercentageValue = document.querySelector('.is-sub-row.maintable-1-row-class' + corporateTaxesId + ' td.editable-date') ? document.querySelector('.is-sub-row.maintable-1-row-class' + corporateTaxesId + ' td.editable-date').getAttribute('data-percentage-value') / 100 : 0;
                                               //     const earningBeforeTaxesTotalValue = document.querySelector('.main-with-no-child[data-financial-statement-able-item-id="' + domElements.earningBeforeTaxesId + '"] input.input-hidden-for-total').value;
                                               //     const totalCorporateTaxes = earningBeforeTaxesTotalValue < 0 ? 0 : earningBeforeTaxesTotalValue * corporateTaxesPercentageValue;
                                               //     const corporateTaxesRow = document.querySelector('tr[data-model-id="' + corporateTaxesId + '"]')
                                               //     const corporateTaxesSubRowRow = document.querySelector('tr.is-sub-row.maintable-1-row-class' + corporateTaxesId)
                                                //    const salesRevenueId = domElements.salesRevenueId
                                               //     const corporateTaxesSalesRateRow = document.querySelector('tr.is-sales-rate[data-financial-statement-able-item-id="' + sales_rate_maps[corporateTaxesId] + '"]')
                                               //     const netProfitTaxesSalesRateRow = document.querySelector('tr.is-sales-rate[data-financial-statement-able-item-id="' + sales_rate_maps[netProfitId] + '"]')
                                                //    const totalOfSalesRevenue = document.querySelector('.maintable-1-row-class' + salesRevenueId + ' .input-hidden-for-total').value;
                                               //     const netProfitRow = document.querySelector('tr[data-model-id="' + netProfitId + '"]')
													
                                                //    corporateTaxesRow.querySelector('td.total-row').innerHTML = number_format(totalCorporateTaxes)
												//	if(corporateTaxesSubRowRow){
                                               //     corporateTaxesSubRowRow.querySelector('td.total-row').innerHTML = number_format(totalCorporateTaxes)
                                               //     corporateTaxesSubRowRow.querySelector('input.input-hidden-for-total').value = totalCorporateTaxes
                                              //      corporateTaxesRow.querySelector('input.input-hidden-for-total').value = totalCorporateTaxes
														
												//	}
                                                //    corporateTaxesSalesRateRow.querySelector('.total-row').innerHTML = number_format(totalOfSalesRevenue ? totalCorporateTaxes / totalOfSalesRevenue * 100 : 0, 2) + ' %'
                                                //    const totalValueForNetProfit = earningBeforeTaxesTotalValue - totalCorporateTaxes;
                                                 //   netProfitRow.querySelector('td.total-row').innerHTML = number_format(totalValueForNetProfit)
                                                  //  netProfitRow.querySelector('input.input-hidden-for-total').value = totalValueForNetProfit
                                                //    netProfitTaxesSalesRateRow.querySelector('.total-row').innerHTML = number_format(totalOfSalesRevenue ? totalValueForNetProfit / totalOfSalesRevenue * 100 : 0, 2) + ' %'
                                            //    }
                                                reinitializeSelect2();

                                                if (reportType == 'adjusted') {



                                                    $('.main-table-class').DataTable().column(1).visible(false);
                                                    $('.kt-portlet__foot').css('display', 'none');
                                                    $('#store-report-form-id .kt-portlet').append(`<div class='single-btn'><button style="float:right" type="submit" class="btn active-style redirect-btn" data-redirect-to="{{ route('admin.view.financial.statement',getCurrentCompanyId()) }}"> Back To Financial Statement </button></div>`);
                                              
                                                }
                                                // handle data for intervals 
                                            }
                                            , initComplete: function(settings, json) {
												// console.log('init completed')
                                                table = $('.main-table-class').DataTable();
                                                globalTable = table;

                                            }



                                        }

                                    );
                                };

                            return {

                                //main function to initiate the module
                                init: function() {
                                    initTable1();

                                },

                            };

                        }();

                        jQuery(document).ready(function() {
                            KTDatatablesDataSourceAjaxServer.init();

                         
                            $(document).on('click', '.close-inner-modal', function(e) {
							// console.log('.close inner')
								$(this).closest('.modal-for-quantity').removeClass('d-block').modal('hide');
								$(this).closest('.modal-for-non-repeating').removeClass('d-block').modal('hide');
                           })
                         //     $(document).on('click', '.close-inner-modal', function() {

                          //      $('.modal-for-quantity').removeClass('d-block').modal('hide')
                          //      $('.modal-for-non-repeating').removeClass('d-block').modal('hide')
                        //  //  })

                          
                         

                          



                            $(document).on('click', '.save-sub-item-edit', function(e) {
						
							 let formId = $(this).data('id');
                                let currentSubItemName = $(this).data('sub-item-name');
							   currentForm = document.getElementById('edit-sub-item-form' + formId + convertStringToClass(currentSubItemName));
									
                                dataForm = new FormData(currentForm);
                                // submit main table inputs 
                              
                                $.ajax({
                                    type: 'POST'
                                    , url: $(currentForm).attr('action')
                                    , data: dataForm
                                    , cache: false
                                    , contentType: false
                                    , processData: false
                                    , success: function(res) {
                                        window.location.reload()
                                        if (res.status) {

                                            Swal.fire({
                                                icon: 'success'
                                                , title: res.message,
												  showConfirmButton: false,
												timer: 2000
                                                // text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
												
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
												  showConfirmButton: false,
												timer: 2000
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        }
                                    }
                                    , error: function(data) {

                                    }
                                });
								
								return ;
								
                            });




                            $(document).on('click', '.save-sub-item-delete', function(e) {
								// console.log('save edit');
                                e.preventDefault();
                                let id = $(this).data('id');
                                let subItemName = $(this).data('sub-item-name');
						
                                $(this).prop('disabled', true);

                                let form = document.getElementById('delete-sub-item-form' + id + convertStringToClass(subItemName));
						
                                var formData = new FormData(form);
			
                          
                                $.ajax({
                                    type: 'POST'
                                    , url: $(form).attr('action')
                                    , data: formData
                                    , cache: false
                                    , contentType: false
                                    , processData: false
                                    , success: function(res) {
                                        $(this).prop('disabled', false);

                                        window.location.reload()
                                        if (res.status) {

                                            Swal.fire({
                                                icon: 'success'
												
                                                , title: res.message,
												  showConfirmButton: false,
												timer: 2000
                                                // text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
												  showConfirmButton: false,
												timer: 2000

                                            })
                                        }
                                    }
                                    , error: function(data) {
                                        $(this).prop('disabled', false);

                                    }
                                });
                            });








                            $(document).on('keyup', '.how-many-class', function() {
								// console.log('how many1')
                                let index = parseInt(this.getAttribute('data-id'));
                                let currentHowMany = parseInt(document.querySelector('.how-many-class[data-id="' + index + '"]').value);
                                let currentHowManyInstances = $('.how-many-item[data-id="' + index + '"]').length;
                                let financialStatementAbleItemId = this.closest('form').getAttribute('data-financial_statement_able_item_id')
                                if (currentHowMany < 1) {
                                    currentHowMany = 1;
                                }
								
                                if (currentHowManyInstances == currentHowMany) {
                                    return;
                                }
                                if (currentHowManyInstances >= currentHowMany) {
                                    document.querySelectorAll('.how-many-item[data-id="' + index + '"]').forEach(function(val, index) {
                                        var order = index + 1;
                                        if (order > currentHowMany) {
                                            $(val).remove();
                                        }
                                    })
                                } else {
                                    let numberOfNewInstances = currentHowMany - currentHowManyInstances;
								
                                    for (i = 0; i < numberOfNewInstances; i++) {
						
                                        var lastInstanceClone = $('.how-many-item[data-id="' + index + '"]:last-of-type').clone(true);
										
                                        var lastItemIndex = parseInt($('.how-many-item[data-id="' + index + '"]:last-of-type').attr('data-index'));
                                        $(lastInstanceClone).attr('data-index', lastItemIndex + 1);
                                        $(lastInstanceClone).find('.modal-for-quantity').attr('id', 'modal-for-quantity-' + (lastItemIndex + 1))
                                        $(lastInstanceClone).find('.add-sub-item-modal').attr('id', 'add-sub-modal' + (lastItemIndex + 1))
                                        $(lastInstanceClone).find('[data-index]').each(function(loopIndex, dataIndexElement) {
                                            var loopCurrentIndex = $(dataIndexElement).attr('data-index');
                                            if (isNumeric(loopCurrentIndex)) {

                                                $(dataIndexElement).attr('data-index', lastItemIndex + 1)
                                            }
                                        })
                                        lastInstanceClone.find('input,select').each(function(i, v) {
                                            if ($(v).attr('type') == 'text') {
                                                $(v).val('');
                                            }
                                            if (v.tagName.toLowerCase() == 'select') {
                                                var name = $(v).attr('name').replace(lastItemIndex, lastItemIndex + 1);
                                                var sub_items_options = '';
                                                var sub_items_quantity_options = '';

                                                let corporateTaxesId = $('#corporate-taxes-id').val();

                                                if (financialStatementAbleItemId == corporateTaxesId) {
                                                    sub_items_options += '<option value="Earning Before Taxes - EBT" selected>Earning Before Taxes - EBT</option>'
                                                } else {
                                                    window['sales_revenues_sub_items_names'].forEach(function(MainItemObject) {
                                                        sub_items_options += '<option value="' + MainItemObject.id.toString() + '">' + MainItemObject.name + '</option>'
                                                    })

                                                    window['sales_revenues_quantity_sub_items_names'].forEach(function(MainItemObject) {
                                                        sub_items_quantity_options += '<option value="' + MainItemObject.id.toString() + '">' + MainItemObject.name + '</option>'
                                                    })

                                                }

                                                if (v.closest('.dropdown.bootstrap-select')) {
                                                    v.closest('.dropdown.bootstrap-select').outerHTML = `<select  multiple name="${name}" class="select select2-select2 ${name}"> ${name.includes('is_cost_of_unit_of') ? sub_items_quantity_options :sub_items_options} </select>`
                                                } else {
                                                    if ($(v).attr('name')) {
                                                        $(v).attr('name', $(v).attr('name').replace(lastItemIndex, lastItemIndex + 1));
                                                    }


                                                }

                                            } else {
                                                if ($(v).attr('name')) {
                                                    $(v).attr('name', $(v).attr('name').replace(lastItemIndex, lastItemIndex + 1));

                                                }
                                            }
                                        })
										
                                        $('.append-names[data-id="' + index + '"]').append(lastInstanceClone);
                                        reinitializeSelect2();

                                    }
                                }
                            });

                        });


                    })(jQuery);

                });

                function getSearchInputSelector(tableId) {
					// console.log('get fixed22');
                    return tableId + '_filter' + ' label input';
                }

              



              


                function isActualDate(date) {
					return !!datesWithIsActual[date]
                }

            


            </script>

            <script>
              

                function getFinancialIncomeOrExpenseCheckBoxes(editMode, pivot, id) {
					// console.log('get checkbox')
                    if (id != domElements.financialIncomeOrExpensesId) {
                        return '';
                    }
                    const isExpense = editMode && pivot.is_financial_expense
                    return `
					<div class="financial-income-or-expense-id only-one-checkbox-parent d-flex mb-2 ${editMode ?'mt-3':''}">
					<div class="d-flex flex-column align-items-center justify-content-center flex-wrap mr-5 ">
					<label >{{ __('Income') }}</label>
					<input ${!isExpense ? 'checked' : ''} class="only-one-checkbox"   type="checkbox" value="1" name="sub_items[0][is_financial_income]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">	
					</div>
					
					<div class="d-flex flex-column align-items-center justify-content-center flex-wrap ">
							<label >{{ __('Expense') }}</label>
					<input ${isExpense ? 'checked':''} class="is-financial-expense-class only-one-checkbox" type="checkbox" value="1" name="sub_items[0][is_financial_expense]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">	
					</div>
					</div>
					
					`;
                }

                function getVatRate(editModel, pivot, data) {
					// console.log('log vat rate')
                    if (vars.subItemType != 'forecast') {
                        return '';
                    }

                    var incomeStatementId = data.isSubItem ? data.pivot.financial_statement_able_id : $('#model-id').val();
                    var incomeStatementItemId = data.isSubItem ? data.pivot.financial_statement_able_item_id : data.id;
                    var oldVatRate = editModel && pivot.vat_rate ? pivot.vat_rate : 0;
                    var isDeductable = editModel ? pivot.is_deductible : 0
                    var isDepreciationOrAmortization = editModel ? pivot.is_depreciation_or_amortization : 0
					isDepreciationOrAmortization = +isDepreciationOrAmortization;
                    isDeductable = +isDeductable;
                    let hasVatRate = vatRateMaps[incomeStatementItemId].has_vat_rate;

                    let canBeDeductable = vatRateMaps[incomeStatementItemId].can_be_dedictiable;

                    var deductableCheckbox = canBeDeductable ? `<label for="dedictiable-for-${incomeStatementId}-element2-${incomeStatementItemId}" class="label" style="margin-right:5px;margin-bottom:0">{{ __('Is Deductible') }}</label>
																	<input ${isDeductable ? 'checked'  : false } id="dedictiable-for-${incomeStatementId}-element2-${incomeStatementItemId}" class="form-control vat-rate-value " type="checkbox" value="1" name="sub_items[0][is_deductible]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">` : '';
                    var spacer = editModel ? `<div style="height:20px"></div>` : ''
                    var vatFields = hasVatRate ? ` ${spacer} <div class="checkboxes-vat ${isDepreciationOrAmortization ? 'd-none' : ''}">
																<div class="checkboxes-vat-content d-flex align-items-center"> 
																	<label for="dedictiable-for-${incomeStatementId}-element-${incomeStatementItemId}" class="label" style="margin-bottom:0">{{ __('Vat Rate %') }}</label>
																	<input ${isDepreciationOrAmortization ? 'readonly' :'' } id="dedictiable-for-${incomeStatementId}-element-${incomeStatementItemId}"  style="margin-right:10px;width:70px;margin-left:15px;" type="text" class="form-control only-percentage-allowed" value="${oldVatRate}" name="sub_items[0][vat_rate]">
																		${deductableCheckbox}
																 </div>
															</div>` : '';


                    return vatFields;

                }

                function getSalesRevenueModal(editModal, pivot = null, id) {
					// console.log('log revenue model')
                    let salesRevenueQuantityDateValues = editModal && pivot && pivot.quantityPivot ? pivot.quantityPivot : {}
                  
                    let pivotFormatted = editModal && pivot && pivot.payload ? JSON.parse(pivot.payload) : {}
                    let subItemName = editModal && pivot && pivot.payload ? pivot.sub_item_name : 'new';
                    let subItemId = editModal && pivot && pivot.payload ? pivot.id : 0;
                    let currentValueForValueOrQuantityOrPrice = editModal && pivot && pivot.payload && pivot.payload.is_value_quantity_price ? pivot.payload.is_value_quantity_price : 'value';
                
                    let thsForHeader = '<th class="text-white"> {{ __("Item") }} <input type="text" style="height:0;overflow:hidden;width:0;background-color:transparent;border:none;color:transparent;" class="value_quantity_price-id" value="' + currentValueForValueOrQuantityOrPrice + '" name="sub_items[0][is_value_quantity_price]"> </th>';
                    let thdClass = 'view-table-th header-th  text-nowrap sorting_disabled  reset-table-width cursor-pointer sub-text-bg text-capitalize';
                    let tdForBodyValue = '<td>{{ __("Value") }}</td>';
                    let tdForBodyQuantity = '<td>{{ __("Quantity") }}</td>';
                    let tdForBodyPrice = '<td>{{ __("Price") }} </td>';
					var totalForValue =  0 ; 
					var totalForQuantity = 0 ; 
					var totalForPrice = 0 ; 
                    for (date of dates) {
                        var salesQuantityAtDate = editModal && salesRevenueQuantityDateValues[date] ? salesRevenueQuantityDateValues[date] : 0;
                        salesQuantityAtDate = parseFloat(salesQuantityAtDate)
						totalForQuantity+= salesQuantityAtDate;
                        var valueAtDate = editModal && pivotFormatted[date] ? pivotFormatted[date] : 0;
                        valueAtDate = parseFloat(valueAtDate)
						totalForValue+=valueAtDate
                        var priceAtDate = editModal && salesQuantityAtDate ? valueAtDate / salesQuantityAtDate : 0;
						
						var disabledInput = isDisabledInput(date)
                        thsForHeader += '<th class="' + thdClass + '" data-date="' + date + '">' + datesFormatted[date] + '</th>'
                        tdForBodyValue += `<td class="" data-type="value"  data-date="${date}">
							<input ${disabledInput ? 'readonly' : ''} data-in-edit-mode="${editModal}" data-in-edit-mode="${subItemId}"  style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type="value" class="val-input hidden-for-popup form-control blured-item" type="text"  value="${number_format(valueAtDate,0)}" > 
							<input ${disabledInput ? 'readonly' : ''} data-in-edit-mode="${editModal}" data-in-edit-mode="${subItemId}"  style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type="value" class="val-input hidden-for-popup pr-0" type="hidden" name="sub_items[0][val][${date}]" value="${valueAtDate}" > 
							<i class="fa fa-ellipsis-h repeat-row" data-column-index="${date}" data-index="value" data-parent-query="tr"  title="{{__('Repeat Right')}}"></i>
							
						  </td> `
                        tdForBodyQuantity += `<td class="" data-type="quantity"  data-date="${date}"> 
						
						<input ${disabledInput ? 'readonly' : ''} data-current-value="${salesQuantityAtDate}" data-in-edit-mode="${editModal}" data-sub-item-id="${subItemId}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type="quantity" class="quantity-input hidden-for-popup form-control blured-item" type="text"  value="${salesQuantityAtDate}" >
						<input ${disabledInput ? 'readonly' : ''} data-current-value="${salesQuantityAtDate}" data-in-edit-mode="${editModal}" data-sub-item-id="${subItemId}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type="quantity" class="quantity-input hidden-for-popup" type="hidden" name="sub_items[0][quantity][${date}]" value="${salesQuantityAtDate}" >
						<i class="fa fa-ellipsis-h repeat-row" data-column-index="${date}" data-index="value" data-parent-query="tr"  title="{{__('Repeat Right')}}"></i>
						
						</td> `
                        tdForBodyPrice += `<td class="" data-type="price"  data-date="${date}">
						
						<input ${disabledInput ? 'readonly' : ''} data-current-value="${priceAtDate}" data-in-edit-mode="${editModal}" data-sub-item-id="${subItemId}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" class="price-input hidden-for-popup form-control blured-item"  data-type="price" type="text"  value="${number_format(priceAtDate,0)}" >
						<input ${disabledInput ? 'readonly' : ''} data-current-value="${priceAtDate}" data-in-edit-mode="${editModal}" data-sub-item-id="${subItemId}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" class="price-input hidden-for-popup" data-type="price" type="hidden" name="sub_items[0][price][${date}]" value="${priceAtDate}" >
						<i class="fa fa-ellipsis-h repeat-row" data-column-index="${date}" data-index="value" data-parent-query="tr"  title="{{__('Repeat Right')}}"></i>
						
						</td> 
						`;
						
                    }
					totalForPrice = totalForQuantity ? totalForValue / totalForQuantity : 0;
                    thsForHeader += "<th class='text-white text-center'>{{ __('Total') }}</th>";


                    tdForBodyPrice += `<td class="" data-type="price"> 
					<input value="${number_format(totalForPrice)}" readonly type="text" class="form-control pr-0 total-for-price" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';"> 
					<i style="visibility:hidden" class="fa fa-ellipsis-h" ></i>
						
						
					</td>`

                    tdForBodyPrice = '<tr data-equation="value / quantity" data-number-format="2" class="price" data-type="price" >' + tdForBodyPrice + '</tr>'


                    tdForBodyQuantity += `<td class="" data-type="quantity"> <input value="${number_format(totalForQuantity)}" readonly type="text" class="form-control pr-0 total-for-quantity" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';">
						<i style="visibility:hidden" class="fa fa-ellipsis-h " ></i>
					 </td>`

                    tdForBodyQuantity = '<tr data-equation="value / price" data-number-format="0" class="quantity" data-type="quantity">' + tdForBodyQuantity + '</tr>'
                    tdForBodyValue += `<td class="" data-type="value"> <input value="${number_format(totalForValue)}" readonly type="text" class="form-control pr-0 total-for-value" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';"> 
					<i style="visibility:hidden" class="fa fa-ellipsis-h " ></i>
					
					</td>`
                    tdForBodyValue = '<tr data-equation="quantity * price" data-number-format="0" class="value" data-type="value">' + tdForBodyValue + '</tr>'
                    editModal = editModal ? 1 : 0;
			
                    if (!salesRevenueModalTdData[editModal]) {
                        salesRevenueModalTdData[editModal] = {
                            [subItemName]: {
                                price: tdForBodyPrice
                                , quantity: tdForBodyQuantity
                                , value: tdForBodyValue
                            }
                        }
                    } else if (!salesRevenueModalTdData[editModal][subItemName]) {
                        salesRevenueModalTdData[editModal][subItemName] = {
                            price: tdForBodyPrice
                            , quantity: tdForBodyQuantity
                            , value: tdForBodyValue
                        }
                    }
					else  {
                        salesRevenueModalTdData[editModal][subItemName] = {
                            price: tdForBodyPrice
                            , quantity: tdForBodyQuantity
                            , value: tdForBodyValue
                        }
                    }
			
                    return `
					<div class="quantity-section ">
						<div class="checkboxes-for-quantity only-two-checkbox-parent mt-4">
							<div class="quantity-checkbox-div">
							<label >{{ __('Value') }}</label>
								<input data-sub-item-name="${editModal ? pivot.sub_item_name : 'new'}" data-sub-item-id="${subItemId}" data-in-edit-mode="${editModal }" class="only-two-checkbox 	" type="checkbox" value="value"  style="width:16px;height:16px;" name="sub_items[0][is_quantity]" ${editModal && pivot.is_value_quantity_price&& pivot.is_value_quantity_price.includes('value') ? 'checked' : '' } ${!editModal ? '' : ''}>
							</div>
							<div class="quantity-checkbox-div">
								<label >{{ __('Quantity') }}</label>
								<input data-sub-item-name="${editModal ? pivot.sub_item_name : 'new'}" data-sub-item-id="${subItemId}" data-in-edit-mode="${editModal }" class="only-two-checkbox can-trigger-quantity-modal" type="checkbox" value="quantity"  style="width:16px;height:16px;" name="sub_items[0][is_quantity]" ${editModal &&  pivot.is_value_quantity_price && pivot.is_value_quantity_price.includes('quantity') ? 'checked' : ''}>
							</div>
							<div class="quantity-checkbox-div">
								<label >{{ __('Price') }}</label>
								<input data-sub-item-name="${editModal ? pivot.sub_item_name : 'new'}" data-sub-item-id="${subItemId}" data-in-edit-mode="${editModal}" class="only-two-checkbox can-trigger-quantity-modal" type="checkbox" value="price"  style="width:16px;height:16px;" name="sub_items[0][is_quantity]" ${editModal && pivot.is_value_quantity_price&& pivot.is_value_quantity_price.includes('price') ? 'checked' : ''}>
							</div>
						</div>
						
						
						<div id="modal-for-quantity-0" data-sub-id="${editModal ? pivot.id : 0}" class="modal fade modal-for-quantity" data-index="0"  tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" >
  <div class="modal-dialog modal-dialog-centered custom-modal-w-h "  role="document">
    <div class="modal-content" style="overflow-x:scroll">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Values And Quantities</h5>
        <button type="button"  class="close-inner-modal close" data-dismiss="modal" aria-label="Close">
          <span >&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-striped-  table-hover table-checkable position-relative dataTable no-footer dtr-inline">
			<thead>
				<tr class="header-tr">
					${thsForHeader}
				</tr>
			</thead>
			<tbody class="append-sales-revenue-modal-table-body" data-id="${id}" data-index="0">
				
			</tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button  type="button" class="btn btn-secondary close-inner-modal" >Close</button>
        <button  type="button" class="btn btn-primary close-inner-modal">Save</button>
      </div>
    </div>
  </div>
</div>

					</div>
					
					
					
					
					`;
                }


				function isDisabledInput(date){
							let reportType =vars.subItemType
					var currentDateIsActual = isActualDate(date);
					return reportType =='actual' && !currentDateIsActual || reportType =='modified' && currentDateIsActual
				}

                function getNonRepeatingModal(editModal, pivot = null, id) {
					// console.log('getNonRepeatingModal')
                    //return '';
              
                    let pivotFormatted = editModal && pivot && pivot.payload ? JSON.parse(pivot.payload) : {}
                    let subItemName = editModal && pivot && pivot.payload ? pivot.sub_item_name : 'new';
                    var isDeductable = editModal && pivot && pivot.payload ? pivot.is_deductible : 0
                    let vatRate = editModal && pivot && pivot.payload && !isDeductable ? pivot.vat_rate : 0;
               
                    let thsForHeader = '<th class="text-white"> {{ __("Item") }}  </th>';
                    let thdClass = 'view-table-th header-th  text-nowrap sorting_disabled  reset-table-width cursor-pointer sub-text-bg text-capitalize';
                    let tdForBodyValue = '<td>{{ __("Value") }}</td>';
					let reportType = vars.subItemType
					// console.warn(reportType);
					var totalForNonRepeating = 0 ;
                    for (date of dates) {

                        var valueAtDate = editModal && pivotFormatted[date] ? pivotFormatted[date] : 0;
                        valueAtDate = parseFloat(valueAtDate)
						valueAtDate = editModal ? valueAtDate / (1+(vatRate/100)) : valueAtDate;
						totalForNonRepeating+=valueAtDate;
                        thsForHeader += '<th class="' + thdClass + '" data-date="' + date + '">' + datesFormatted[date] + '</th>'
						var disabledInput =isDisabledInput(date) ;
                        tdForBodyValue += `<td data-id="${id}" class="" data-type="value"  data-date="${date}">
							<input ${disabledInput ? 'readonly ' :'' } data-id="${id}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type-non-repeating="value" class="val-input hidden-for-popup-non-repeating blured-item-non-repeating form-control " type="text"  value="${number_format(valueAtDate,0)}" > 
							<input ${disabledInput ? 'readonly ' :'' } data-id="${id}" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';" data-date="${date}" data-type-non-repeating="value" class="val-input hidden-for-popup-non-repeating pr-0" type="hidden" name="sub_items[0][non_repeating_popup][${date}]" value="${valueAtDate}" > 
							<i class="fa fa-ellipsis-h repeat-row" data-column-index="${date}" data-index="value" data-parent-query="tr"  title="{{__('Repeat Right')}}"></i>
							
						  </td> `

                    }
                    thsForHeader += "<th class='text-white text-center'>{{ __('Total') }}</th>";






                    tdForBodyValue += `<td class="" data-type="value"> <input value="${number_format(totalForNonRepeating)}" readonly type="text" class="form-control pr-0 total-for-non-repeating-value" style="min-width: 80px" onchange="this.style.width = ((this.value.length + 1) * 10) + 'px';" onblur="this.style.width = ((this.value.length + 1) * 10) + 'px';" onkeyup="this.style.width = ((this.value.length + 1) * 10) + 'px';"> 
					<i style="visibility:hidden" class="fa fa-ellipsis-h " ></i>
					
					</td>`
                    tdForBodyValue = '<tr data-number-format="0" class="value" data-type="value">' + tdForBodyValue + '</tr>'
                    editModal = editModal ? 1 : 0;
                    if (!nonRepeatingModalTdData[editModal]) {
                        nonRepeatingModalTdData[editModal] = {
                            [subItemName]: {
                                value: tdForBodyValue
                            }
                        }
                    } else if (!nonRepeatingModalTdData[editModal][subItemName]) {
                        nonRepeatingModalTdData[editModal][subItemName] = {
                            value: tdForBodyValue
                        }
                    }

                 

                    let result = `<div class="non-repeating-section ">
						
						
						<div id="modal-for-non-repeating-0" data-sub-id="${editModal ? pivot.id : 0}" data-edit-model="${editModal}" class="modal fade modal-for-non-repeating" data-index="0"  tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" >
  <div class="modal-dialog modal-dialog-centered custom-modal-w-h "  role="document">
    <div class="modal-content" style="overflow-x:scroll">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Non Repeating</h5>
        <button type="button"  class="close-inner-modal close" data-dismiss="modal" aria-label="Close">
          <span >&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-striped-  table-hover table-checkable position-relative dataTable no-footer dtr-inline">
			<thead>
				<tr class="header-tr">
					${thsForHeader}
				</tr>
			</thead>
			<tbody class="append-non-repeating-modal-table-body"  data-id="${id}" data-index="0">
				${tdForBodyValue}
			</tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary close-inner-modal" >Close</button>
        <button type="button"   class="btn btn-primary close-inner-modal">Save</button>
      </div>
    </div>
  </div>
</div>

					</div>`;
                    return result;
                }


                function getCollectionPolicyHtml(editMode, pivot = null, id) {
					// console.log('getCollectionPolicyHtml')
                    let valueOfCustom = [];
					var hasCollectionPolicy = pivot && pivot && pivot.has_collection_policy ;
                    let isCustom =  hasCollectionPolicy && pivot.collection_policy_type == 'customize'
                    let isSystemDefault = hasCollectionPolicy && pivot.collection_policy_type == 'system_default'
                    let collectionOrPayment = id == domElements.salesRevenueId ? 'Collection' : 'Payment'
                    if (isCustom) {
                        valueOfCustom = JSON.parse(pivot.collection_policy_value)
                    }
                    let collectionRates = ``
                    let dueInDays = ``
					let currentCollectionTotal = 0 ;
                    for (let i = 0; i < 5; i++) {
						var hasDue = isCustom && valueOfCustom.due_in_days && valueOfCustom.due_in_days[i];
						var currentCollectionValue = parseFloat(isCustom && valueOfCustom.rate && valueOfCustom.rate[i]?valueOfCustom.rate[i] :0)  ;
						currentCollectionTotal+=currentCollectionValue;
                        collectionRates += `<div class="collection-rate-item mb-3">
						
												<input class="form-control collection_rate_input" type="text" name="sub_items[0][collection_policy][type][customize][value][rate][${i}]" style="width:100px;" value="${currentCollectionValue}">
											</div>`
                        dueInDays += `<div class="collection-rate-item mb-3">
												<select name="sub_items[0][collection_policy][type][customize][value][due_in_days][${i}]" class="form-control">
													<option ${ hasDue && valueOfCustom.due_in_days[i]==0?'selected':'' } value="0">0</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==15 ? 'selected' :''} value="15">15</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==30 ? 'selected' :''} value="30">30</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==45 ? 'selected' :''} value="45">45</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==60 ? 'selected' :''} value="60">60</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==75 ? 'selected' :''} value="75">75</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==90 ? 'selected' :''} value="90">90</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==120? 'selected' :'' } value="120">120</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==150? 'selected' :'' } value="150">150</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==180? 'selected' :'' } value="180">180</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==210? 'selected' :'' } value="210">210</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==240? 'selected' :'' } value="240">240</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==270? 'selected' :'' } value="270">270</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==300? 'selected' :'' } value="300">300</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==330? 'selected' :'' } value="330">330</option>
													<option ${hasDue && valueOfCustom.due_in_days[i]==360? 'selected' :'' } value="360">360</option>
												</select>
											</div>`
                    }

                    return `
					<div class="collection-policy ${pivot && pivot.is_depreciation_or_amortization ? 'd-none' : 'd-flex'}  flex-wrap w-100 mt-3 ${id == domElements.salesRevenueId && editMode  ? 'pl-25' :''}">
						<div class="collection-policy-header basis-100 mb-4">
							<div class="check-boxes">
								<div class="checkbox-item d-flex ">
									<label class="form-label label  mr-3">{{ __('Has ${collectionOrPayment} Policy') }}</label>
									<input checked type="checkbox" style="width:16px;height:16px;" name="" class="checkbox has-collection-policy-class form-control" checked  value="1">
									<input type="hidden" class="has_collection_policy_input" name="sub_items[0][collection_policy][has_collection_policy]" value="1">
								</div>
							</div>
						</div>
						<div class="collection-policy-content basis-100  only-one-checked-parent">
							<div class="collection-policy-wrapper ">
								<div class="collection-policy-checkboxes d-flex parent-for-checkbox">
									<div class="checkbox-item d-flex mr-3">
									<label class="form-label label  mr-3">{{ __('System Default') }}</label>
									<input data-index="0" ${isSystemDefault || !isCustom ? 'checked' : ''} type="checkbox" style="width:16px;height:16px;" class="checkbox only-one-checked form-control checkedbox checkbox-for-policy system_default" name="sub_items[0][collection_policy][type][name]" value="system_default">
								</div>
								
								<div class="checkbox-item d-flex ">
									<label class="form-label label  mr-3">{{ __('Customize') }}</label>
									<input data-index="0" ${isCustom ? 'checked' : ''} type="checkbox" style="width:16px;height:16px;" class="checkbox only-one-checked form-control checkedbox  checkbox-for-policy customize" name="sub_items[0][collection_policy][type][name]"  value="customize">
								</div>
								
								</div>
								
							</div>
							
							<div class="checkboxes-content d-flex mt-4">
								<div class="basis-100 for-only-one-checked ${isCustom ? 'd-none' : ''}" data-item="system_default">
										<div class="system-default-select">
											<select name="sub_items[0][collection_policy][type][system_default][value]" class="select form-control">
												<option ${isSystemDefault && pivot.collection_policy_value =='monthly' ? 'selected' : ''} value="monthly">{{ __('Cash') }}</option>
												<option ${isSystemDefault && pivot.collection_policy_value =='quarterly' ? 'selected' : ''} value="quarterly">{{ __('Quarterly') }}</option>
												<option ${isSystemDefault && pivot.collection_policy_value =='semi-annually' ? 'selected' : ''} value="semi-annually">{{ __('Semi-annually') }}</option>
												<option ${isSystemDefault && pivot.collection_policy_value =='annually' ? 'selected' : ''} value="annually">{{ __('Annually') }}</option>
											</select>
										</div>
								</div>
								<div class="basis-100 for-only-one-checked ${isCustom ? '' : 'd-none'} " data-item="customize">
									<div class="customize-content" style="display:flex;gap:50px;">
										<div class="collection-rate d-flex flex-column ">
											<h5 class="mb-3 label form-label">{{ __('${collectionOrPayment} Rate %') }} </h5>
											${collectionRates}
											<label class="label form-label">{{ __('Total') }}</label>
											<input style="width:100px;" value="${currentCollectionTotal}" readonly class="form-control collection_rate_total_class" name="sub_items[0][collection_rate_total][]">
										</div>
										<div class="due-in-days d-flex flex-column">
											<h5 class="label form-label mb-3">{{ __('Due In Days') }}</h5>
											${dueInDays}
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					${id == domElements.marketExpensesId || id == domElements.costOfGoodsId|| id == domElements.financialIncomeOrExpensesId|| id == domElements.financialIncomeOrExpensesId || id == domElements.generalExpensesId|| id == domElements.salesAndDistributionExpensesId   ? '</div>' : ''}
					
					`;
                }




            </script>
            <script>


            </script>


        </x-slot>

    </x-tables.basic-view>
</div>
