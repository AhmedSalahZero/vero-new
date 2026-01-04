@extends('layouts.dashboard')
@php
use App\Models\NonBankingService\Study;
use App\Models\NonBankingService\ReverseFactoringBreakdown;
@endphp
@section('css')
<x-styles.commons></x-styles.commons>
<link rel="stylesheet" href="/custom/css/non-banking-services/common.css">
<link rel="stylesheet" href="/custom/css/non-banking-services/select2.css">

@endsection
@section('sub-header')
<x-main-form-title :id="'main-form-title'" :class="''">{{ $title }}</x-main-form-title>


@endsection
@section('content')
<div id="app-reverse-factoring">
</div>

@endsection
  @vite('resources/js/reverse-factoring.js')
