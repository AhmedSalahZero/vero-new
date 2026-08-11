{{-- عقود الموردين المربوطة بعقد العميل (parent_id) --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-90 modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Related Supplier Contracts') }} [{{ $parent['name'] }}] {{ $parent['amount'] .' '. $parent['currency'] }} </h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table kt_table_with_no_pagination_no_collapse table-striped- table-bordered table-hover table-checkable position-relative main-table-class dataTable no-footer">
                        <thead>
                            <tr class="header-tr">
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Supplier Name') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Contract Name') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Contract Code') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Purchase Order Number') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Start Date') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('End Date') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Currency') }}</th>
                                <th class="view-table-th bg-lighter header-th align-middle text-center">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            /**
                             * الإجمالي بيتحسب لكل عملة لوحدها — عقود الموردين
                             * على المشروع الواحد ممكن تكون بعملات مختلفة
                             */
                            $totalsPerCurrency = [];
                            @endphp
                            @foreach($detailItems as $relatedContract)
                            @php
                            $relatedCurrency = $relatedContract->getCurrency();
                            $totalsPerCurrency[$relatedCurrency] = ($totalsPerCurrency[$relatedCurrency] ?? 0) + $relatedContract->getAmount();
                            @endphp
                            <tr>
                                <td class="sub-text-bg text-center">{{ $relatedContract->getClientName() }}</td>
                                <td class="sub-text-bg text-center">{{ $relatedContract->getName() }}</td>
                                <td class="sub-text-bg text-center text-nowrap">{{ $relatedContract->getCode() }}</td>
                                <td class="sub-text-bg text-center text-nowrap">{{ $relatedContract->purchasesOrders->map(fn($purchaseOrder) => $purchaseOrder->getNumber())->filter()->implode(' , ') }}</td>
                                <td class="sub-text-bg text-center text-nowrap">{{ $relatedContract->getStartDateFormatted() }}</td>
                                <td class="sub-text-bg text-center text-nowrap">{{ $relatedContract->getEndDateFormatted() }}</td>
                                <td class="sub-text-bg text-center">{{ $relatedCurrency }}</td>
                                <td class="sub-text-bg text-center text-nowrap">{{ $relatedContract->getAmountFormatted() }}</td>
                            </tr>
                            @endforeach

                            @foreach($totalsPerCurrency as $currencyName => $currencyTotal)
                            <tr class="is-total-row">
                                <td class="sub-text-bg text-center"><b>{{ __('Total') }}</b></td>
                                <td class="sub-text-bg text-center"></td>
                                <td class="sub-text-bg text-center"></td>
                                <td class="sub-text-bg text-center"></td>
                                <td class="sub-text-bg text-center"></td>
                                <td class="sub-text-bg text-center"></td>
                                <td class="sub-text-bg text-center"><b>{{ $currencyName }}</b></td>
                                <td class="sub-text-bg text-center text-nowrap"><b>{{ number_format($currencyTotal) }}</b></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
