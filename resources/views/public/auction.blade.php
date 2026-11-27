@extends('layouts.app')
@section('title', 'Auction')
@section('content')
<h1 class="title">Live Auction</h1>
<div data-refresh="{{ route('auction.data') }}" data-every="3000">@include('partials.auction-board')</div>
@endsection
