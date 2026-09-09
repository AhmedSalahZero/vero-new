{{--
 * * البوب اب نفسه — واحد بس في الصفحة كلها ، و الجافاسكريبت بيملاه من
 * * الـ endpoint لما المستخدم يدوس على أي زرار info
 *
 * * عملناه واحد مشترك مش واحد لكل صف عشان الصفحة ما تتقلش بعشرات المودالز
--}}
<div class="modal fade" id="settlements-info-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Settlement Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="settlements-info-loading" class="text-center py-4">{{ __('Loading') }}...</div>

                <div id="settlements-info-content" style="display:none">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice Number') }}</th>
                                    <th>{{ __('Invoice Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th class="text-right">{{ __('Invoice Amount') }}</th>
                                    <th class="text-right">{{ __('Settlement Amount') }}</th>
                                    <th class="text-right">{{ __('Withhold Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody id="settlements-info-body"></tbody>
                            <tfoot id="settlements-info-foot"></tfoot>
                        </table>
                    </div>
                </div>

                {{--
                 * * تفاصيل الدفعة المقدمة — بتظهر برضه لما تكون الحركة دفعة
                 * * مقدمة صافية (من غير أي فواتير مسوّاة) ، و ساعتها بتبقى هي
                 * * المحتوى الوحيد في البوب اب
                --}}
                <div id="settlements-info-down-payment-details" class="alert alert-info mb-0" style="display:none"></div>

                <div id="settlements-info-empty" class="alert alert-warning mb-0" style="display:none">
                    {{ __('No Settled Invoices') }}
                </div>
                <div id="settlements-info-error" class="alert alert-danger mb-0" style="display:none">
                    {{ __('Something Went Wrong') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('js_last')
<script>
    (function () {
        var $modal = $('#settlements-info-modal');

        function reset() {
            $('#settlements-info-loading').show();
            $('#settlements-info-content, #settlements-info-empty, #settlements-info-error').hide();
            $('#settlements-info-body, #settlements-info-foot').empty();
            $('#settlements-info-down-payment-details').hide().empty();
        }

        function money(value, currency) {
            return value + ' ' + (currency || '');
        }

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        /**
         * * بيوصف الدفعة المقدمة نفسها : نوعها (عام / على عقد) و العقد لو
         * * موجود — بيتنادى في الحالتين ، الدفعة الصافية و التسوية مع دفعة
         */
        function renderDownPayment(info, currency) {
            if (!info) {
                return false;
            }

            var parts = [
                '<div><strong>{{ __('Down Payment') }}</strong></div>',
                '<div>{{ __('Down Payment Type') }}: ' + escapeHtml(info.type_label) + '</div>'
            ];

            if (info.contract_name) {
                parts.push('<div>{{ __('Contract Name') }}: ' + escapeHtml(info.contract_name) + '</div>');
            }
            if (info.contract_code) {
                parts.push('<div>{{ __('Contract Code') }}: ' + escapeHtml(info.contract_code) + '</div>');
            }

            parts.push('<div>{{ __('Down Payment Amount') }}: ' + escapeHtml(money(info.amount, currency)) + '</div>');

            $('#settlements-info-down-payment-details').html(parts.join('')).show();

            return true;
        }

        $(document).on('click', '.js-settlements-info', function (e) {
            e.preventDefault();
            reset();
            $modal.modal('show');

            $.getJSON($(this).data('url'))
                .done(function (data) {
                    $('#settlements-info-loading').hide();

                    if (!data.rows || !data.rows.length) {
                        /**
                         * * الدفعة المقدمة الصافية مالهاش فواتير مسوّاة أصلا ،
                         * * فرسالة "مفيش فواتير مسوّاة" لوحدها كانت بتبقى مضللة
                         */
                        if (!renderDownPayment(data.down_payment, data.currency)) {
                            $('#settlements-info-empty').show();
                        }
                        return;
                    }

                    data.rows.forEach(function (row) {
                        var label = row.is_from_down_payment
                            ? ' <span class="label label-inline label-light-primary">{{ __('From Down Payment') }}</span>'
                            : '';
                        /**
                         * * الصف اللي فاتورته مش موجودة بيفضل جنبه مبلغ تسوية
                         * * حقيقي ، فبنميّزه بدل ما يبان فاتورة بصفر
                         */
                        var invoiceNumber = row.has_invoice === false
                            ? '<span class="text-danger">' + escapeHtml(row.invoice_number) + '</span>'
                            : escapeHtml(row.invoice_number);
                        $('#settlements-info-body').append(
                            '<tr>' +
                            '<td>' + invoiceNumber + label + '</td>' +
                            '<td>' + row.invoice_date + '</td>' +
                            '<td>' + row.due_date + '</td>' +
                            '<td class="text-right">' + row.invoice_amount + '</td>' +
                            '<td class="text-right">' + row.settlement_amount + '</td>' +
                            '<td class="text-right">' + row.withhold_amount + '</td>' +
                            '</tr>'
                        );
                    });

                    $('#settlements-info-foot').append(
                        '<tr>' +
                        '<th colspan="4" class="text-right">{{ __('Total') }}</th>' +
                        '<th class="text-right">' + money(data.total_settlement, data.currency) + '</th>' +
                        '<th class="text-right">' + money(data.total_withhold, data.currency) + '</th>' +
                        '</tr>'
                    );

                    renderDownPayment(data.down_payment, data.currency);

                    $('#settlements-info-content').show();
                })
                .fail(function () {
                    $('#settlements-info-loading').hide();
                    $('#settlements-info-error').show();
                });
        });
    })();
</script>
@endpush
