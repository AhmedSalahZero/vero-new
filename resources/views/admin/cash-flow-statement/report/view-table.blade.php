@php
$tableId = 'kt_table_1';
@endphp


<style>
.btn-active{
	background-color:#0741A5 !important ;
	color:white !important;
}
.btn-active i {
	color:white !important;
}
    td.editable-date,
    th.editable-date,
    input.editable-date {
        width: 100px !important;
        min-width: 100px !important;
        max-width: 100px !important;
        overflow: hidden;
    }

    .width-66 {


        width: 66% !important;
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


    .flex-checkboxes>div {
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

    .main-with-no-child,
    .main-with-no-child td,
    .main-with-no-child th {
        background-color: #046187 !important;
        color: white !important;
        font-weight: bold;
    }

    .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg {
        border: 1.5px solid white !important;
        background-color: #0e96cd !important;
        color: white !important;


        background-color: #E2EFFE !important;
        color: black !important
    }

    .is-sub-row td.sub-text-bg {

        background-color: #aedbed !important;
        color: black !important;

    }

    .sub-numeric-bg {
        text-align: center;

    }

    .is-sub-row td.sub-numeric-bg,
    .is-sub-row td.sub-text-bg,
    tr[data-financial-statement-able-item-id="{{ \App\Models\CashFlowStatementItem::NET_CASH_PROFIT_ID }}"] td.sub-text-bg,
    tr[data-financial-statement-able-item-id="{{ \App\Models\CashFlowStatementItem::NET_CASH_PROFIT_ID }}"] td.sub-numeric-bg {
        background-color: #046187 !important;
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

<input type="hidden" id="editable-by-btn" value="1">
@if(in_array($reportType,['modified','adjusted']))
<input type="hidden" id="fixed-column-number" value="2">
@else
<input type="hidden" id="fixed-column-number" value="2">
@endif

<script>

</script>

<input type="hidden" id="monthly_data_monthly_net_cash" data-total="{{ json_encode($formattedDataForMonthlyNetCashChart ?? []) }}">
<input type="hidden" id="sub-item-type" value="{{ $reportType }}">
<div class="table-custom-container position-relative  ">



    <div class="responsive">
        <table class="table kt_table_with_no_pagination_no_collapse table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
            <thead>
                <tr class="header-tr " data-model-name="{{ $modelName }}">
                    <th class="view-table-th header-th trigger-child-row-1">
                        {{ __('Expand') }}
                    </th>


                    <th class="view-table-th header-th max-w-classes" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="BussinessLineName" data-is-relation="1" class="header-th" data-is-json="0">
                        {{ __('Name') }}
         
                    </th>
                    <input type="hidden" name="dates" value="{{ json_encode(array_keys($cashFlowStatement->getIntervalFormatted())) }}" id="dates">
                     @foreach($cashFlowStatement->getIntervalFormatted() as $dateAsIndex=>$dateAsString)
                    <th  class="view-table-th editable-date header-th" data-is-collection-relation="0" data-collection-item-id="0" data-db-column-name="name" data-relation-name="ServiceCategory" data-is-relation="1" class="header-th" data-is-json="0">
                         {{ formatDateForView($dateAsString) }}
                    </th>
                    @endforeach
                    <th class="view-table-th header-th">
                        {{ __('Total') }}
                    </th>

                </tr>
            </thead>
            <tbody>
                @php
                $rowIndex = 0 ;
                @endphp
			
                @foreach($cashes as $cashName => $elements)
                @php

                $isAccumulatedRow = $rowIndex ==3;
                $isMonthlyRow = $rowIndex ==2;
                $hasSubRows = $rowIndex < 2 ; @endphp <tr @if($isMonthlyRow) data-financial-statement-able-item-id="78" @endif class="@if($isAccumulatedRow) main-with-no-child even @endif   reset-table-width text-nowrap  cursor-pointer sub-text-bg text-capitalize is-close  " data-model-id="{{ $rowIndex }}">
                    <td class="red reset-table-width text-nowrap trigger-child-row-1 cursor-pointer sub-text-bg text-capitalize  is-close"> @if($hasSubRows) + @endif</td>
                    <td class="sub-text-bg  max-w-classes editable-text is-name-cell ">{{ $cashName }}</td>
                    @foreach($cashFlowStatement->getIntervalFormatted() as $dateIndex=>$dateAsString)
                    <td class="  sub-numeric-bg text-center editable-date" @if($isAccumulatedRow) style="color:white !important" @endif>{{ number_format($elements['total'][$dateAsString] ??0,0) }}</td>
                    @endforeach
                    <td @if($isMonthlyRow) class="sub-numeric-bg text-center" @endif @if($isAccumulatedRow) style="color:white !important" @endif>{{ $isAccumulatedRow ? '-' : number_format(array_sum($elements['total'] ?? []),0) }}</td>
                    </tr>
                    @php
                    $currentTotalAtDate = 0 ;
                    @endphp
                    @if($hasSubRows)

                    @foreach($elements as $subName => $dateAndValues)
                    @php
                    $currentTotalAtDate = 0;
                    @endphp
                    @if($subName !='total')
                    <tr class="edit-info-row add-sub maintable-1-row-class{{ $rowIndex }} is-sub-row d-none">
                        <td class=" reset-table-width text-nowrap trigger-child-row-1 cursor-pointer sub-text-bg text-capitalize is-close "></td>

                        <td class="sub-text-bg max-w-classes editable editable-text is-name-cell ">{{ $subName }}</td>
                        @foreach($cashFlowStatement->getIntervalFormatted() as $dateAsIndex=>$dateAsString)
                        @php
					
                        $totalAtDate = $dateAndValues[$dateAsString] ?? 0 ;
                        $currentTotalAtDate += $totalAtDate ;
                        @endphp
                        <td class="sub-numeric-bg editable-date">{{ number_format($totalAtDate,0) }}</td>
                        @endforeach
                        <td @if($isAccumulatedRow) style="color:white !important" @endif class="  sub-numeric-bg text-center total-row">{{ number_format($currentTotalAtDate,0) }}</td>
                    </tr>
                    @endif

                    @endforeach
                    @endif

                    @php
                    $rowIndex = $rowIndex+ 1;
                    @endphp

                    @endforeach
            </tbody>
        </table>
    </div>

</div>

@push('js')
<script >
    $(document).on('click', '.trigger-child-row-1', function(e) {
        const parentId = $(e.target.closest('tr')).data('model-id');
        var parentRow = $(e.target).parent();
        var subRows = parentRow.nextAll('tr.add-sub.maintable-1-row-class' + parentId);

        subRows.toggleClass('d-none');
        if (subRows.hasClass('d-none')) {
            parentRow.find('td.trigger-child-row-1').removeClass('is-open').addClass('is-close').html('+');
            var closedId = parentRow.attr('data-financial-statement-able-item-id')


        } else if (!subRows.length) {
            // if parent row has no sub rows then remove + or - 
            parentRow.find('td.trigger-child-row-1').html('×');
        } else {
            parentRow.find('td.trigger-child-row-1').addClass('is-open').removeClass('is-close').html('-');



        }

    });



    $(document).on('click', '.expand-all', function(e) {
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





    var table = $(".kt_table_with_no_pagination_no_collapse");






    table.DataTable({
            dom: 'Bfrtip'
            , "processing": false
            , "scrollX": true
            , "scrollY": true
            , "ordering": false
            , 'paging': false
            , "fixedColumns": {
                left: 2
            }
            , "fixedHeader": {
                headerOffset: 60
            }
            , "serverSide": false
            , "responsive": false
            , "pageLength": 25,
            buttons: [
                 {
                    "attr": {}
                    , "text": '<i class="fa fa-pen-alt  " style="color:#366cf3;"></i>' + '{{ __("Cash Opening Balances") }}'
                    , 'className': 'btn btn-bold btn-active filter-table-btn ml-2  flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away'
                    , "action": function() {
                        window.location.href = "{{ route('admin.show-cash-and-banks',[$company->id,$cashFlowStatement->id,$reportType]) }}"
                    }
                },

                {
                    "attr": {}
                    , "text": '<svg style="margin-right:10px;position:relative;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M5,4 L19,4 C19.2761424,4 19.5,4.22385763 19.5,4.5 C19.5,4.60818511 19.4649111,4.71345191 19.4,4.8 L14,12 L14,20.190983 C14,20.4671254 13.7761424,20.690983 13.5,20.690983 C13.4223775,20.690983 13.3458209,20.6729105 13.2763932,20.6381966 L10,19 L10,12 L4.6,4.8 C4.43431458,4.5790861 4.4790861,4.26568542 4.7,4.1 C4.78654809,4.03508894 4.89181489,4 5,4 Z" id="Path-33" fill="#000000"/></g></svg>' + '{{ __("Interval View") }}'
                    , 'className': 'btn btn-bold btn-secondary filter-table-btn ml-2 flex-1 flex-grow-0 btn-border-radius do-not-close-when-click-away'
                    , "action": function() {}
                }
                , {
                    "text": '<svg style="margin-right:10px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1" class="kt-svg-icon"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect id="bound" x="0" y="0" width="24" height="24"/><path d="M17,8 C16.4477153,8 16,7.55228475 16,7 C16,6.44771525 16.4477153,6 17,6 L18,6 C20.209139,6 22,7.790861 22,10 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,9.99305689 C2,7.7839179 3.790861,5.99305689 6,5.99305689 L7.00000482,5.99305689 C7.55228957,5.99305689 8.00000482,6.44077214 8.00000482,6.99305689 C8.00000482,7.54534164 7.55228957,7.99305689 7.00000482,7.99305689 L6,7.99305689 C4.8954305,7.99305689 4,8.88848739 4,9.99305689 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,10 C20,8.8954305 19.1045695,8 18,8 L17,8 Z" id="Path-103" fill="#000000" fill-rule="nonzero" opacity="0.3"/><rect id="Rectangle" fill="#000000" opacity="0.3" transform="translate(12.000000, 8.000000) scale(1, -1) rotate(-180.000000) translate(-12.000000, -8.000000) " x="11" y="2" width="2" height="12" rx="1"/><path d="M12,2.58578644 L14.2928932,0.292893219 C14.6834175,-0.0976310729 15.3165825,-0.0976310729 15.7071068,0.292893219 C16.0976311,0.683417511 16.0976311,1.31658249 15.7071068,1.70710678 L12.7071068,4.70710678 C12.3165825,5.09763107 11.6834175,5.09763107 11.2928932,4.70710678 L8.29289322,1.70710678 C7.90236893,1.31658249 7.90236893,0.683417511 8.29289322,0.292893219 C8.68341751,-0.0976310729 9.31658249,-0.0976310729 9.70710678,0.292893219 L12,2.58578644 Z" id="Path-104" fill="#000000" fill-rule="nonzero" transform="translate(12.000000, 2.500000) scale(1, -1) translate(-12.000000, -2.500000) "/></g></svg>' + '{{ __("Export") }}'
                    , 'className': 'btn btn-bold btn-secondary  flex-1 flex-grow-0 btn-border-radius ml-2 do-not-close-when-click-away'
                    , "action": function() {
                        let form = $('form#store-report-form-id');
                        let oldFormAction = form.attr('action');
                        let exportFormAction = "{{ route('admin.export.cash.flow.statement.report',$company->id) }}";
                        form.attr('action', exportFormAction);
                        form.submit();
                        form.attr('action', oldFormAction);
                    }
                },

            ]





        }

  							  )

</script>
@endpush
