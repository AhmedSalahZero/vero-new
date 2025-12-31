@php
$tableId = 'kt_table_1';
@endphp


<style>
    /* table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control::before, table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control::before,
    .dataTables_wrapper table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td:first-child::before
    {
        content:none ;
    } */
    .modal-backdrop {
        display: none !important;
    }

    .main-with-no-child {
        background-color: rgb(238, 238, 238) !important;
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
        background-color: #0e96cd !important;
        color: white !important;
		
		
		background-color:#E2EFFE !important;
		color:black !important
		

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
<input type="hidden" id="editable-by-btn" value="1">
<div class="table-custom-container position-relative  ">
    <input type="hidden" value="{{ $financialStatement->id }}" id="model-id">
    <input type="hidden" id="financial-statement-duration-type" value="{{ $financialStatement->duration_type ?? '' }}">

    {{-- <input type="hidden" id="cost-of-goods-id"  value="{{ \App\Models\FinancialStatementItem::COST_OF_GOODS_ID }}">
    <input type="hidden" id="sales-growth-rate-id" value="{{ \App\Models\FinancialStatementItem::SALES_GROWTH_RATE_ID }}">
    <input type="hidden" id="sales-revenue-id" value="{{ \App\Models\FinancialStatementItem::SALES_REVENUE_ID }}">
    <input type="hidden" id="gross-profit-id" value="{{ \App\Models\FinancialStatementItem::GROSS_PROFIT_ID }}">
    <input type="hidden" id="market-expenses-id" value="{{ \App\Models\FinancialStatementItem::MARKET_EXPENSES_ID }}">
    <input type="hidden" id="sales-and-distribution-expenses-id" value="{{ \App\Models\FinancialStatementItem::SALES_AND_DISTRIBUTION_EXPENSES_ID }}">
    <input type="hidden" id="general-expenses-id" value="{{ \App\Models\FinancialStatementItem::GENERAL_EXPENSES_ID }}">
    <input type="hidden" id="earning-before-interest-taxes-depreciation-amortization-id" value="{{ \App\Models\FinancialStatementItem::EARNING_BEFORE_INTEREST_TAXES_DEPRECIATION_AMORTIZATION_ID }}">
    <input type="hidden" id="earning-before-interest-taxes-id" value="{{ \App\Models\FinancialStatementItem::EARNING_BEFORE_INTEREST_TAXES_ID }}">
    <input type="hidden" id="financial-income-or-expenses-id" value="{{ \App\Models\FinancialStatementItem::FINANCIAL_INCOME_OR_EXPENSE_ID }}">
    <input type="hidden" id="earning-before-taxes-id" value="{{ \App\Models\FinancialStatementItem::EARNING_BEFORE_TAXES_ID }}">
    <input type="hidden" id="corporate-taxes-id" value="{{ \App\Models\FinancialStatementItem::CORPORATE_TAXES_ID }}">
    <input type="hidden" id="net-profit-id" value="{{ \App\Models\FinancialStatementItem::NET_PROFIT_ID }}">
    <input type="hidden" id="sales-rate-maps" value="{{ json_encode(\App\Models\FinancialStatementItem::salesRateMap()) }}">
    <script>
        let sales_rates_maps = document.getElementById('sales-rate-maps').value;
        const sales_rate_maps = JSON.parse(sales_rates_maps);

    </script> --}}



    <x-tables.basic-view :form-id="'store-report-form-id'" :wrap-with-form="true" :form-action="route('admin.store.financial.statement.report',['company'=>getCurrentCompanyId()])" class="position-relative table-with-two-subrows main-table-class" id="{{ $tableId }}">
        <x-slot name="filter">
            @include('admin.financial-statement.report.filter' , [
            'type'=>'filter'
            ])
        </x-slot>

        <x-slot name="export">
            @include('admin.financial-statement.report.export' , [
            'type'=>'export'
            ])
        </x-slot>


        <x-slot name="headerTr">
            <tr class="header-tr " data-model-name="{{ $modelName }}">
                <input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">


                <th class="view-table-th header-th trigger-child-row-1">
                    {{ __('Expand') }}
                </th>

                <th class="view-table-th header-th" data-db-column-name="id" data-is-relation="0" class="header-th" data-is-json="0">
                    {{ __('Actions') }}
                </th>
                <th class="view-table-th header-th" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="BussinessLineName" data-is-relation="1" class="header-th" data-is-json="0">
                    {{ __('Name') }}
                    {{-- {!!  !!} --}}
                </th>
                <input type="hidden" name="dates[]" value="{{ json_encode(array_keys($financialStatement->getIntervalFormatted())) }}" id="dates">
                @foreach($financialStatement->getIntervalFormatted() as $defaultDateFormate=>$interval)
                <th data-date="{{ $defaultDateFormate }}" data-month-year="{{explode('-',$defaultDateFormate)[0].'-'.explode('-',$defaultDateFormate)[1]}}" class="view-table-th header-th" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="ServiceCategory" data-is-relation="1" class="header-th" data-is-json="0">
                    {{ $interval }}
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
                window.addEventListener('DOMContentLoaded', function() {
                    (function($) {
                        function formatsubrow1(d, dates) {

                            // `d` is the original data object for the row
                            let subtable = `<table id="subtable-1-id${d.id}" class="subtable-1-class table table-striped-  table-hover table-checkable position-relative dataTable no-footer dtr-inline" > <thead style="display:none"><tr><td></td><td></td><td></td><td></td><td></td>
    <td></td> <td></td><td></td>  `;
                            for (date in dates) {
                                subtable += ' <td> </td>';
                            }
                            subtable += ` </tr> </thead> `;

                            subtable += '</table>';
                            return (subtable);
                        }






                        // Add event listener for opening and closing details


                        $(document).on('click', '.filter-btn-class', function(e) {
                            e.preventDefault();

                            $('#loader_id').removeClass('hide_class');
                            const interval = $('select[name="interval_view"]').val();
                            formatDatesForInterval(interval);
                            $(document).trigger('click');
                            $('#loader_id').addClass('hide_class')

                        });

                        $(document).on('click', function(e) {
                            // close opened custom modal [for filter and export btn]
                            let target = e.target;
                            if (!$(target).closest('.close-when-clickaway').length && !$(target).closest('.do-not-close-when-click-away').length) {
                                $('.close-when-clickaway').addClass('d-none');
                            }
                        });


                        $(document).on('click', '.trigger-child-row-1', function(e) {
                            const parentId = $(e.target.closest('tr')).data('model-id');
                            var parentRow = $(e.target).parent();
                            var subRows = parentRow.nextAll('tr.add-sub.maintable-1-row-class' + parentId);

                            subRows.toggleClass('d-none');
                            if (subRows.hasClass('d-none')) {
                                parentRow.find('td.trigger-child-row-1').html('+');
                            } else if (!subRows.length) {
                                // if parent row has no sub rows then remove + or - 
                                parentRow.find('td.trigger-child-row-1').html('×');
                            } else {
                                parentRow.find('td.trigger-child-row-1').html('-');
                            }

                        });

                        "use strict";
                        var KTDatatablesDataSourceAjaxServer = function() {

                            var initTable1 = function() {
                                var tableId = '#' + "{{ $tableId }}";

                                var table = $(tableId);
                                let data = $('#dates').val();
                                data = JSON.parse(data);
                                window['dates'] = data;
                                const columns = [];
                                columns.push({
                                    data: 'id'
                                    , searchable: false
                                    , orderable: false
                                    , className: 'trigger-child-row-1 cursor-pointer sub-text-bg'
                                    , render: function(d, b, row) {
                                        if (!row.isSubItem && row.has_sub_items) {
                                            return '+';
                                        }
                                        return '';
                                    }
                                });
                                columns.push({
                                    render: function(d, b, row) {
                                        let modelId = $('#model-id').val();

                                        if (!row.isSubItem && row.has_sub_items) {
                                            elements = `<a data-is-subitem="0" data-financial-statement-item-id="${row.id}" data-financial-statement-id="${modelId}" class="d-block add-btn mb-2" href="#" data-toggle="modal" data-target="#add-sub-modal${row.id}">{{ __('Add') }}</a> `;
                                            if (row.sub_items.length) {
                                                // elements += `<a data-is-subitem="0" data-financial-statement-item-id="${row.id}" data-financial-statement-id="${modelId}" class="d-block  text-danger" href="#" data-toggle="modal" data-target="#delete-all-sub-modal${row.id}" >{{ __('Delete') }}</a> `
                                            }
                                            return elements;
                                        } else if (row.isSubItem) {
                                            return `<a data-is-subitem="1" class="d-block edit-btn mb-2 text-white " href="#" data-toggle="modal" data-is-depreciation-or-amortization="${row.pivot.is_depreciation_or_amortization}" data-financial-statement-id="${row.pivot.financial_statement_able_id}" data-target="#edit-sub-modal${row.pivot.financial_statement_able_item_id + row.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-') }"> <i class="fa fa-pen-alt"></i>  </a> <a class="d-block  delete-btn text-white mb-2 text-danger" href="#" data-toggle="modal" data-target="#delete-sub-modal${row.pivot.financial_statement_able_item_id + row.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-') }">
                                <i class="fas fa-trash-alt"></i>
                                
                                </a>`
                                        }
                                        return '';
                                    }
                                    , data: 'id'
                                    , className: 'cursor-pointer sub-text-bg d-flex justify-content-between'
                                , });
                                columns.push({
                                    render: function(d, b, row) {
                                        this.currentRow = row;
                                        if (row.isSubItem) {
                                            return row.pivot.sub_item_name;
                                        }
                                        return row['name']

                                    }
                                    , data: 'id'
                                    , className: 'sub-text-bg text-nowrap editable editable-text is-name-cell'
                                });
                                for (let i = 0; i < data.length; i++) {
                                    columns.push({
                                        render: function(d, b, row, setting) {
                                            date = data[i];
                                            if (row.isSubItem && row.pivot.payload) {
                                                var payload = JSON.parse(row.pivot.payload);
                                                return payload[date] ? number_format(payload[date]) : 0;
                                            }
                                            if (row.has_sub_items) {
                                                let total = get_total_of_object(row.sub_items, date);
                                                return row.sub_items ? number_format(total) : 0
                                            }
                                            // if(row.sub_items && row.sub_items[0]){
                                            //     var payload = row.sub_items[0].pivot.payload ;
                                            //     return   payload ? JSON.parse(payload)[date] : 0; 
                                            // }

                                            return 0

                                        }
                                        , data: 'id'
                                        , className: 'sub-numeric-bg text-nowrap editable editable-date date-' + data[i]

                                    });




                                }

                                columns.push({
                                    render: function(d, b, row, setting) {

                                        return 0

                                    }
                                    , data: 'id'
                                    , className: 'sub-numeric-bg text-nowrap total-row'

                                })



                                // begin first table
                                table.DataTable({


                                        dom: 'Bfrtip',
                                        // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                                        "ajax": {
                                            "url": "{{ $getDataRoute }}"
                                            , "type": "post"
                                            , "dataSrc": "data", // they key in the jsom response from the server where we will get our data
                                            "data": function(d) {
                                                d.search_input = $(getSearchInputSelector(tableId)).val();
                                            }

                                        }
                                        , "processing": false
                                        , "scrollX": true
                                        , "ordering": false
                                        , 'paging': false
                                        , "fixedColumns": {
                                            left: 3
                                        }
                                        , "serverSide": true
                                        , "responsive": false
                                        , "pageLength": 25
                                        , "columns": columns
                                        , columnDefs: [{
                                            targets: 0
                                            , defaultContent: 'salah'
                                            , className: 'red reset-table-width'
                                        }]
                                        , buttons: [{
                                                "attr": {
                                                    'data-table-id': tableId.replace('#', ''),
                                                    // 'id':'test'
                                                }
                                                , "text": '<svg style="margin-right:10px;position:relative;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M5,4 L19,4 C19.2761424,4 19.5,4.22385763 19.5,4.5 C19.5,4.60818511 19.4649111,4.71345191 19.4,4.8 L14,12 L14,20.190983 C14,20.4671254 13.7761424,20.690983 13.5,20.690983 C13.4223775,20.690983 13.3458209,20.6729105 13.2763932,20.6381966 L10,19 L10,12 L4.6,4.8 C4.43431458,4.5790861 4.4790861,4.26568542 4.7,4.1 C4.78654809,4.03508894 4.89181489,4 5,4 Z" id="Path-33" fill="#000000"/></g></svg>' + '{{ __("Analysis") }}'
                                                , 'className': 'btn btn-bold btn-secondary filter-table-btn  flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away'
                                                , "action": function() {
                                                    window.location.href = '';
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
                                                "text": '<svg style="margin-right:10px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3"/><rect id="Rectangle" fill="#000000" opacity="0.3" transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) " x="11" y="2" width="2" height="12" rx="1"/><path d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/></g></svg>' + '{{ __("Export") }}'
                                                , 'className': 'btn btn-bold btn-secondary  flex-1 flex-grow-0 btn-border-radius ml-2 do-not-close-when-click-away'
                                                , "action": function() {
                                                    let form = $('form#store-report-form-id');
                                                    let oldFormAction = form.attr('action');
                                                    let exportFormAction = "{{ route('admin.export.financial.statement.report',$company->id) }}";
                                                    form.attr('action', exportFormAction);
                                                    form.submit();
                                                    form.attr('action', oldFormAction);

                                                    // $('#export_form-for-'+tableId.replace('#','')).toggleClass('d-none');
                                                }
                                            },

                                        ]
                                        , createdRow: function(row, data, dataIndex, cells, x) {

                                            let salesGrowthRateId = $('#sales-growth-rate-id').val();

                                            var totalOfRowArray = [];

                                            var financialStatementId = data.isSubItem ? data.pivot.financial_statement_able_id : $('#model-id').val();
                                            var financialStatementItemId = data.isSubItem ? data.pivot.financial_statement_able_item_id : data.id;
                                            var subItemName = data.isSubItem ? data.pivot.sub_item_name : '';
                                            $(cells).filter(".editable").attr('contenteditable', true)
                                                .attr('data-financial-statement-id', financialStatementId)
                                                .attr('data-main-model-id', financialStatementId)
                                                .attr('data-financial-statement-item-id', financialStatementItemId)
                                                .attr('data-main-row-id', financialStatementItemId)
                                                .attr('data-sub-item-name', subItemName)
                                                .attr('data-table-id', "{{$tableId}}")
                                            if (data.isSubItem) {
                                                let Depreciation = '';
                                                if (data.pivot && data.pivot.can_be_depreciation) {
                                                    let checked = '';
                                                    if (data.pivot.is_depreciation_or_amortization) {
                                                        checked = ' checked ';
                                                    }
                                                    Depreciation = `
            <label>{{ __('Is Depreciation Or Amortization ? ') }}</label>
                            
                            <input ${checked} class="form-check-input" type="checkbox" value="1" name="is_depreciation_or_amortization"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">`
                                                }


                                                $(row).append(
                                                    `
                            
                            
                    <div class="modal fade" id="edit-sub-modal${data.pivot.financial_statement_able_item_id + data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-')}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Sub Item For') }} ${data.pivot.sub_item_name} </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="edit-sub-item-form${data.pivot.financial_statement_able_item_id + data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-')  }" class="edit-submit-sub-item" action="{{ route('admin.update.financial.statement.report',['company'=>getCurrentCompanyId()]) }}">
			<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
            
            <input type="hidden" name="financial_statement_able_item_id"  value="${data.pivot.financial_statement_able_item_id}">
            <input  type="hidden" name="financial_statement_able_id"  value="{{ $financialStatement->id }}">
            <input  type="hidden" name="sub_item_name"  value="${data.pivot.sub_item_name}">
            <label>{{ __('name') }}</label>
            <input name="new_sub_item_name"  class="form-control   mb-2" type="text" value="${data.pivot.sub_item_name}">
            ${Depreciation}
           <div class="mt-2">
            <label>{{ __('Sub Of') }}</label>
            <select name="sub_of_id" class="form-control main-row-select" data-selected-main-row="${data.pivot.financial_statement_able_item_id}">
                
            </select>
            </div>
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
        <button type="button" class="btn btn-primary save-sub-item-edit" data-id="${data.pivot.financial_statement_able_item_id}" data-sub-item-name="${data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-') }">{{ __('Edit') }}</button>
      </div>
    </div>
  </div>
    </div>
    `
                                                )


                                                $(row).append(
                                                    `
                            
                            
                    <div class="modal fade" id="delete-sub-modal${data.pivot.financial_statement_able_item_id + data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-')}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Delete Sub Item ') }} ${data.pivot.sub_item_name} </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="delete-sub-item-form${data.pivot.financial_statement_able_item_id+data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-') }" class="delete-submit-sub-item" action="{{ route('admin.destroy.financial.statement.report',['company'=>getCurrentCompanyId()]) }}">
			<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
            
            <input type="hidden" name="financial_statement_able_item_id"  value="${data.pivot.financial_statement_able_item_id}">
            <input  type="hidden" name="financial_statement_able_id"  value="{{ $financialStatement->id }}">
            <input  type="hidden" name="sub_item_name"  value="${data.pivot.sub_item_name}">
            <p>{{ __('Are You Sure To Delete ') }} ${data.pivot.sub_item_name}  ? </p>
         
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
        <button type="button" class="btn btn-primary save-sub-item-delete" data-id="${data.pivot.financial_statement_able_item_id}" data-sub-item-name="${data.pivot.sub_item_name.replaceAll('/','-').replaceAll('&','-').replaceAll('%','-').replaceAll(' ','-').replaceAll('(','-').replaceAll(')','-') }" >{{ __('Delete') }}</button>
      </div>
    </div>
  </div>
    </div>
    `
                                                )

                                                $(row).addClass('edit-info-row').addClass('add-sub maintable-1-row-class' + (financialStatementItemId))
                                                $(row).addClass('d-none is-sub-row ');

                                                if (data.pivot && data.pivot.is_depreciation_or_amortization) {
                                                    $(row).addClass('is-depreciation-or-amortization')
                                                }

                                                $(cells).filter('.editable.editable-date').each(function(index, dateDt) {
                                                    var filterDate = $(dateDt).attr("class").split(/\s+/).filter(function(classItem) {
                                                        return classItem.startsWith('date-');
                                                    })[0];
                                                    filterDate = filterDate.split('date-')[1];
                                                    totalOfRowArray.push(parseFloat($(dateDt).html().replace(/(<([^>]+)>)/gi, "").replace(/,/g, "")));


                                                    var hiddenInput = `<input type="hidden" name="value[${financialStatementId}][${financialStatementItemId}][${subItemName}][${filterDate}]" data-date="${filterDate}" data-parent-model-id="${financialStatementItemId}" value="${($(dateDt).html().replace(/(<([^>]+)>)/gi, "").replace(/,/g, ""))}" > `;
                                                    $(dateDt).after(hiddenInput);
                                                    $(hiddenInput).trigger('change');
                                                });

                                                $(row).append(
                                                    `<input type="hidden" class="input-hidden-for-total" name="subTotals[${financialStatementId}][${financialStatementItemId}][${subItemName}]"  data-parent-model-id="${financialStatementItemId}" value="0" >`
                                                );


                                                $(cells).filter('.editable.editable-text').each(function(index, textDt) {



                                                    var hiddenInput = `<input type="hidden" class="text-input-hidden"  name="financialStatementAbleItemName[${financialStatementId}][${financialStatementItemId}][${subItemName}]" value="${$(textDt).html()}" > `;
                                                    $(textDt).after(hiddenInput);
                                                })

                                            } else {
                                                if (!data.has_sub_items) {

                                                    $(row).addClass('main-with-no-child').attr('data-model-id', data.id);

                                                    $(cells).filter('.editable.editable-date').each(function(index, dateDt) {

                                                        var filterDate = $(dateDt).attr("class").split(/\s+/).filter(function(classItem) {
                                                            return classItem.startsWith('date-');
                                                        })[0];
                                                        filterDate = filterDate.split('date-')[1];

                                                        var hiddenInput = `<input type="hidden" name="valueMainRowWithoutSubItems[${financialStatementId}][${financialStatementItemId}][${filterDate}]" data-date="${filterDate}" data-parent-model-id="${financialStatementItemId}" value="${($(dateDt).html().replace(/(<([^>]+)>)/gi, "").replace(/,/g, ""))}" > `;
                                                        $(dateDt).after(hiddenInput);
                                                    });

                                                    $(row).append(`
                        <input type="hidden" class="input-hidden-for-total" name="totals[${financialStatementId}][${financialStatementItemId}]" value="0">
                    `);


                                                    let dependOn = JSON.parse(data.depends_on);
                                                    if (dependOn.length) {
                                                        $(row).attr('data-depends-on', dependOn.join(','))
                                                    }
                                                    $(cells).each(function(index, cell) {
                                                        $(cell).removeClass('editable').removeClass('editable-text').attr('contenteditable', false)
                                                    });


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
                                                    $(row).addClass('is-main-with-sub-items');
                                                    if (data.is_main_for_all_calculations) {
                                                        $(row).addClass('is-main-for-all-calculations');
                                                    }
                                                    $(cells).filter('.editable.editable-date').each(function(index, dateDt) {
                                                        var filterDate = $(dateDt).attr("class").split(/\s+/).filter(function(classItem) {
                                                            return classItem.startsWith('date-');
                                                        })[0];


                                                        filterDate = filterDate.split('date-')[1];
                                                        totalOfRowArray.push(parseFloat($(dateDt).html().replace(/(<([^>]+)>)/gi, "").replace(/,/g, "")));

                                                        var hiddenInput = `<input type="hidden" class="main-row-that-has-sub-class" name="valueMainRowThatHasSubItems[${financialStatementId}][${financialStatementItemId}][${filterDate}]" data-date="${filterDate}" data-parent-model-id="${financialStatementItemId}" value="${($(dateDt).html().replace(/(<([^>]+)>)/gi, "").replace(/,/g, ""))}" > `;
                                                        $(dateDt).after(hiddenInput);
                                                    });
                                                    $(row).append(
                                                        `<input type="hidden" class="input-hidden-for-total" name="totals[${financialStatementId}][${financialStatementItemId}]"  data-parent-model-id="${financialStatementItemId}" value="0" >`
                                                    );

                                                    $(cells).each(function(index, cell) {
                                                        $(cell).removeClass('editable').removeClass('editable-text').attr('contenteditable', false)
                                                    });

                                                    if (data.has_depreciation_or_amortization) {
                                                        $(row).addClass('has-depreciation-or-amortization');
                                                        nameAndDepreciationIfExist = ` <div class="append-names mt-2" data-id="${data.id}">

                <div class="form-group how-many-item d-flex text-nowrap" data-id="${data.id}" data-index="0">
                   <div>
                        <label class="form-label">{{ __('Name') }}</label>
                        <input name="sub_items[0][name]" type="text" value="" class="form-control">
                    </div>
                    <div class="form-check mt-2">
  <label class="form-check-label"  style="margin-top:3px;display:block" >
    {{ __('Is Depreciation Or Amortization') }}
  </label>

  <input class="form-check-input" type="checkbox" value="1" name="sub_items[0][is_depreciation_or_amortization]"  style="width:16px;height:16px;margin-left:-0.05rem;left:50%;">
  
</div>
                </div>
            </div>`;
                                                    } else {
                                                        nameAndDepreciationIfExist = ` <div class="append-names mt-2" data-id="${data.id}">

                <div class="form-group how-many-item" data-id="${data.id}" data-index="0">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input name="sub_items[0][name]" type="text" value="" class="form-control">
                </div>
            </div>`;

                                                    }

                                                    $(row).addClass('edit-info-row').addClass('add-sub maintable-1-row-class' + (data.id)).attr('data-model-id', data.id).attr('data-model-name', '{{ $modelName }}')
                                                        .append(`
                    <div class="modal fade" id="add-sub-modal${data.id}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Sub Item For') }} ${data.name} </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="add-sub-item-form${data.id}" class="submit-sub-item" action="{{ route('admin.store.financial.statement.report',['company'=>getCurrentCompanyId()]) }}">
			<input type="hidden" name="sub_item_type" value="{{ getReportNameFromRouteName(Request()->route()->getName()) }}">
            <label class="label ">{{ __('How Many Items ?') }}</label>
            <input type="hidden" name="financial_statement_able_item_id"  value="${data.id}">
            <input  type="hidden" name="financial_statement_able_id"  value="{{ $financialStatement->id }}">

            <input data-id="${data.id}" class="form-control how-many-class only-greater-than-zero-allowed" type="number" value="1">
          
           ${nameAndDepreciationIfExist}
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close')  }}</button>
        <button type="button" class="btn btn-primary save-sub-item" data-id="${data.id}">{{ __('Save') }}</button>
      </div>
    </div>
  </div>
    </div>

                    `)

                                                }


                                            }




                                            ;
                                            $(cells).filter(".editable").attr('contenteditable', true);



                                            if (data.is_sales_rate || data.id == salesGrowthRateId) {
                                                $(row).find('td.total-row').html('-')
                                            } else {
                                                var totals = array_sum(totalOfRowArray);
                                                $(row).find('td.total-row').html(number_format(totals));
                                                $(row).find('.input-hidden-for-total').val(totals)
                                            }




                                        }
                                        , drawCallback: function(settings) {
                                            var dates = "{{ json_encode(array_keys($financialStatement->getIntervalFormatted())) }}";
                                            dates = dates.replace(/(&quot\;)/g, "\"")

                                            if ($('.is-main-for-all-calculations').length) {
                                                var mainRowWithSubItems = null;
                                                $('.is-main-for-all-calculations').each(function(index, mainRow) {
                                                    if (!mainRowWithSubItems) {
                                                        var modelId = $(mainRow).data('model-id');
                                                        if ($(mainRow).nextAll('.is-sub-row.maintable-1-row-class' + modelId).length) {
                                                            mainRowWithSubItems = mainRow
                                                        }

                                                    }

                                                });
                                                if (mainRowWithSubItems) {
                                                    dates = JSON.parse(dates);
                                                    for (date of dates) {
                                                        $(mainRowWithSubItems).next('tr').find('input[data-date="' + date + '"]').trigger('change');
                                                    }
                                                }
                                                let options = '';
                                                $('.is-main-with-sub-items').each(function(index, row) {
                                                    var name = $(row).find('.is-name-cell').html();
                                                    options += ` <option value="${$(row).data('model-id')}"> ${name} </option> `
                                                })

                                                $('.main-row-select').each(function(index, mainRowSelect) {
                                                    var selectedRowId = $(mainRowSelect).data('selected-main-row');
                                                    var replace = `value="${selectedRowId}"`;
                                                    var replaceWith = `selected value="${selectedRowId}"`
                                                    options = options.replace(replace, replaceWith);
                                                    $(mainRowSelect).empty().append(options);
                                                })
                                            }
                                            updateAllMainsRowPercentageOfSales(dates);

                                            // handle data for intervals 
                                        }
                                        , initComplete: function(settings, json) {

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

                            $(document).on('click', '.save-sub-item', function(e) {
                                e.preventDefault();
                                let id = $(this).data('id');

                                let form = document.getElementById('add-sub-item-form' + id);

                                var formData = new FormData(form);

                                $.ajax({
                                    type: 'POST'
                                    , url: $(form).attr('action')
                                    , data: formData
                                    , cache: false
                                    , contentType: false
                                    , processData: false
                                    , success: function(res) {

                                        $('.main-table-class').DataTable().ajax.reload(null, false)
                                        if (res.status) {

                                            Swal.fire({
                                                icon: 'success'
                                                , title: res.message,
                                                // text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        }
                                    }
                                    , error: function(data) {}
                                });
                            });


                            $(document).on('click', '.save-sub-item-edit', function(e) {
                                e.preventDefault();
                                const btn = $(this);
                                btn.prop('disabled', true);
                                let id = $(this).data('id');
                                let subItemName = $(this).data('sub-item-name');
                                let form = document.getElementById('edit-sub-item-form' + id + subItemName);

                                var formData = new FormData(form);

                                $.ajax({
                                    type: 'POST'
                                    , url: $(form).attr('action')
                                    , data: formData
                                    , cache: false
                                    , contentType: false
                                    , processData: false
                                    , success: function(res) {

                                        $('.main-table-class').DataTable().ajax.reload(null, false)
                                        if (res.status) {

                                            Swal.fire({
                                                icon: 'success'
                                                , title: res.message,
                                                // text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        }
                                    }
                                    , error: function(data) {
                                        // $(this).prop('disabled',false);

                                    }
                                });
                            });




                            $(document).on('click', '.save-sub-item-delete', function(e) {
                                e.preventDefault();
                                let id = $(this).data('id');
                                let subItemName = $(this).data('sub-item-name');

                                $(this).prop('disabled', true);

                                let form = document.getElementById('delete-sub-item-form' + id + subItemName);

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

                                        $('.main-table-class').DataTable().ajax.reload(null, false)
                                        if (res.status) {

                                            Swal.fire({
                                                icon: 'success'
                                                , title: res.message,
                                                // text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        }
                                    }
                                    , error: function(data) {
                                        $(this).prop('disabled', false);

                                    }
                                });
                            });


                            $(document).on('blur', '.editable', function() {
                                let firstDateString = $(this).attr("class").split(/\s+/).filter(function(classItem) {
                                    return classItem.startsWith('date-');
                                })[0];
                                if (firstDateString) {

                                    var firstDate = firstDateString.split('date-')[1]
                                    // 
                                    $(this).parent().find('input[data-date="' + firstDate + '"]').val($(this).text().replace(/(<([^>]+)>)/gi, "").replace(/,/g, "")).trigger('change');


                                } else {

                                    $(this).parent().find('input.text-input-hidden').val($(this).text().replace(/(<([^>]+)>)/gi, "").replace(/,/g, "")).trigger('change');
                                }
                                $('.main-table-class').DataTable().columns.adjust();

                            });
                            $(document).on('change', '.is-sub-row input', function(e) {
                                // let grossProfitId = $('#gross-profit-id').val();
                                // let costOfGoodsId = $('#cost-of-goods-id').val();
                                // let salesRevenueId = $('#sales-revenue-id').val();
                                // let financialIncomeOrExpenses = $('#financial-income-or-expenses-id').val();
                                // let corporateTaxesId = $('#corporate-taxes-id').val();

                                //     let marketExpensesId = $('#market-expenses-id').val();
                                //  let salesAndDistributionExpensesId = $('#sales-and-distribution-expenses-id').val();
                                //  let generalExpensesId = $('#general-expenses-id').val();

                                let parentModelId = $(this).data('parent-model-id');
                                let date = $(this).data('date');

                                if (date && parentModelId) {
                                    updateParentMainRowTotal(parentModelId, date);
                                }
                                // if(parentModelId == salesRevenueId || parentModelId == costOfGoodsId)
                                // {

                                //     updateGrowthRateForSalesRevenue(date);
                                //     updateGrossProfit(date);

                                // }
                                // if(parentModelId == marketExpensesId || parentModelId == salesAndDistributionExpensesId || parentModelId == generalExpensesId  ){
                                //        updateEarningBeforeIntersetTaxesDepreciationAmortization( date);
                                // }

                                // if(parentModelId == financialIncomeOrExpenses )
                                // {
                                //     updateEarningBeforeTaxes(date);
                                // }

                                //  if(parentModelId == corporateTaxesId )
                                // {
                                //     updateNetProfit(date);
                                // }

                                // updatePercentageOfSalesFor(parentModelId,date);
                                // updateAllMainsRowPercentageOfSales()


                            });

                            function updateGrowthRateForSalesRevenue(currentDate) {

                                let dates = getDates();
                                let previousDate = getPreviousDate(dates, currentDate);
                                if (previousDate) {
                                    let salesRevenueId = $('#sales-revenue-id').val();
                                    let salesGrowthRateId = $('#sales-growth-rate-id').val();
                                    let currentTotalSalesRevenueValue = parseFloat($('.is-main-with-sub-items[data-model-id="' + salesRevenueId + '"]').find('input[data-date="' + currentDate + '"]').val());
                                    let previousTotalSalesRevenueValue = parseFloat($('.is-main-with-sub-items[data-model-id="' + salesRevenueId + '"]').find('input[data-date="' + previousDate + '"]').val());
                                    let salesRevenueGrowthRate = currentTotalSalesRevenueValue ? ((currentTotalSalesRevenueValue - previousTotalSalesRevenueValue) / previousTotalSalesRevenueValue) * 100 : 0;
                                    $('.main-with-no-child[data-model-id="' + salesGrowthRateId + '"]').find('input[data-date="' + currentDate + '"]').val(salesRevenueGrowthRate).trigger('change');
                                    $('.main-with-no-child[data-model-id="' + salesGrowthRateId + '"]').find('td.date-' + currentDate).html(number_format(salesRevenueGrowthRate, 2) + ' %');
                                }

                                return number_format(0, 2) + ' %';


                            }

                            function getPreviousDate(dates, currentDate) {
                                let index = dates.indexOf(currentDate);
                                if (index == 0) {
                                    return null;
                                }
                                return dates[index - 1];

                            }

                            function getDates() {
                                var dates = "{{ json_encode(array_keys($financialStatement->getIntervalFormatted())) }}";
                                dates = dates.replace(/(&quot\;)/g, "\"");
                                return JSON.parse(dates);
                            }

                            $(document).on('change', '.main-with-no-child input', function(e) {
                                // let rowId = $(this).data('parent-model-id');
                                //  let grossProfitId = $('#gross-profit-id').val();
                                //  let earningBeforeInterestTaxesDepreciationAmortizationId = $('#earning-before-interest-taxes-depreciation-amortization-id').val();
                                //  let earningBeforeInterestTaxesId = $('#earning-before-interest-taxes-id').val();
                                //  let earningBeforeTaxesId = $('#earning-before-taxes-id').val();
                                //  let date = $(this).data('date');
                                //  if(rowId == grossProfitId ){
                                //      updateEarningBeforeIntersetTaxesDepreciationAmortization( date);
                                // }
                                // else if(rowId == earningBeforeInterestTaxesId){
                                //     updateEarningBeforeTaxes(date);
                                // }
                                //    else if(rowId == earningBeforeTaxesId){
                                //     updateNetProfit(date);
                                // }


                                // updatePercentageOfSalesFor(rowId,date);
                            });
                            // function getSalesRateMaps()
                            // {

                            // }

                            function updateNetProfit(date) {
                                let earningBeforeTaxesId = $('#earning-before-taxes-id').val();
                                let corporateTaxesId = $('#corporate-taxes-id').val();
                                let netProfitId = $('#net-profit-id').val();
                                let netProfitRow = $('.main-with-no-child[data-model-id="' + netProfitId + '"]');
                                let earningBeforeTaxesValueAtDate = $('.main-with-no-child[data-model-id="' + earningBeforeTaxesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                let corporateTaxesValueAtDate = $('.is-main-with-sub-items[data-model-id="' + corporateTaxesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                netprofitAtDate = earningBeforeTaxesValueAtDate - corporateTaxesValueAtDate;
                                netProfitRow.find('td.date-' + date).html(number_format(netprofitAtDate));
                                netProfitRow.find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val(netprofitAtDate).trigger('change');
                                updateTotalForRow(netProfitRow);
                            }

                            function updateEarningBeforeTaxes(date) {
                                let earningBeforeInterstTaxesId = $('#earning-before-interest-taxes-id').val();
                                let financialIncomeOrExpensesId = $('#financial-income-or-expenses-id').val();
                                let earningBeforeTaxesId = $('#earning-before-taxes-id').val();
                                let earningBeforeTaxesIdRow = $('.main-with-no-child[data-model-id="' + earningBeforeTaxesId + '"]');
                                let earningBeforeInterstTaxesValueAtDate = $('.main-with-no-child[data-model-id="' + earningBeforeInterstTaxesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                let financialIncomeOrExpensesValueAtDate = $('.is-main-with-sub-items[data-model-id="' + financialIncomeOrExpensesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                earningBeforeTaxesAtDate = parseFloat(earningBeforeInterstTaxesValueAtDate) + parseFloat(financialIncomeOrExpensesValueAtDate);
                                earningBeforeTaxesIdRow.find('td.date-' + date).html(number_format(earningBeforeTaxesAtDate));
                                earningBeforeTaxesIdRow.find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val(earningBeforeTaxesAtDate).trigger('change');
                                updateTotalForRow(earningBeforeTaxesIdRow);
                                updateNetProfit(date);
                            }

                            function updateEarningBeforeIntersetTaxesDepreciationAmortization(date) {
                                let grossProfitId = $('#gross-profit-id').val();
                                let marketExpensesId = $('#market-expenses-id').val();
                                let salesAndDistributionExpensesId = $('#sales-and-distribution-expenses-id').val();
                                let generalExpensesId = $('#general-expenses-id').val();
                                let costOfGoodsId = $('#cost-of-goods-id').val();
                                let earningBeforeInterstTaxesDepreciationAmortizationId = $('#earning-before-interest-taxes-depreciation-amortization-id').val();
                                let earningBeforeInterestTaxesDepreciationAmortizationRow = $('.main-with-no-child[data-model-id="' + earningBeforeInterstTaxesDepreciationAmortizationId + '"]');
                                let grossProfitAtDate = parseFloat($('.main-with-no-child[data-model-id="' + grossProfitId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                let marketExpensesAtDate = parseFloat($('.is-main-with-sub-items[data-model-id="' + marketExpensesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                let salesAndDistributionExpensesAtDate = parseFloat($('.is-main-with-sub-items[data-model-id="' + salesAndDistributionExpensesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                let generalExpensesAtDate = parseFloat($('.is-main-with-sub-items[data-model-id="' + generalExpensesId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());

                                let depreciationForCostOfGoodsSold = $('.is-main-with-sub-items[data-model-id="' + costOfGoodsId + '"]').nextAll('tr.is-depreciation-or-amortization.maintable-1-row-class' + costOfGoodsId);
                                let totalDepreciationForCostOfGoodsSoldAtDate = 0;
                                for (depreciationRow of depreciationForCostOfGoodsSold) {
                                    totalDepreciationForCostOfGoodsSoldAtDate += parseFloat($(depreciationRow).find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                }


                                let depreciationForMarketExpenses = $('.is-main-with-sub-items[data-model-id="' + marketExpensesId + '"]').nextAll('tr.is-depreciation-or-amortization.maintable-1-row-class' + marketExpensesId);
                                let totalDepreciationForMarketExpensesAtDate = 0;
                                for (depreciationRow of depreciationForMarketExpenses) {
                                    totalDepreciationForMarketExpensesAtDate += parseFloat($(depreciationRow).find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                }

                                let depreciationForSalesAndDistributionExpense = $('.is-main-with-sub-items[data-model-id="' + salesAndDistributionExpensesId + '"]').nextAll('tr.is-depreciation-or-amortization.maintable-1-row-class' + salesAndDistributionExpensesId);
                                let totalDepreciationForSalesAndDistributionExpenseAtDate = 0;
                                for (depreciationRow of depreciationForSalesAndDistributionExpense) {
                                    totalDepreciationForSalesAndDistributionExpenseAtDate += parseFloat($(depreciationRow).find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                }

                                let depreciationForGeneralExpenses = $('.is-main-with-sub-items[data-model-id="' + generalExpensesId + '"]').nextAll('tr.is-depreciation-or-amortization.maintable-1-row-class' + generalExpensesId);
                                let totalDepreciationForGeneralExpensesAtDate = 0;
                                for (depreciationRow of depreciationForGeneralExpenses) {
                                    totalDepreciationForGeneralExpensesAtDate += parseFloat($(depreciationRow).find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                }
                                let totalDepreciationsAtDate = totalDepreciationForGeneralExpensesAtDate + totalDepreciationForSalesAndDistributionExpenseAtDate + totalDepreciationForMarketExpensesAtDate + totalDepreciationForCostOfGoodsSoldAtDate
                                let earningBeforeInterestTaxesAtDate = grossProfitAtDate - marketExpensesAtDate - salesAndDistributionExpensesAtDate - generalExpensesAtDate;
                                let earningBeforeInterstTaxesDepreciationAmortizationAtDate = earningBeforeInterestTaxesAtDate + totalDepreciationsAtDate;
                                earningBeforeInterestTaxesDepreciationAmortizationRow.find('td.date-' + date).html(number_format(earningBeforeInterstTaxesDepreciationAmortizationAtDate));
                                earningBeforeInterestTaxesDepreciationAmortizationRow.find('input[data-date="' + date + '"]').val(earningBeforeInterstTaxesDepreciationAmortizationAtDate).trigger('change');
                                updateTotalForRow(earningBeforeInterestTaxesDepreciationAmortizationRow);

                                updateEarningBeforeInterestTaxesDepreciationAmortizationId(earningBeforeInterestTaxesAtDate, date)

                            }

                            function updateEarningBeforeInterestTaxesDepreciationAmortizationId(earningBeforeInterestTaxesWithoutDepreciationAtDate, date) {
                                let EarningBeforeInterestTaxesId = $('#earning-before-interest-taxes-id').val();
                                let earningBeforeInterestTaxesRow = $('.main-with-no-child[data-model-id="' + EarningBeforeInterestTaxesId + '"]');
                                earningBeforeInterestTaxesRow.find('td.date-' + date).html(number_format(earningBeforeInterestTaxesWithoutDepreciationAtDate));
                                earningBeforeInterestTaxesRow.find('input[data-date="' + date + '"]').val(earningBeforeInterestTaxesWithoutDepreciationAtDate).trigger('change');
                                updateTotalForRow(earningBeforeInterestTaxesRow);

                            }

                            function updateParentMainRowTotal(parentModelId, date) {
                                let parentElement = $('tr.is-main-with-sub-items[data-model-id="' + parentModelId + '"] ');
                                let total = 0;
                                parentElement.nextAll('.maintable-1-row-class' + parentModelId).each(function(index, subRow) {
                                    var subRowTdValue = parseFloat($(subRow).find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val());
                                    total += subRowTdValue;
                                })
                                parentElement.find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val(total);
                                parentElement.find('td.date-' + date).html(number_format(total));
                                updateTotalForRow(parentElement);

                            }

                            function updateGrossProfit(date) {
                                let grossProfitId = $('#gross-profit-id').val();
                                let costOfGoodsId = $('#cost-of-goods-id').val();
                                let salesReveueId = $('#sales-revenue-id').val();
                                let grossProfitRow = $('.main-with-no-child[data-model-id="' + grossProfitId + '"]');
                                let salesRevenueValueAtDate = $('.is-main-with-sub-items[data-model-id="' + salesReveueId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                let costOfGoodsValueAtDate = $('.is-main-with-sub-items[data-model-id="' + costOfGoodsId + '"]').find('td.date-' + date).parent().find('input[data-date="' + date + '"]').val();
                                grossProfitAtDate = salesRevenueValueAtDate - costOfGoodsValueAtDate;
                                grossProfitRow.find('td.date-' + date).html(number_format(grossProfitAtDate));
                                grossProfitRow.find('input[data-date="' + date + '"]').val(grossProfitAtDate).trigger('change');
                                updateTotalForRow(grossProfitRow);

                            }

                            function updateTotalForRow(row) {
                                var total = 0;
                                row.find('input[data-date]').each(function(index, input) {
                                    total += parseFloat($(input).val());
                                });
                                // alert
                                $(row).find('.input-hidden-for-total').val(total)
                                $(row).find('td.total-row').html(number_format(total));


                                // $('.is-main-with-sub-items[data-model-id="'+ salesReveueId +'"]').each(()=>{})
                            }

                            $(document).on('click', '.save-form', function(e) {
                                e.preventDefault();
                                form = document.getElementById('store-report-form-id');

                                var formData = new FormData(form);

                                $.ajax({
                                    type: 'POST'
                                    , url: $(form).attr('action')
                                    , data: formData
                                    , cache: false
                                    , contentType: false
                                    , processData: false
                                    , success: function(res) {

                                        $('.main-table-class').DataTable().ajax.reload(null, false)

                                        if (res.status) {
                                            Swal.fire({
                                                icon: 'success'
                                                , title: res.message
                                                , text: 'تمت العملية بنجاح',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            }).then(function() {

                                            })
                                        } else {
                                            Swal.fire({
                                                icon: 'error'
                                                , title: res.message
                                                , text: 'حدث خطا اثناء تنفيذ العملية',
                                                // footer: '<a href="">Why do I have this issue?</a>'
                                            })
                                        }
                                    }
                                    , error: function(data) {}
                                });

                            })


                            $(document).on('keyup', '.how-many-class', function() {
                                let index = $(this).data('id');
                                let oldHowMany = parseInt($('.old-how-many[data-id="' + index + '"]').val());
                                let currentHowMany = parseInt($('.how-many-class[data-id="' + index + '"]').val());
                                let currentHowManyInstances = $('.how-many-item[data-id="' + index + '"]').length;
                                if (currentHowMany < 1) {
                                    currentHowMany = 1;
                                }
                                if (currentHowManyInstances == currentHowMany) {
                                    return;
                                }
                                if (currentHowManyInstances >= currentHowMany) {
                                    $('.how-many-item[data-id="' + index + '"]').each(function(index, val) {
                                        var order = index + 1;

                                        if (order > currentHowMany) {

                                            $(val).remove();
                                        }
                                    })
                                } else {
                                    let numberOfNewInstances = currentHowMany - currentHowManyInstances;
                                    for (i = 0; i < numberOfNewInstances; i++) {
                                        var lastInstanceClone = $('.how-many-item[data-id="' + index + '"]:last-of-type').clone(true);
                                        var lastItemIndex = parseInt($('.how-many-item[data-id="' + index + '"]:last-of-type').data('index'));
                                        $(lastInstanceClone).attr('data-index', lastItemIndex + 1)

                                        lastInstanceClone.find('input').each(function(i, v) {
                                            if ($(v).attr('type') == 'text') {
                                                $(v).val('');
                                            }
                                            $(v).attr('name', $(v).attr('name').replace(lastItemIndex, lastItemIndex + 1));
                                        })
                                        $('.append-names[data-id="' + index + '"]').append(lastInstanceClone);

                                    }

                                }

                            });

                        });
                    })(jQuery);
                });

                function getSearchInputSelector(tableId) {
                    return tableId + '_filter' + ' label input';
                }

                function updateAllMainsRowPercentageOfSales(dates = null) {
                    if (!dates) {
                        dates = "{{ json_encode(array_keys($financialStatement->getIntervalFormatted())) }}";
                        dates = dates.replace(/(&quot\;)/g, "\"")

                        dates = JSON.parse(dates);
                    }


                    // $('.is-main-with-sub-items').each(function(index,val){
                    //                         var mainRowId = $(val).data('model-id');
                    //                         dates = Array.isArray(dates) ? dates : JSON.parse(dates);
                    //                         for(date of dates){
                    //                             updatePercentageOfSalesFor(mainRowId,date,false);
                    //                         }
                    //                     })

                }

                function updatePercentageOfSalesFor(rowId, date, mainRowIsSub = true) {

                    let salesRevenueId = $('#sales-revenue-id').val();
                    let rateMainRowId = sales_rate_maps[rowId];
                    let mainRowValue = 0;
                    let salesRevenueValue = 0;
                    mainRow = '';
                    if (mainRowIsSub) {
                        mainRow = $('.main-with-no-child[data-model-id="' + rowId + '"]');
                        mainRowValue = parseFloat(mainRow.find('input[data-date="' + date + '"]').val());
                        salesRevenueValue = parseFloat($('.is-main-with-sub-items[data-model-id="' + salesRevenueId + '"]').find('input[data-date="' + date + '"]').val());

                    } else {
                        mainRow = $('.is-main-with-sub-items[data-model-id="' + rowId + '"]')
                        mainRowValue = parseFloat(mainRow.find('input[data-date="' + date + '"]').val());
                        salesRevenueValue = parseFloat($('.is-main-with-sub-items[data-model-id="' + salesRevenueId + '"]').find('input[data-date="' + date + '"]').val());

                    }
                    let salesPercentage = salesRevenueValue ? mainRowValue / salesRevenueValue * 100 : 0;
                    $('.main-with-no-child.is-sales-rate[data-model-id="' + rateMainRowId + '"]').find('input[data-date="' + date + '"]').val(salesPercentage);
                    $('.main-with-no-child.is-sales-rate[data-model-id="' + rateMainRowId + '"]').find('td.date-' + date).html(number_format(salesPercentage, 2) + ' %');

                    totalPercentage = mainRow.find('.total-row').html();

                    if (totalPercentage) {
                        totalPercentage = parseFloat(number_unformat(totalPercentage));
                        totalSalesRevenue = parseFloat(number_unformat($('.is-main-with-sub-items[data-model-id="' + salesRevenueId + '"]').find('.total-row').html()));
                        if (totalPercentage && totalSalesRevenue) {
                            $('.main-with-no-child[data-model-id="' + rateMainRowId + '"]').find('.input-hidden-for-total').val(totalPercentage / totalSalesRevenue * 100)
                            $('.main-with-no-child[data-model-id="' + rateMainRowId + '"]').find('.total-row').html(number_format(totalPercentage / totalSalesRevenue * 100, 2) + ' %');

                        }

                    }


                }

                function formatDatesForInterval(intervalName) {
                    const table = $('.main-table-class').DataTable();

                    const noCols = $('#cols-counter').data('value');
                    table.columns([...Array(noCols).keys()], false).visible(true);



                    const firstDateColumn = $('td.editable-date').eq(0);
                    salesGrowthRateId = $('#sales-growth-rate-id').val();
                    salesRevenueId = $('#sales-revenue-id').val();
                    var visiableHeaderDates = [];

                    const allYears = getYearsFromDates(dates);



                    const firstDateColumnIndex = $(firstDateColumn).index();

                    let firstDateString = $(firstDateColumn).attr("class").split(/\s+/).filter(function(classItem) {
                        return classItem.startsWith('date-');
                    })[0];
                    var firstDate = firstDateString.split('date-')[1];
                    var year = firstDate.split('-')[0];
                    var month = firstDate.split('-')[1];
                    var day = firstDate.split('-')[2];

                    let hideColumnsFromMonthMapping = {
                        monthly: {
                            12: []
                            , 11: []
                            , 10: []
                            , "09": []
                            , "08": []
                            , "07": []
                            , "06": []
                            , "05": []
                            , "04": []
                            , "03": []
                            , "02": []
                            , "01": []
                        }
                        , quarterly: {
                            12: ["11", "10"]
                            , 11: ["10"]
                            , 10: []
                            , "09": ["08", "07"]
                            , "08": ["07"]
                            , "07": []
                            , "06": ["05", "04"]
                            , "05": ["04"]
                            , "04": []
                            , "03": ["02", "01"]
                            , "02": ["01"]
                            , "01": []
                        }
                        , "semi-annually": {
                            12: ["11", "10", "09", "08", "07"]
                            , 11: ["10", "09", "08", "07"]
                            , 10: ["09", "08", "07"]
                            , "09": ["08", "07"]
                            , "08": ["07"]
                            , "07": []
                            , "06": ["05", "04", "03", "02", "01"]
                            , "05": ["04", "03", "02", "01"]
                            , "04": ["03", "02", "01"]
                            , "03": ["02", "01"]
                            , "02": ["01"]
                            , "01": []
                        , }
                        , "annually": {
                            12: ["11", "10", "09", "08", "07", "06", "05", "04", "03", "02", "01"]
                            , 11: ["10", "09", "08", "07", "06", "05", "04", "03", "02", "01"]
                            , 10: ["09", "08", "07", "06", "05", "04", "03", "02", "01"]
                            , "09": ["08", "07", "06", "05", "04", "03", "02", "01"]
                            , "08": ["07", "06", "05", "04", "03", "02", "01"]
                            , "07": ["06", "05", "04", "03", "02", "01"]
                            , "06": ["05", "04", "03", "02", "01"]
                            , "05": ["04", "03", "02", "01"]
                            , "04": ["03", "02", "01"]
                            , "03": ["02", "01"]
                            , "02": ["01"]
                            , "01": []
                        , }
                    };

                    if ($('#financial-statement-duration-type').val() != intervalName) {
                        $('.add-btn , .edit-btn , .delete-btn').removeClass('d-block').addClass('d-none')
                    } else {

                        if (intervalName == 'monthly') {
                            $('input[type="hidden"][data-date]').each(function(index, inputHidden) {

                                let date = $(inputHidden).data('date');
                                var parentRow = $(this).parent();
                                if (parentRow.hasClass('is-sales-rate') || parentRow.data('model-id') == salesGrowthRateId) {
                                    parentRow.find('td.date-' + date).html(number_format($(inputHidden).val(), 2) + ' %');
                                } else {
                                    parentRow.find('td.date-' + date).html(number_format($(inputHidden).val()));

                                }
                            })

                            return;
                        }

                        $('.add-btn , .edit-btn , .delete-btn').addClass('d-block').removeClass('d-none')
                    }



                    let totalOfVisisableDates = [];
                    let hiddenMonths = [];

                    let hiddenColumnsAtInterval = hideColumnsFromMonthMapping[intervalName];
                    let monthsKeys = orderObjectKeys(hiddenColumnsAtInterval);

                    additionalColumnsToHide = [];


                    for (loopMonth of monthsKeys) {




                        let loopMonths = hideColumnsFromMonthMapping[intervalName][loopMonth];

                        var currentYear = date.split('-')[0];
                        var currentMonth = date.split('-')[1];
                        var currentDay = date.split('-')[2];
                        allYears.sort().reverse();


                        // hide columns 
                        for (loopYear of allYears) {

                            for (removeMonth of loopMonths) {
                                if (!hiddenMonths.includes(loopYear + '-' + removeMonth)) {
                                    currentColumn = $('th.date-' + loopYear + '-' + removeMonth + '-' + currentDay);

                                    if ($('td.date-' + loopYear + '-' + loopMonth + '-' + currentDay).length) {
                                        if (!visiableHeaderDates.includes(loopYear + '-' + loopMonth + '-' + currentDay)) {
                                            visiableHeaderDates.push(loopYear + '-' + loopMonth + '-' + currentDay);
                                        }
                                        for (rowId = 1; rowId <= $('tbody tr').length; rowId++) {
                                            currentRow = $('tbody tr:nth-of-type(' + rowId + ')');
                                            // rowId = currentRow.find('.is-name-cell').html();
                                            var searchRowValue = null;
                                            if (totalOfVisisableDates[rowId] && totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay] && totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay]['value']) {
                                                searchRowValue = totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay]['value'];
                                            }



                                            if (searchRowValue != null) {
                                                var val = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + removeMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + removeMonth + '-' + currentDay + '"]').val());
                                                val = val ? val : 0;
                                                totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay]['value'] += val;


                                            } else {
                                                var val = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + removeMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + removeMonth + '-' + currentDay + '"]').val());
                                                val = val ? val : 0;


                                                if (totalOfVisisableDates[rowId] && totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay] && totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay]) {

                                                    totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay] = {
                                                        value: val
                                                    }

                                                } else {
                                                    var val = 0;

                                                    if (!totalOfVisisableDates[rowId]) {


                                                        var val1 = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + loopMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + loopMonth + '-' + currentDay + '"]').val());
                                                        val1 = val1 ? val1 : 0;

                                                        var val2 = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + removeMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + removeMonth + '-' + currentDay + '"]').val());
                                                        val2 = val2 ? val2 : 0;


                                                        val = val1 + val2;

                                                        totalOfVisisableDates[rowId] = {
                                                            [loopYear + '-' + loopMonth + '-' + currentDay]: {
                                                                value: val
                                                            }
                                                        }

                                                    } else {
                                                        var val1 = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + loopMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + loopMonth + '-' + currentDay + '"]').val());
                                                        val1 = val1 ? val1 : 0;
                                                        var val2 = parseFloat($('tbody tr:nth-of-type(' + rowId + ') td.editable-date.date-' + loopYear + '-' + removeMonth + '-' + currentDay).parent().find('input[data-date="' + loopYear + '-' + removeMonth + '-' + currentDay + '"]').val());
                                                        val2 = val2 ? val2 : 0;
                                                        val = val1 + val2;

                                                        totalOfVisisableDates[rowId][
                                                            [loopYear + '-' + loopMonth + '-' + currentDay]
                                                        ] = {
                                                            value: val

                                                        }
                                                    }




                                                }

                                            }

                                            if (currentRow.hasClass('is-sales-rate')) {

                                                // do nothing
                                            } else {
                                                $('tbody tr:nth-of-type(' + rowId + ')').find('td.editable-date.date-' + loopYear + '-' + loopMonth + '-' + currentDay).html(number_format(totalOfVisisableDates[rowId][loopYear + '-' + loopMonth + '-' + currentDay]['value']));
                                            }
                                        }
                                        hiddenMonths.push(loopYear + '-' + removeMonth);




                                    }




                                }

                            }

                        }

                    }
                    var hiddenIndexes = [];
                    for (hiddenMonth of hiddenMonths) {
                        $('th[data-month-year="' + hiddenMonth + '"]').each(function(i, m) {
                            hiddenIndexes.push($(m).index());
                        })

                    }
                    table.columns(hiddenIndexes).visible(false)
                    updateSalesGrowthRate(visiableHeaderDates.sort());
                    updatePercentageRows(visiableHeaderDates);


                };

            </script>

            <script>
                function updatePercentageRows(visiableHeaderDates) {
                    var percentage = 0;
                    const salesRevenueId = $('#sales-revenue-id').val();
                    $('tr.is-sales-rate').each(function(index, isSalesRow) {

                        for (visiableHeaderDate of visiableHeaderDates) {



                            var currentRowId = $(isSalesRow).data('model-id');
                            var parentId = getKeyByValue(sales_rate_maps, currentRowId);

                            let parentRowValAtDate = parseFloat(number_unformat($('tbody tr[data-model-id="' + parentId + '"]').find('td.date-' + visiableHeaderDate).html()));
                            let salesRevenueAtDate = parseFloat(number_unformat($('tbody tr[data-model-id="' + salesRevenueId + '"]').find('td.date-' + visiableHeaderDate).html()));
                            if (salesRevenueAtDate) {
                                percentage = parentRowValAtDate / salesRevenueAtDate * 100
                            }

                            var number_formatted = number_format(percentage, 2) + ' %';
                            $('tbody tr[data-model-id="' + currentRowId + '"]').find('td.editable-date.date-' + visiableHeaderDate).html(number_formatted);

                        }



                    })




                }

                function updateSalesGrowthRate(visiableHeaderDates) {
                    const salesRevenueId = $('#sales-revenue-id').val();
                    const salesGrowthRateId = $('#sales-growth-rate-id').val();
                    for (visiableHeaderDate of visiableHeaderDates) {
                        previousDate = getPreviousElementInArray(visiableHeaderDates, visiableHeaderDate);
                        if (previousDate) {

                            var currentSalesRevenueValue = number_unformat($('tbody tr[data-model-id="' + salesRevenueId + '"] td.editable-date.date-' + visiableHeaderDate).html());
                            var previousSalesRevenueValue = number_unformat($('tbody tr[data-model-id="' + salesRevenueId + '"] td.editable-date.date-' + previousDate).html());
                            if (previousSalesRevenueValue) {
                                $('tbody tr[data-model-id="' + salesGrowthRateId + '"] td.editable-date.date-' + visiableHeaderDate).html(number_format((currentSalesRevenueValue - previousSalesRevenueValue) / previousSalesRevenueValue * 100, 2) + ' %');
                            } else {
                                $('tbody tr[data-model-id="' + salesGrowthRateId + '"] td.editable-date.date-' + visiableHeaderDate).html(number_format(0, 2) + ' %');

                            }


                        } else {
                            $('tbody tr[data-model-id="' + salesGrowthRateId + '"] td.editable-date.date-' + visiableHeaderDate).html(number_format(0, 2) + ' %');
                        }
                    }


                }

                function getYearsFromDates(dates) {
                    years = [];
                    for (date of dates) {
                        years.push(date.split('-')[0]);
                    }
                    return uniqueArray(years);
                }

                function uniqueArray(a) {
                    return a.filter(function(item, pos) {
                        return a.indexOf(item) == pos;
                    });

                }

            </script>

        </x-slot>

    </x-tables.basic-view>

</div>
{{-- <button>Save</button> --}}
{{-- </form> --}}
