@extends('layouts.app')
@section('title', 'Players')
@section('content')
<h1 class="title">Registered Players ({{ $players->total() }})</h1>
<form class="filters">
    <input name="q" value="{{ $search }}" placeholder="Search Player Name or Village...">
    <select name="role"><option value="">All roles</option>
        @foreach (\App\Models\Player::ROLES as $r)<option @selected($role === $r)>{{ $r }}</option>@endforeach
    </select>
    <button class="btn orange" style="padding:10px 18px">Search</button>
</form>

<div class="p-grid">
    @forelse ($players as $p)
        <div class="p-card">
            <div class="p-card-top">
                <img src="{{ $p->photoUrl() }}" alt="{{ $p->name }}" loading="lazy">
            </div>
            <div class="p-card-body">
                <div class="p-card-name">{{ $p->name }}</div>
                <div class="p-card-pill">{{ $p->role }}@if ($p->team) &middot; {{ $p->team->short_name }}@endif</div>
                <div class="p-card-rows">
                    <div class="p-card-row"><span>📍</span><span><b>Village:</b> {{ $p->village }}</span></div>
                    <div class="p-card-row"><span>🏏</span><span><b>Batting:</b> {{ $p->batting_style }}</span></div>
                </div>
                <details class="p-more">
                    <summary class="btn orange small">View More</summary>
                    <div class="p-more-box">
                        <div><b>Bowling:</b> {{ $p->bowling_style }}</div>
                        @if ($p->jersey_name || $p->jersey_number !== null)
                            <div><b>Jersey:</b> {{ $p->jersey_name ?: '-' }} {{ $p->jersey_number !== null ? '#'.$p->jersey_number : '' }}</div>
                        @endif
                        <div><b>Team:</b> {{ $p->team?->name ?? 'Unassigned' }}</div>
                    </div>
                </details>
            </div>
        </div>
    @empty
        <div class="card empty">No players yet.</div>
    @endforelse
</div>
{{ $players->links('partials.pagination') }}
@endsection
