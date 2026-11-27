@extends('layouts.app')
@section('content')
@php
    $start = \Carbon\Carbon::parse($site['start_date']);
    $end = \Carbon\Carbon::parse($site['end_date']);
    if ($start->format('Ym') === $end->format('Ym') && $start->diffInDays($end) < 7) {
        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) { $days[] = $d->format('d'); }
        $dates = implode(' / ', $days).' '.$start->format('F Y');
    } else {
        $dates = $start->format('d M').' - '.$end->format('d M Y');
    }
@endphp
<section class="hero">
    <img src="{{ ! empty($site['logo']) ? asset('storage/'.$site['logo']) : asset('images/logo.svg') }}" alt="">
    <p class="kicker">{{ $site['tagline'] }}</p>
    <h1>{{ $site['title'] }}</h1>
    <p>Organised by <strong>{{ $site['organizer'] }}</strong></p>
    <div class="facts">
        <div><small>Date</small><b>{{ $dates }}</b></div>
        <div><small>Venue</small><b>{{ $site['venue'] }}</b></div>
        <div><small>Total Teams</small><b>{{ $site['total_teams'] }}</b></div>
    </div>
    <div class="countdown" data-countdown="{{ $start->format('Y-m-d') }}T00:00:00+05:30"></div>
    @if ($site['registration_open'] === '1')
        <a class="btn gold" href="{{ route('register') }}">Register as Player</a>
    @else
        <span class="badge">Registration closed</span>
    @endif
</section>

<div class="tiles">
    @foreach (['register' => ['📝', 'Registration'], 'players' => ['🧢', 'Players'], 'teams' => ['🛡️', 'Teams'], 'auction' => ['🔨', 'Auction'], 'live' => ['🔴', 'Live Score'], 'matches' => ['📅', 'Matches'], 'points' => ['🏆', 'Points Table'], 'cric11.index' => ['⚡', 'Cric11 Fantasy']] as $r => [$icon, $label])
        <a href="{{ route($r) }}"><i>{{ $icon }}</i><span>{{ $label }}</span></a>
    @endforeach
</div>

<div class="stats">
    <div><strong>{{ $playerCount }}</strong><span>Total Player Registration</span></div>
    <div><strong>{{ $teams->count() }} / {{ $site['total_teams'] }}</strong><span>Teams Added</span></div>
</div>

@if ($live->count())
    <h2 class="title">Live Now</h2>
    <div class="grid">@foreach ($live as $m)@include('partials.match-card', ['m' => $m])@endforeach</div>
@endif

@if ($upcoming->count())
    <h2 class="title">Upcoming Matches</h2>
    <div class="grid">@foreach ($upcoming as $m)@include('partials.match-card', ['m' => $m])@endforeach</div>
@endif
@endsection
