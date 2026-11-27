@extends('layouts.app')
@section('title', 'Teams')
@section('content')
<h1 class="title">Teams</h1>
<div class="p-grid">
    @forelse ($teams as $t)
        <div class="p-card">
            <div class="p-card-top">
                @if ($t->logo)
                    <img class="logo" src="{{ asset('storage/'.$t->logo) }}" alt="{{ $t->name }}" loading="lazy">
                @else
                    <div class="p-card-initials">{{ $t->short_name }}</div>
                @endif
            </div>
            <div class="p-card-body">
                <div class="p-card-name">{{ $t->name }}</div>
                <div class="p-card-pill">{{ $t->short_name }} &middot; {{ $t->players_count }} {{ $t->players_count === 1 ? 'Player' : 'Players' }}</div>
                <div class="p-card-rows">
                    @if ($t->owner_name)<div class="p-card-row"><span>👤</span><span><b>Owner:</b> {{ $t->owner_name }}</span></div>@endif
                    @if ($t->captain_name)<div class="p-card-row"><span>🧢</span><span><b>Captain:</b> {{ $t->captain_name }}</span></div>@endif
                </div>
                <div class="p-card-foot">
                    <a class="btn orange small" href="{{ route('team', $t) }}">View Squad</a>
                </div>
            </div>
        </div>
    @empty
        <div class="card empty">Teams will be announced soon.</div>
    @endforelse
</div>
@endsection
