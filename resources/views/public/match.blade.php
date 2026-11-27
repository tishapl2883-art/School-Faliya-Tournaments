@extends('layouts.app')
@section('title', 'Scorecard')
@section('content')
<div class="actions" style="justify-content:space-between;align-items:center">
    <h1 class="title" style="margin:0">Scorecard</h1>
    <div class="actions" style="margin:0">
        @if ($fixture->status === 'upcoming')
            <a class="btn small gold" href="{{ route('cric11.create', $fixture) }}">&#9889; Play Cric11</a>
        @endif
        <a class="btn small" href="{{ route('cric11.match', $fixture) }}">Cric11 Leaderboard</a>
    </div>
</div>
<div style="margin-top:12px" @if ($fixture->status !== 'completed') data-refresh="{{ route('match.data', $fixture) }}" data-every="4000" @endif>@include('partials.scorecard')</div>
@endsection
