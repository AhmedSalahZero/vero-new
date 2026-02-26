@extends('layouts.dashboard')
@php
use App\Models\Trading\Expense;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/expenses.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">
@vite(['resources/js/Trading/Views/FixedAssets/fixed-assets.ts'])
<style>
    .js-parent-to-table {
        min-height: 50vh !important;
    }
    .payment_terms {
        min-width: 140px !important;
    }

</style>
@endsection
@section('sub-header')

<x-main-form-title :id="'main-form-title'" :class="''">{{ $title  }}</x-main-form-title>
@endsection
@section('content')
<div id="app-fixed-assets">
	
</div>

@endsection
