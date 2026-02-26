@extends('layouts.dashboard')
@section('css')
<x-styles.commons></x-styles.commons>
@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{$pageTitle }}</x-main-form-title>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        
            <div class="kt-portlet" >
          
                
                <div class="kt-portlet__body">
        @include('admin.revenue-business-line.view-table' )
        </div>
</div>
</div>
</div>
@endsection
@section('js')
<x-js.commons></x-js.commons>

<script>
        $(function(){
		
             
        })
</script>

@endsection
