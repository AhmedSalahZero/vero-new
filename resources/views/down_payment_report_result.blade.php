@extends('layouts.dashboard')
@section('css')
<style>
    .kt-portlet {
        overflow: visible !important;
    }

    .down-payment-report-table th {
        background-color: #087383;
        color: #fff !important;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .down-payment-report-table td {
        vertical-align: middle;
    }

</style>
@endsection
@section('sub-header')
{{ __('Down Payment Report') }} — {{ $typeLabel }} · {{ __('From Date') }} {{ $fromDate }}
@endsection
@section('content')
<div class="">
    <div class="row">
        <div class="col-md-12">

            <div class="kt-portlet">
                <div class="kt-portlet__body">

                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                        <p class="text-muted mb-0">
                            {{ $totals['count'] }} {{ __('Down Payments') }}
                            · {{ count($currencies) ? implode(', ', $currencies) : __('All currencies') }}
                        </p>
                        <div>
                            <a href="{{ route('view.down.payment.report',['company'=>$company->id]) }}" class="btn btn-secondary">{{ __('Back') }}</a>
                            <a href="{{ $exportUrl }}" class="btn active-style"><i class="fa fa-file-excel"></i> {{ __('Export to Excel') }}</a>
                        </div>
                    </div>

                    {{-- One card per currency: they can not be added together --}}
                    <div class="row mb-4">
                        @foreach($totals['perCurrency'] as $currencyCode => $sums)
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <div class="text-muted mb-2">{{ $currencyCode }}</div>
                                <div class="d-flex justify-content-between"><span>{{ __('Down Payment Amount') }}</span><b>{{ number_format($sums['amount'], 2) }}</b></div>
                                <div class="d-flex justify-content-between"><span>{{ __('Settled Amount') }}</span><b>{{ number_format($sums['settled'], 2) }}</b></div>
                                <div class="d-flex justify-content-between"><span>{{ __('Balance') }}</span><b class="{{ $sums['balance'] > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($sums['balance'], 2) }}</b></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover down-payment-report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $partnerHeading }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Contract') }}</th>
                                    <th>{{ __('Down Payment Amount') }}</th>
                                    <th>{{ __('Settled Amount') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paginator as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $paginator->firstItem() + $index }}</td>
                                    <td>{{ $row['partnerName'] }}</td>
                                    <td class="text-center text-nowrap">{{ $row['date'] }}</td>
                                    <td>{{ $row['contractName'] }}</td>
                                    <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                                    <td class="text-right">{{ number_format($row['settled'], 2) }}</td>
                                    <td class="text-right"><b class="{{ $row['balance'] > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($row['balance'], 2) }}</b></td>
                                    <td class="text-center">{{ $row['currency'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">{{ __('No down payments found for these filters.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $paginator->links('pagination::bootstrap-4') }}

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
