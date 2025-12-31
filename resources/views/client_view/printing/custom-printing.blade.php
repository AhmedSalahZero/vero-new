@extends('layouts.dashboard')
@push('css')

<style>
td , td * {
	font-weight:normal !important ;
	font-size:14px !important;
}
    * {
        white-space: normal !important;
    }

    .stamp {
         display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-top: -72px;
    margin-right: 10px;
    margin-bottom: -20px;
        z-index: 99999
    }

    .max-w-logo {
        max-width: 100px;
        margin: 5px;
    }

</style>
@if($printPaper == 'landscape')
<style type="text/css" media="print">
    @page {
        size: landscape;
    }

</style>

@endif
@if($printPaper == 'portrait')
<style type="text/css" media="print">
    @page {
        size: portrait;
    }

</style>

@endif


<x-styles.commons></x-styles.commons>
<style>
    .show-hide-repeater {
        cursor: pointer
    }

    [data-css-col-name="Code"],
    [data-css-col-name="code"],
    [data-css-col-name="id"],
    [data-css-col-name="ID"],
    [data-css-col-name="Id"],
    [data-css-col-name="Item"],
    [data-css-col-name="item"] {
        max-width: 300px !important;
        min-width: 300px !important;
        width: 300px !important;

    }

    th,
    td {
        white-space: normal !important;
    }

    svg[xmlns],
    svg[xmlns] * {
        width: 100%;
        height: 100%;
    }

    .dt-buttons.btn-group.flex-wrap {
        float: right;
    }

    .arrow-right {
        right: 10px !important;
    }

    .arrow-left {
        left: 10px !important;
    }

    .dataTables_filter {
        display: none !important;
    }

    .flex-1 {
        flex: 1 !important;
    }

    tbody .kt-option {
        border: none;
        padding: 0 !important;
        position: relative !important;
        top: -20px !important;
        max-width: 30px !important;
        left: 28% !important;
        height: 0 !important;
    }

    th .kt-checkbox.kt-checkbox--brand>span:after {
        border-color: white !important;
    }

    th .kt-checkbox.kt-checkbox--brand>span {
        border-color: white !important;
    }

    th .kt-checkbox.kt-checkbox--brand.kt-checkbox--bold>input~span {
        color: white !important;
    }

</style>

<style>
    table {}

</style>

<style type="text/css">
    div.force-new-page {
        page-break-before: always
    }

</style>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/af-2.4.0/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/cr-1.5.6/date-1.1.2/fc-4.1.0/fh-3.2.3/r-2.3.0/rg-1.2.0/sl-1.4.0/sr-1.1.1/datatables.min.css" />

<style>
    table.dataTable thead tr>.dtfc-fixed-left,
    table.dataTable thead tr>.dtfc-fixed-right {
        background-color: #086691;
    }

    thead * {
        text-align: center !important;
    }

    @media print {
		::-webkit-scrollbar {
    display: none;
}

        #kt_header,t
        .kt-header__brand-logo,
        .kt-portlet__head-wrapper,
        #open-instructions,
		.kt-subheader,
		.kt-header,
        .kt-portlet__head-icon,
        .kt-footer,
        #kt_header_mobile,
        .arrow-nav,
        .buttons-html5,
        .buttons-print {
            display: none !important;
        }
    }

</style>

@endpush



@section('sub-header')
{{-- {{ camelToTitle($modelName) }} {{ __('Section') }} --}}
<x-navigators-dropdown :navigators="$navigators ?? []"></x-navigators-dropdown>
@endsection
@section('content')
@php
$user = auth()->user();
@endphp

@if($modelName == 'LabelingItem' )


@endif
@if(count($exportables))
<form action="{{ route('multipleRowsDelete', [$company, $modelName]) }}" method="POST">
    @csrf
    @method('delete')
    @foreach($labeling as $pageIndex=>$salesGatherings )
    <div class="kt-portlet kt-portlet--mobile">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label" style="width:100%">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-secondary btn-outline-hover-danger fa fa-layer-group"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    <x-sectionTitle :title="$reportTitle"></x-sectionTitle>
                    <div class="logs" style="margin-left:auto;">
                        @foreach(['getFirstLabelingLogo','getSecondLabelingLogo','getThirdLabelingLogo'] as $imgFun)
                        @if($company->$imgFun())
                        <img class="max-w-logo" src="{{$company->$imgFun()  }}">
                        @endif
                        @endforeach
                    </div>
                    @if(isset($instructionsIcon))
                    <span id="open-instructions" class="kt-input-icon__icon kt-input-icon__icon--right ml-2 cursor-pointer" tabindex="0" role="button" data-toggle="kt-tooltip" data-trigger="focus" title="{{ __('Uploading Instructions') }}">
                        <span><i class="fa fa-question text-primary"></i></span>
                    </span>




                    @endif
                </h3>
            </div>

        </div>
        <div class="kt-portlet__body table-responsive">

            <!--begin: Datatable -->
            <table class="table table-striped- table-bordered table-hover table-checkable exclude-table ">

                <thead>
                    <tr class="table-active remove-max-class text-center">

                        @if($modelName == 'LabelingItem')
                        <th class="select-to-delete">{{ __('No.') }}</th>
                        <th data-css-col-name="qrcode">
                            @if($company->labeling_type == 'qrcode')
                            {{ __('QR Code') }}
                            @else
                            {{ __('Barcode') }}
                            @endif
                        </th>

                        @endif


                        @foreach ($viewing_names as $name)

                        <th data-css-col-name="{{ $name }}">{{ __($name) }}</th>
                        @endforeach

                        @if($modelName == 'LabelingItem' && ! $hasCodeColumnForLabelingItem)

                        <th data-css-col-name="id">{{ __('ID') }}</th>

                        @endif

                    </tr>
                </thead>
                <tbody>

                    @foreach ($salesGatherings as $index=>$item)
                    @php
                    $serial = \App\Models\LabelingItem::generateSerial($salesGatherings,$index) ;
                    @endphp

                    <tr>
                        @if($modelName == 'LabelingItem')
                        <td>
                            {{ $serial }}
                        </td>
                        <td class="text-center" data-css-col-name="{{ 'qrcode' }}">
                            @php
                            $generateCode = $item->getCode($serial) ;
                            @endphp
                            @if($company->labeling_type == 'barcode')

                            {!! DNS1D::getBarcodeHTML($generateCode, 'C39',3,33 ) !!}
                            @else
                            {{-- <img src="http://127.0.0.1:8000/assets/media/logos/vero%20analysis%20blue%20logo.png"> --}}
                            <img style="max-width:60px;max-height:60px;" src="data:image/png;base64,{!! DNS2D::getBarcodePNG($generateCode, 'QRCODE') !!}" alt="barcode" />

                            @endif
                        </td>

                        @endif


                        @foreach ($db_names as $name)

                        @if ($name == 'date' || $name=='invoice_due_date' || $name == 'invoice_date')
                        <td class="text-center">{{ isset($item->$name) ? date('d-M-Y',strtotime($item->$name)):  '-' }}</td>
                        @elseif($name == 'invoice_amount' || $name == 'vat_amount' || $name == 'withhold_amount' || $name == 'collected_amount' || $name=='net_balance'|| $name=='net_invoice_amount')
                        <td class="text-center">{{ number_format($item->$name?:0 ,2 ) }}</td>
                        @else
                        <td data-css-col-name="{{ $name??'' }}" class="text-center">
                            {{ qrcodeSpacing($item->$name??'') }}


                            @endif
                            @endforeach



                            @if($modelName == 'LabelingItem' && !$hasCodeColumnForLabelingItem)
                        <td data-css-col-name="{{ $name??'' }}">
                            {{ qrcodeSpacing($item->getCode($serial)) }}
                        </td>
                        @endif


                    </tr>

                    @endforeach
                </tbody>
            </table>

            <!--end: Datatable -->
            @if($src=$company->getStampLabelingLogo())
            <div class="row">
                <div class="col-6">
                    <div >{{ __('Page') }} {{ $pageIndex+1 }} / {{ count($labeling) }} </div>
                </div>
                <div class="col-6">
                    <div class="stamp">
                        <img src="{{  $src }}" class="max-w-logo">
                    </div>
                </div>
            </div>

	@endif
        </div>
    </div>

    <div class="force-new-page"></div>
    @endforeach




</form>
@endif


@endsection

@section('js')
@include('js_datatable')
{{-- <script src="{{ url('assets/vendors/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script> --}}
<script src="{{ url('assets/js/demo1/pages/crud/datatables/basic/paginations.js') }}" type="text/javascript"></script>

<script>
    $('#select_all').change(function(e) {
        if ($(this).prop("checked")) {
            $('.rows').prop("checked", true);
        } else {
            $('.rows').prop("checked", false);
        }
    });









    window.addEventListener('scroll', function() {
        const top = window.scrollY > 140 ? window.scrollY + 210 : 250;

        $('.arrow-nav').css('top', top + 'px')
    })
    if ($('div.kt-portlet__body').length) {

        $('div.kt-portlet__body').append(`
								<i class="cursor-pointer text-dark arrow-nav  arrow-left fa fa-arrow-left"></i>
								<i class="cursor-pointer text-dark arrow-nav arrow-right fa  fa-arrow-right"></i>
								`)


        $(document).on('click', '.arrow-nav', function() {
            const scrollLeftOfTableBody = document.querySelector('.kt-portlet__body').scrollLeft
            const scrollByUnit = 500
            if (this.classList.contains('arrow-right')) {
                document.querySelector('.dataTables_scrollBody').scrollLeft += scrollByUnit

            } else {
                document.querySelector('.dataTables_scrollBody').scrollLeft -= scrollByUnit

            }
        })

        window.dispatchEvent(new Event('scroll'));

    }

</script>
<script>
    $(document).on('click', '.show-hide-repeater', function() {
        const query = this.getAttribute('data-query')
        $(query).fadeToggle(300)

    })
    $('.kt_table_with_no_pagination-printing').DataTable({
        // responsive:true ,
        deferRender: true
        , bPaginate: false
        , bLengthChange: false
        , bInfo: false
        , scrollY: true
        , scrollX: true
        , search: false
        , pageLength: 100
        , scrollCollapse: true
        , paging: false
        , paging: false
        , ordering: false

    });
    window.onbeforeprint = function() {
        document.title = "_";
    }

    $(document).click(function() {
        var e = jQuery.Event("keydown");
        e.which = 80; // # P code value
        e.ctrlKey = true; // Alt key pressed
        e.shiftLeftKey = true; // Alt key pressed
        $(document).trigger(e);
    });

</script>
<x-js.commons></x-js.commons>
@endsection
