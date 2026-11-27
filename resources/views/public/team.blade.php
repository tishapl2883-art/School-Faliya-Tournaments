@extends('layouts.app')
@section('title', $team->name)
@section('content')
<h1 class="title">{{ $team->name }}</h1>
<div class="chips">
    @if ($team->owner_name)<span>Owner: <b>{{ $team->owner_name }}</b></span>@endif
    @if ($team->captain_name)<span>Captain: <b>{{ $team->captain_name }}</b></span>@endif
    <span>Purse left: <b>{{ number_format($team->remaining()) }}</b></span>
</div>
<div class="p-grid">
    @forelse ($players as $p)
        <div class="p-card">
            <div class="p-card-top">
                <img src="{{ $p->photoUrl() }}" alt="{{ $p->name }}" loading="lazy">
            </div>
            <div class="p-card-body">
                <div class="p-card-name">{{ $p->name }}</div>
                <div class="p-card-pill">{{ $p->role }}</div>
                <div class="p-card-rows">
                    <div class="p-card-row"><span>📍</span><span><b>Village:</b> {{ $p->village }}</span></div>
                    <div class="p-card-row"><span>💰</span><span><b>Auction Price:</b> {{ number_format((int) $p->sold_price) }} pts</span></div>
                </div>
            </div>
        </div>
    @empty
        <div class="card empty">No players in this team yet.</div>
    @endforelse
</div>
@endsection
