@php
    use Carbon\Carbon;
    $noRowHeaders = $reportInterval === 'weekly' ? 3 : 1;
    $rowTotal = static function (array $vals, array $keys): float {
        $s = 0.0;
        foreach ($keys as $k) {
            $s += (float) ($vals[$k] ?? 0);
        }
        return $s;
    };
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="kt-portlet kt-portlet--mobile">
            <div class="table-custom-container position-relative ccf-table-outer">
                <div class="ccf-table-inner">
                    <table class="table kt_table_with_no_pagination ccf-consolidated-table table-bordered table-hover table-checkable position-relative dataTable no-footer">
                        <thead>
                            <tr class="header-tr">
                                <th rowspan="{{ $noRowHeaders }}" class="view-table-th header-th max-w-classes-expand align-middle text-center">—</th>
                                <th rowspan="{{ $noRowHeaders }}" class="view-table-th header-th max-w-classes-name align-middle text-center">{{ __('Item') }}</th>
                                <th class="view-table-th max-w-weeks header-th align-middle text-center">
                                    @if($reportInterval === 'weekly'){{ __('Week Num') }}@elseif($reportInterval === 'monthly'){{ __('Months') }}@else{{ __('Days') }}@endif
                                </th>
                                @foreach($weekKeys as $wk)
                                    <th class="view-table-th header-th max-w-weeks align-middle text-center">
                                        @if($reportInterval === 'weekly')
                                            @php $parts = explode('-', $wk); @endphp
                                            <span class="d-block">{{ __('Week '.$weeks[$wk]) }}</span><span class="d-block">[ {{ $parts[1] ?? '' }} ]</span>
                                        @elseif($reportInterval === 'monthly')
                                            <span class="d-block">{{ isset($dates[$wk]['start_date']) ? Carbon::make($dates[$wk]['start_date'])->format('m-Y') : ($weeks[$wk] ?? $wk) }}</span>
                                        @else
                                            <span class="d-block">{{ isset($dates[$wk]['start_date']) ? Carbon::make($dates[$wk]['start_date'])->format('d-m-Y') : $wk }}</span>
                                        @endif
                                    </th>
                                @endforeach
                                <th rowspan="{{ $noRowHeaders }}" class="view-table-th header-th max-w-grand-total align-middle text-center">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="main-with-no-child ccf-section-title">
                                <td colspan="{{ 4 + count($weekKeys) }}">{{ __('Section A — Company level (Cash & Banks Balance)') }}</td>
                            </tr>
                            @foreach($banksSection as $label => $row)
                                @php $tot = $rowTotal($row['total'] ?? [], $weekKeys); @endphp
                                <tr class="parent-tr reset-table-width sub-text-bg text-capitalize is-close">
                                    <td class="sub-text-bg main-tr is-close"></td>
                                    <td class="sub-text-bg max-w-classes-name is-name-cell">{{ $label }}</td>
                                    <td class="sub-numeric-bg text-center">—</td>
                                    @foreach($weekKeys as $wk)
                                        @php $v = (float) ($row['total'][$wk] ?? 0); @endphp
                                        <td class="sub-numeric-bg text-center">{{ number_format($v, 0) }}</td>
                                    @endforeach
                                    <td class="sub-numeric-bg text-center">{{ number_format($tot, 0) }}</td>
                                </tr>
                            @endforeach
                            {{--
                                Company-level cash IN not covered by the selected contracts —
                                the inflow counterpart of the "Company cash out (unallocated)"
                                row further down. Both are now also folded into Section C's
                                grand totals.
                            --}}
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Cash Inflow (unallocated)') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)
                                    <td class="sub-numeric-bg text-center">{{ number_format((float) (($companyUnallocatedCashIn ?? [])[$wk] ?? 0), 0) }}</td>
                                @endforeach
                                <td class="sub-numeric-bg text-center">{{ number_format($rowTotal(($companyUnallocatedCashIn ?? []), $weekKeys), 0) }}</td>
                            </tr>

                            @foreach($contractsSection as $block)
                                <tr class="main-with-no-child ccf-section-title">
                                    <td colspan="{{ 4 + count($weekKeys) }}">{{ $block['contract_name'] }} @if(!empty($block['contract_code'])) [{{ $block['contract_code'] }}] @endif</td>
                                </tr>
                                <tr class="parent-tr reset-table-width sub-text-bg">
                                    <td class="sub-text-bg main-tr"></td>
                                    <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Total Cash Inflow') }}</td>
                                    <td class="sub-numeric-bg text-center">—</td>
                                    @foreach($weekKeys as $wk)<td class="sub-numeric-bg text-center">{{ number_format((float) ($block['cash_inflow'][$wk] ?? 0), 0) }}</td>@endforeach
                                    <td class="sub-numeric-bg text-center">{{ number_format($rowTotal($block['cash_inflow'], $weekKeys), 0) }}</td>
                                </tr>
                                <tr class="parent-tr reset-table-width sub-text-bg">
                                    <td class="sub-text-bg main-tr"></td>
                                    <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Total Cash Outflow') }}</td>
                                    <td class="sub-numeric-bg text-center">—</td>
                                    @foreach($weekKeys as $wk)<td class="sub-numeric-bg text-center">{{ number_format((float) ($block['cash_outflow'][$wk] ?? 0), 0) }}</td>@endforeach
                                    <td class="sub-numeric-bg text-center">{{ number_format($rowTotal($block['cash_outflow'], $weekKeys), 0) }}</td>
                                </tr>
                                <tr class="parent-tr reset-table-width sub-text-bg">
                                    <td class="sub-text-bg main-tr"></td>
                                    <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Net Cash (+/-)') }}</td>
                                    <td class="sub-numeric-bg text-center">—</td>
                                    @foreach($weekKeys as $wk)
                                        @php $v = (float) ($block['net_cash'][$wk] ?? 0); @endphp
                                        <td class="sub-numeric-bg text-center {{ $v < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($v, 0) }}</td>
                                    @endforeach
                                    @php $nt = $rowTotal($block['net_cash'], $weekKeys); @endphp
                                    <td class="sub-numeric-bg text-center {{ $nt < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($nt, 0) }}</td>
                                </tr>
                            @endforeach

                            <tr class="main-with-no-child ccf-section-title">
                                <td colspan="{{ 4 + count($weekKeys) }}">{{ __('Company cash out (unallocated)') }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Company cash out (unallocated)') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)
                                    <td class="sub-numeric-bg text-center">{{ number_format((float) (($companyUnallocatedCashOut ?? [])[$wk] ?? 0), 0) }}</td>
                                @endforeach
                                <td class="sub-numeric-bg text-center">{{ number_format($rowTotal(($companyUnallocatedCashOut ?? []), $weekKeys), 0) }}</td>
                            </tr>

                            <tr class="main-with-no-child ccf-section-title">
                                <td colspan="{{ 4 + count($weekKeys) }}">{{ __('Section C — Grand total') }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Cash & Banks Balance') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)<td class="sub-numeric-bg text-center">{{ number_format((float) ($grandTotal['cash_and_banks'][$wk] ?? 0), 0) }}</td>@endforeach
                                <td class="sub-numeric-bg text-center">{{ number_format($rowTotal(($grandTotal['cash_and_banks'] ?? []), $weekKeys), 0) }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Total Cash Inflow') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)<td class="sub-numeric-bg text-center">{{ number_format((float) ($grandTotal['cash_inflow'][$wk] ?? 0), 0) }}</td>@endforeach
                                <td class="sub-numeric-bg text-center">{{ number_format($rowTotal($grandTotal['cash_inflow'], $weekKeys), 0) }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Total Cash Outflow') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)<td class="sub-numeric-bg text-center">{{ number_format((float) ($grandTotal['cash_outflow'][$wk] ?? 0), 0) }}</td>@endforeach
                                <td class="sub-numeric-bg text-center">{{ number_format($rowTotal($grandTotal['cash_outflow'], $weekKeys), 0) }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Net Cash (+/-)') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)
                                    @php $v = (float) ($grandTotal['net_cash'][$wk] ?? 0); @endphp
                                    <td class="sub-numeric-bg text-center {{ $v < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($v, 0) }}</td>
                                @endforeach
                                @php $gt = $rowTotal($grandTotal['net_cash'], $weekKeys); @endphp
                                <td class="sub-numeric-bg text-center {{ $gt < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($gt, 0) }}</td>
                            </tr>
                            <tr class="parent-tr reset-table-width sub-text-bg is-sub-row is-total-row">
                                <td class="sub-text-bg main-tr"></td>
                                <td class="sub-text-bg max-w-classes-name is-name-cell">{{ __('Accumulated Net Cash (+/-)') }}</td>
                                <td class="sub-numeric-bg text-center">—</td>
                                @foreach($weekKeys as $wk)
                                    @php $v = (float) ($grandTotal['accumulated_net'][$wk] ?? 0); @endphp
                                    <td class="sub-numeric-bg text-center {{ $v < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($v, 0) }}</td>
                                @endforeach
                                @php $lastWk = $weekKeys !== [] ? $weekKeys[array_key_last($weekKeys)] : null; $at = $lastWk !== null ? (float) ($grandTotal['accumulated_net'][$lastWk] ?? 0) : 0.0; @endphp
                                <td class="sub-numeric-bg text-center {{ $at < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($at, 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
