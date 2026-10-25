@extends('layouts.dashboard')
@section('css')
<style>
    .kt-portlet {
        overflow: visible !important;
    }

    .cheques-report-table th {
        background-color: #087383;
        color: #fff !important;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .cheques-report-table td {
        vertical-align: middle;
    }

</style>
@endsection
@section('sub-header')
{{ __('Cheques Report') }} — {{ $typeLabel }} · {{ __('Due Date From') }} {{ $dueDate }}
@endsection
@section('content')
<div class="">
    <div class="row">
        <div class="col-md-12">

            <div class="kt-portlet">
                <div class="kt-portlet__body">

                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                        {{-- Bank filter: re-runs the report, so the totals and the Excel export follow it --}}
                        <form method="get" action="{{ route('result.cheques.report',['company'=>$company->id]) }}" class="form-inline">
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="due_date" value="{{ $dueDate }}">
                            @foreach($currencies as $currencyCode)
                            <input type="hidden" name="currencies[]" value="{{ $currencyCode }}">
                            @endforeach
                            <label class="mr-2">{{ __('Bank') }}</label>
                            <select name="bank_id" class="form-control mr-2" onchange="this.form.submit()">
                                <option value="">{{ __('All banks') }}</option>
                                @foreach($banks as $bank)
                                <option value="{{ $bank['id'] }}" @if($bankId == $bank['id']) selected @endif>{{ $bank['name'] }}</option>
                                @endforeach
                            </select>
                        </form>

                        <div>
                            <a href="{{ route('view.cheques.report',['company'=>$company->id]) }}" class="btn btn-secondary">{{ __('Back') }}</a>
                            <a href="{{ $exportUrl }}" class="btn active-style"><i class="fa fa-file-excel"></i> {{ __('Export to Excel') }}</a>
                        </div>
                    </div>

                    <p class="text-muted">
                        {{ $totals['count'] }} {{ __('Cheques') }}
                        · {{ count($currencies) ? implode(', ', $currencies) : __('All currencies') }}
                    </p>

                    {{-- One total per currency: they can not be added together --}}
                    <div class="row mb-4">
                        @foreach($totals['perCurrency'] as $currencyCode => $total)
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted">{{ __('Total') }} · {{ $currencyCode }}</div>
                                <h4 class="mb-0">{{ number_format($total, 2) }}</h4>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover cheques-report-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $partnerHeading }}</th>
                                    <th>{{ __('Cheque Number') }}</th>
                                    <th>{{ __('Bank Name') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paginator as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $paginator->firstItem() + $index }}</td>
                                    <td>{{ $row['partnerName'] }}</td>
                                    <td class="text-center">{{ $row['chequeNumber'] }}</td>
                                    <td>{{ $row['bankName'] }}</td>
                                    <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                                    <td class="text-center">{{ $row['currency'] }}</td>
                                    <td class="text-center text-nowrap">{{ $row['dueDate'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ in_array($row['status'], ['collected', 'paid']) ? 'badge-success' : ($row['status'] == 'rejected' ? 'badge-danger' : 'badge-warning') }}">{{ $row['statusLabel'] }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">{{ __('No cheques found for these filters.') }}</td>
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
