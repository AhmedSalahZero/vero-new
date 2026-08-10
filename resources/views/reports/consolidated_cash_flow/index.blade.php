@extends('layouts.dashboard')
@push('css')
<x-styles.commons></x-styles.commons>
@endpush
@section('css')
<link href="{{ url('assets/vendors/general/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ url('assets/vendors/general/bootstrap-select/dist/css/bootstrap-select.css') }}" rel="stylesheet" type="text/css" />
<style>
    .kt-portlet {
        overflow: visible !important;
    }
    .input-group.date > .datepicker:not(.datepicker-dropdown) {
        display: none !important;
    }
    .ccf-tip {
        color: #0f172a;
        margin-bottom: 1rem;
    }
</style>
@endsection
@section('sub-header')
{{ __('Consolidated Cash Flow') }}
@endsection
@section('content')
@php
    $mainCurrency = strtoupper(trim((string) $company->getMainFunctionalCurrency()));
    $selectedCurrencies = old('currencies', old('currency') ? [old('currency')] : [$mainCurrency]);
    if (! is_array($selectedCurrencies)) {
        $selectedCurrencies = [$selectedCurrencies];
    }
    $selectedCurrencies = array_map(static fn ($c) => strtoupper(trim((string) $c)), $selectedCurrencies);
    $selectedYear = (string) old('year', '');
    $contractsForJs = $activeContracts->map(function ($c) {
        $label = $c->getName();
        if ($c->getCode()) {
            $label .= ' [' . $c->getCode() . ']';
        }

        return [
            'id' => (string) $c->id,
            'label' => $label,
            'currency' => strtoupper(trim((string) $c->getCurrency())),
            // null = open-ended contract, it never drops out of the year filter
            'end_year' => $c->getEndYear(),
        ];
    })->values();
@endphp
<div>
<form class="kt-form kt-form--label-right" method="get" action="{{ route('reports.consolidated-cash-flow.result', ['company' => $company->id]) }}">
    <div class="kt-portlet">
        <div class="kt-portlet__body">
            <p class="text-red mb-2">{{ __('Note: the report period must include today (same rule as the main cash flow report).') }}</p>
            <p class="ccf-tip mb-4">{{ __('Tip: leave contracts empty to include all active contracts, or select the ones you need. Monthly interval is faster than daily for long periods.') }} {{ __('Currency filters which contracts are included; amounts stay in the company functional currency.') }}</p>
            <div class="form-group row">
                <div class="col-md-3">
                    <label>{{ __('Report Interval') }} @include('star')</label>
                    <select name="report_interval" class="form-control" required>
                        <option value="daily">{{ __('Daily') }}</option>
                        <option value="weekly" selected>{{ __('Weekly') }}</option>
                        <option value="monthly">{{ __('Monthly') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <x-calendar :onlyMonth="false" :value="formatDateForDatePicker(old('start_date', now()->format('Y-m-d')))" :label="__('Start Date')" :id="'ccf_start'" :name="'start_date'"></x-calendar>
                </div>
                <div class="col-md-3">
                    <x-calendar :onlyMonth="false" :value="formatDateForDatePicker(old('end_date', now()->addMonths(6)->format('Y-m-d')))" :label="__('End Date')" :id="'ccf_end'" :name="'end_date'"></x-calendar>
                </div>
                <div class="col-md-3">
                    <label>{{ __('Currency') }}</label>
                    <select name="currencies[]" id="ccf_currency" class="form-control select2-select" multiple data-live-search="true" data-actions-box="true">
                        @foreach (getBanksCurrencies() as $currencyId => $currentName)
                            @php $code = strtoupper(trim((string) $currencyId)); @endphp
                            <option value="{{ $currencyId }}" @if (in_array($code, $selectedCurrencies, true) || in_array(strtoupper(trim((string) $currentName)), $selectedCurrencies, true)) selected @endif>
                                {{ touppercase($currentName) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label>{{ __('Year') }}</label>
                    <select name="year" id="ccf_year" class="form-control">
                        <option value="">{{ __('All years') }}</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @if ($selectedYear === (string) $year) selected @endif>{{ $year }}</option>
                        @endforeach
                    </select>
                    <span class="form-text text-muted">{{ __('Keeps only contracts ending in that year or later.') }}</span>
                </div>
                <div class="col-md-9">
                    <label>{{ __('Contracts') }} ({{ __('leave empty for all active contracts') }})</label>
                    <select name="contract_ids[]" id="ccf_contracts" class="form-control select2-select" multiple data-live-search="true" data-actions-box="true" data-max-options="200">
                        @foreach ($activeContracts as $c)
                            <option value="{{ $c->id }}" data-currency="{{ $c->getCurrency() }}" @if (collect(old('contract_ids', []))->contains($c->id)) selected @endif>
                                {{ $c->getName() }} @if ($c->getCode()) [{{ $c->getCode() }}] @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <x-custom-button-name-to-submit :displayName="__('Run Report')" />
</form>
</div>
@endsection
@section('js')
<script src="{{ url('assets/vendors/general/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/custom/js/vendors/bootstrap-datepicker.init.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-datepicker.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/vendors/general/bootstrap-select/dist/js/bootstrap-select.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/demo1/pages/crud/forms/widgets/bootstrap-select.js') }}" type="text/javascript"></script>
<script>
    $(function () {
        const $dateInputs = $('input.datepicker-input:not(.only-month-year-picker)');
        $dateInputs.each(function () {
            const $input = $(this);
            if ($input.data('datepicker')) {
                $input.datepicker('destroy');
            }
        });
        $dateInputs.datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom auto',
            container: 'body',
            zIndexOffset: 1100,
            disableTouchKeyboard: true,
        });
    });
</script>
@endsection
@push('js')
<script>
    $(function () {
        // Layout calls reinitializeSelect2() after @stack('js'); wait for it,
        // then only refresh contracts — do not destroy #ccf_currency.
        setTimeout(function () {
            const $currency = $('#ccf_currency');
            const $year = $('#ccf_year');
            const $contracts = $('#ccf_contracts');
            const allContracts = @json($contractsForJs);
            let preferredSelectedIds = @json(array_map('strval', old('contract_ids', [])));

            function normalizeCurrency(value) {
                return String(value || '').trim().toUpperCase();
            }

            function selectedCurrencies() {
                const raw = $currency.val();
                if (!raw) {
                    return [];
                }
                return (Array.isArray(raw) ? raw : [raw]).map(normalizeCurrency).filter(Boolean);
            }

            function selectedYear() {
                const parsed = parseInt($year.val(), 10);
                return isNaN(parsed) ? null : parsed;
            }

            function matchesYear(contract, year) {
                if (year === null || contract.end_year === null || contract.end_year === undefined) {
                    return true;
                }
                return contract.end_year >= year;
            }

            function refreshContractsPicker() {
                if (typeof $contracts.selectpicker !== 'function') {
                    return;
                }
                try {
                    $contracts.selectpicker('destroy');
                } catch (e) {}
                const maxOptions = $contracts.data('max-options') || 200;
                $contracts.selectpicker({
                    liveSearch: true,
                    actionsBox: true,
                    maxOptions: maxOptions,
                    buttons: ['selectMax', 'disableAll'],
                });
                $contracts.data('max-options', maxOptions);
            }

            function refreshContractsSelect(clearSelection) {
                const currencies = selectedCurrencies();
                const currencySet = {};
                currencies.forEach(function (c) { currencySet[c] = true; });

                const keepSelected = clearSelection
                    ? []
                    : (($contracts.val() || []).length ? ($contracts.val() || []) : preferredSelectedIds).map(String);

                const year = selectedYear();
                const matching = allContracts.filter(function (contract) {
                    return !!currencySet[normalizeCurrency(contract.currency)] && matchesYear(contract, year);
                });

                $contracts.empty();
                matching.forEach(function (contract) {
                    const selected = keepSelected.indexOf(String(contract.id)) !== -1;
                    $contracts.append(
                        $('<option></option>')
                            .attr('value', contract.id)
                            .attr('data-currency', contract.currency)
                            .prop('selected', selected)
                            .text(contract.label)
                    );
                });

                refreshContractsPicker();
            }

            $(document)
                .off('changed.bs.select.ccfCurrency change.ccfCurrency')
                .on('changed.bs.select.ccfCurrency change.ccfCurrency', '#ccf_currency', function () {
                    preferredSelectedIds = [];
                    refreshContractsSelect(true);
                });

            $(document)
                .off('change.ccfYear')
                .on('change.ccfYear', '#ccf_year', function () {
                    preferredSelectedIds = [];
                    refreshContractsSelect(true);
                });

            refreshContractsSelect(false);
        }, 50);
    });
</script>
@endpush
