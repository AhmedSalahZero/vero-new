@extends('layouts.dashboard')
@section('sub-header')
{{ __('Money Received') }}
@endsection

@push('css')
@include('reports.moneyPayments._dark_theme_styles')
@endpush

@section('content')
@php
    $moneyReceivedPageConfig = [
        'searchFieldsByTab' => $searchFieldsByTab ?? [],
        'advancedFilterUi' => $advancedFilterUi ?? [],
        'tabTitles' => $tabTitles ?? [],
    ];
@endphp
<div class="money-flow-dark">
<script type="application/json" id="money-received-page-config">@json($moneyReceivedPageConfig)</script>
<div
    id="money-received-vue-app"
    data-app-lang="{{ app()->getLocale() }}"
    data-company-id="{{ $company->id }}"
    data-default-active-tab="{{ $defaultActiveTab }}"
    data-json-url="{{ route('view.money.receive.json', ['company' => $company->id]) }}"
    data-create-url="{{ route('create.money.receive', ['company' => $company->id]) }}"
    data-create-down-payment-url="{{ route('create.money.receive', ['company' => $company->id, 'type' => 'down-payment']) }}"
    data-can-create="{{ auth()->user()->can('create money received') ? '1' : '0' }}"
    data-initial-filter-dates="{{ e(json_encode($filterDates ?? [])) }}"
    data-search-fields-by-tab="{{ e(json_encode($searchFieldsByTab ?? [])) }}"
    data-advanced-filter-ui="{{ e(json_encode($advancedFilterUi ?? [])) }}"
></div>

{{-- Bootstrap modals + batch “send to collection” (IDs must exist for .js-can-trigger-cheque-under-collection-modal) --}}
@php
use App\Models\MoneyReceived;
@endphp
<div class="money-received-hidden-bootstrap-modals" aria-hidden="true">
    @foreach([MoneyReceived::CHEQUE, MoneyReceived::CHEQUE_REJECTED] as $mrType)
        <x-export-money
            :account-types="$accountTypes"
            :financial-institution-banks="$financialInstitutionBanks"
            :search-fields="$searchFieldsByTab[$mrType]"
            :money-received-type="$mrType"
            :has-search="false"
            :has-batch-collection="false"
            :banks="$banks"
            :selected-banks="$selectedBanks"
            :href="route('create.money.receive', ['company' => $company->id])"
        />
    @endforeach
</div>
</div>
@endsection

@push('js')
<script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript"></script>
{{-- Same handlers as legacy index.blade.php (account numbers, batch checkboxes, draw bank, etc.) --}}
<script src="{{ url('custom/money-receive.js') }}" type="text/javascript"></script>
@vite('resources/js/MoneyReceived/index.js')
<script>
$(function () {
    /* Move modals out of the zero-size / pointer-events-none wrapper so stacking & Bootstrap work */
    $('.money-received-hidden-bootstrap-modals .modal[id^="send-to-under-collection-modal"]').appendTo('body');
});
/* Datepicker + account fields after modal is visible (Metronic global init may run before modals are in body) */
$(document).on('shown.bs.modal', '[id^="send-to-under-collection-modal"]', function () {
    var $modal = $(this);
    $modal.find('input.kt_datepicker_max_date_is_today[name="deposit_date"]').each(function () {
        var $el = $(this);
        if ($el.data('datepicker')) {
            return;
        }
        if (typeof $.fn.datepicker !== 'function') {
            return;
        }
        $el.datepicker({
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom left',
            endDate: new Date(),
            rtl: false
        });
    });
    $modal.find('.js-update-account-number-based-on-account-type').first().trigger('change');
});
$(document).on('click', '.js-can-trigger-cheque-under-collection-modal', function(e) {
    e.preventDefault();
    const moneyType = $(this).attr('data-money-type');
    const type = $(this).attr('data-type');
    $('#single-or-multi' + moneyType).val(type);
    if (type == 'single') {
        $('#current-single-item' + moneyType).val($(this).attr('data-id'));
        $('#current-currency' + moneyType).val($(this).attr('data-currency'));
    }
    var target = $(this).attr('data-target');
    if (target && $(target).length) {
        $(target).modal('show');
    }
});
$(document).on('submit', '.ajax-send-cheques-to-collection', function(e) {
    e.preventDefault();
    const url = $(this).attr('action');
    const moneyType = $(this).attr('data-money-type');
    const type = $('#single-or-multi' + moneyType).val();
    const singleId = parseInt($('#current-single-item' + moneyType).val(), 10);
    let checked = [];
    $('.js-send-to-collection[data-money-type="' + moneyType + '"]:checked').each(function(index, element) {
        checked.push(parseInt($(element).val(), 10));
    });
    const checkedItems = type == 'multi' ? checked : [singleId];
    const ids = checkedItems.filter(function (id) {
        return id != null && id !== '' && !isNaN(id);
    });
    if (!ids.length) {
        Swal.fire({ icon: 'error', text: '{{ __("Could not resolve cheque id. Close the modal and try again.") }}' });
        return;
    }
    let form = document.getElementById('ajax-send-cheques-to-collection-id' + moneyType);
    let formData = new FormData(form);
    /* MoneyReceivedController expects string or array; explode(',', ...) matches legacy append(cheques, array).toString() */
    formData.set('cheques', ids.join(','));
    $.ajax({
        cache: false,
        contentType: false,
        processData: false,
        url: url,
        data: formData,
        type: 'post',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    }).then(function(res) {
        try {
            $('#send-to-under-collection-modal' + moneyType).modal('hide');
        } catch (e) {}
        if (!res || typeof res !== 'object') {
            Swal.fire({ icon: 'error', text: '{{ __("Invalid server response") }}' });
            return;
        }
        if (res.status === false) {
            Swal.fire({ text: res.msg || '', icon: 'error' }).then(function () {
                if (res.pageLink) {
                    window.location.assign(res.pageLink);
                }
            });
            return;
        }
        /* Full navigation: guarantees fresh Vue + correct ?active=cheque-under-collection (SPA reload alone was unreliable). */
        if (res.pageLink) {
            window.location.assign(res.pageLink);
            return;
        }
        if (typeof window.moneyReceivedVueReloadAfterSendToCollection === 'function') {
            window.moneyReceivedVueReloadAfterSendToCollection('cheque-under-collection');
        }
    }).catch(function(res) {
        var title = "{{ __('Error !') }}";
        var message = "{{ __('Something went Wrong') }}";
        if (res.responseJSON && res.responseJSON.errors) {
            message = res.responseJSON.errors[Object.keys(res.responseJSON.errors)[0]][0];
        }
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
        });
    });
});
/* Same as legacy index.blade.php — update Balance / Net balance when Account Number changes (send-under-collection modal) */
$(document).on('change', '.js-account-number', function() {
    var parent = $(this).closest('.modal-body');
    if (!parent.length) {
        parent = $(this).closest('.kt-portlet__body');
    }
    if (!parent.length) {
        parent = $(this).closest('form');
    }
    var financialInstitutionId = parent.find('select.js-drawl-bank').val();
    var accountNumber = $(this).val();
    var accountType = parent.find('select.js-update-account-number-based-on-account-type').val();
    if (!accountNumber || !accountType || !financialInstitutionId) {
        return;
    }
    $.ajax({
        url: "{{ route('update.balance.and.net.balance.based.on.account.number', ['company' => $company->id]) }}",
        data: {
            accountNumber: accountNumber,
            accountType: accountType,
            financialInstitutionId: financialInstitutionId
        },
        type: 'get',
        success: function(res) {
            if (res.balance_date) {
                parent.find('.balance-date-js').html('[ ' + res.balance_date + ' ]');
            }
            if (res.net_balance_date) {
                parent.find('.net-balance-date-js').html('[ ' + res.net_balance_date + ' ]');
            }
            parent.find('.net-balance-js').val(number_format(res.net_balance));
            parent.find('.balance-js').val(number_format(res.balance));
        }
    });
});
</script>
@endpush
