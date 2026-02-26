@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{$pageTitle .' [ '. $cashFlowStatement->FinancialStatement->name. ' ] - [' . ucfirst($reportType) .' ]' }}</x-main-form-title>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">

        <div class="kt-portlet">


            <div class="kt-portlet__body">
                @include('admin.cash-flow-statement.report.view-table' )
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>

<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<script>

    function getDateFormatted(yourDate) {
        const offset = yourDate.getTimezoneOffset()
        yourDate = new Date(yourDate.getTime() - (offset * 60 * 1000))
        return yourDate.toISOString().split('T')[0]
    }
	
    am4core.ready(function() {

        // Themes begin
       


    }); // end am4core.ready()

</script>
<script>
 

</script>

@endsection
