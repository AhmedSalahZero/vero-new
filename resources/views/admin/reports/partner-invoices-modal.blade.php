{{-- مودال فواتير الشريك (نفس شكل مودال فواتير العقد اللي في صفحة العقود) --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-90 modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="font-weight-bold form-label kt-subheader__title small-caps mr-5 text-primary text-nowrap"> {{ __('Invoices') }} [{{ $partnerName }}] [{{ $rowCurrency }}] </h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table kt_table_with_no_pagination_no_collapse table-striped- table-bordered table-hover table-checkable position-relative table-with-two-subrows main-table-class dataTable no-footer">
                        <thead>
                            <tr class="header-tr">
                                @include('admin.reports.invoice-report-th',['excludeMaxWith'=>true,'showInvoiceCurrency'=>true])
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailItems as $invoice)
                            @php
                            $currency = $invoice->getCurrency();
                            @endphp
                            <tr>
                                @include('admin.reports.invoice-report-td',['excludeMaxWith'=>true,'showInvoiceCurrency'=>true])
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
