@php
    $banks = [];
    foreach ($detailItems as $detailItem) {
        $bankName = isset($detailItem['branch_name']) ? $detailItem['branch_name'] : $detailItem['financial_institution_name'];
        if (! isset($banks[$bankName])) {
            $banks[$bankName] = [
                'name' => $bankName,
                'limit' => 0,
                'outstanding_balance' => 0,
                'room' => 0,
                'cash_cover' => 0,
                'facilities' => [],
            ];
        }
        foreach (['limit', 'outstanding_balance', 'room', 'cash_cover'] as $colName) {
            $banks[$bankName][$colName] += $detailItem[$colName] ?? 0;
        }
        $banks[$bankName]['facilities'][] = $detailItem;
    }
    $banks = array_values($banks);
    $totals = [];
@endphp

<div class="modal fade" id="{{ $modalId.$currency.$lgOrLcType }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <form action="#" class="modal-content" method="post">
		@csrf
            <div class="modal-header">
                <h5 class="modal-title"  id="exampleModalLongTitle"> {{ $title }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="customize-elements">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center w-50-percentage text-capitalize th-main-color">{{ __('Financial Institution') }}</th>
                                <th class="text-center text-capitalize th-main-color"> {!! __('Limit') !!} </th>
                                <th class="text-center text-capitalize th-main-color"> {!! __('Outstanding') !!} </th>
                                <th class="text-center text-capitalize th-main-color"> {!! __('Room') !!} </th>
                                <th class="text-center text-capitalize th-main-color"> {!! __('Cash Cover') !!} </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($banks as $bankIndex => $bank)
                            @php
                                $contractsModalId = $modalId.'-contracts-'.$bankIndex.'-'.$currency.'-'.$lgOrLcType;
                            @endphp
                            <tr>
                                <td class="w-50-percentage">
                                    <div class="d-flex align-items-center">
                                        <input disabled type="text" class="form-control text-left ignore-global-style" value="{{ $bank['name'] }}">
                                        @if(count($bank['facilities']) > 1)
                                        <button type="button" class="btn btn-sm btn-brand btn-elevate btn-pill text-white ml-2 text-nowrap" data-toggle="modal" data-target="#{{ $contractsModalId }}">{{ __('Contracts') }}</button>
                                        @endif
                                    </div>
                                </td>
                                @foreach(['limit','outstanding_balance','room','cash_cover'] as $colName)
                                <td>
                                    <div class="kt-input-icon">
                                        <div class="input-group">
                                            <input disabled type="text" class="form-control text-center ignore-global-style" value="{{ number_format($bank[$colName]) }}">
                                            @php
                                                $totals[$colName] = isset($totals[$colName]) ? $totals[$colName] + $bank[$colName] : ($bank[$colName] ?? 0);
                                            @endphp
                                        </div>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                @foreach(['limit','outstanding_balance','room','cash_cover'] as $colName)
                                <td class="text-center">
                                    {{ number_format($totals[$colName] ?? 0) }}
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </form>
    </div>
</div>

@foreach($banks as $bankIndex => $bank)
    @if(count($bank['facilities']) > 1)
    @php
        $contractsModalId = $modalId.'-contracts-'.$bankIndex.'-'.$currency.'-'.$lgOrLcType;
        $contractTotals = [];
    @endphp
    <div class="modal fade lg-contracts-modal" id="{{ $contractsModalId }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Contracts') }} — {{ $bank['name'] }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center text-capitalize th-main-color">{{ __('Contract Name') }}</th>
                                <th class="text-center text-capitalize th-main-color">{{ __('Start Date') }}</th>
                                <th class="text-center text-capitalize th-main-color">{{ __('End Date') }}</th>
                                <th class="text-center text-capitalize th-main-color">{!! __('Limit') !!}</th>
                                <th class="text-center text-capitalize th-main-color">{!! __('Outstanding') !!}</th>
                                <th class="text-center text-capitalize th-main-color">{!! __('Room') !!}</th>
                                <th class="text-center text-capitalize th-main-color">{!! __('Cash Cover') !!}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bank['facilities'] as $facility)
                            <tr>
                                <td>{{ $facility['name'] ?: '—' }}</td>
                                <td class="text-center">{{ $facility['contract_start_date'] ?: '—' }}</td>
                                <td class="text-center">{{ $facility['contract_end_date'] ?: '—' }}</td>
                                @foreach(['limit','outstanding_balance','room','cash_cover'] as $colName)
                                <td class="text-center">
                                    {{ number_format($facility[$colName] ?? 0) }}
                                    @php
                                        $contractTotals[$colName] = ($contractTotals[$colName] ?? 0) + ($facility[$colName] ?? 0);
                                    @endphp
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                            <tr>
                                <td>{{ __('Total') }}</td>
                                <td></td>
                                <td></td>
                                @foreach(['limit','outstanding_balance','room','cash_cover'] as $colName)
                                <td class="text-center">{{ number_format($contractTotals[$colName] ?? 0) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) {
        return;
    }
    jQuery(document).on('shown.bs.modal', '.lg-contracts-modal', function () {
        var backdrops = jQuery('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.last().css('z-index', 1055);
        }
        jQuery(this).css('z-index', 1060);
    });
});
</script>
@endonce
