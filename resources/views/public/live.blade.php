@extends('layouts.app')
@section('title', 'Live Score')
@section('content')
<h1 class="title">Live Score</h1>
<div data-refresh="{{ route('live.data') }}" data-every="4000">@include('partials.live-list')</div>
@endsection
