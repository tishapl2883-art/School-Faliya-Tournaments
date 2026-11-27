@extends('layouts.app')
@section('title', 'Manage Teams')
@section('content')
<h1 class="title">Teams ({{ $teams->count() }} / {{ $site['total_teams'] }})</h1>
@if ($editing || $teams->count() < (int) $site['total_teams'])
<form class="card form" method="post" action="{{ route('admin.teams.save', $editing?->id) }}" enctype="multipart/form-data">
    @csrf
    <h3 class="full">{{ $editing ? 'Edit team' : 'Add team' }}</h3>
    <label>Team name<input name="name" value="{{ old('name', $editing?->name) }}" required></label>
    <label>Short name (max 6)<input name="short_name" maxlength="6" value="{{ old('short_name', $editing?->short_name) }}" required></label>
    <label>Owner<input name="owner_name" value="{{ old('owner_name', $editing?->owner_name) }}"></label>
    <label>Captain<input name="captain_name" value="{{ old('captain_name', $editing?->captain_name) }}"></label>
    <label>Purse (points)<input type="number" name="purse" value="{{ old('purse', $editing?->purse ?? $site['team_purse']) }}" required></label>
    <label>Logo<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <button class="btn gold full">Save Team</button>
</form>
@endif
<div class="card scroll desktop-only"><table>
    <tr><th>Team</th><th>Players</th><th>Purse</th><th>Left</th><th>Actions</th></tr>
    @forelse ($teams as $t)
        <tr><td><strong>{{ $t->name }}</strong><small>{{ $t->short_name }}</small></td><td>{{ $t->players_count }}</td><td>{{ number_format($t->purse) }}</td><td>{{ number_format($t->remaining()) }}</td>
            <td class="rowact">
                <a class="btn icon" href="{{ route('admin.teams', ['edit' => $t->id]) }}" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                <form method="post" action="{{ route('admin.teams.delete', $t) }}" data-confirm="Delete this team?">@csrf @method('delete')<button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
            </td></tr>
    @empty
        <tr><td colspan="5">No teams yet. Add your first team above.</td></tr>
    @endforelse
</table></div>

<div class="mobile-only">
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
                        <div class="p-card-row"><span>💰</span><span><b>Purse:</b> {{ number_format($t->purse) }} &middot; <b>Left:</b> {{ number_format($t->remaining()) }}</span></div>
                    </div>
                    <div class="p-card-foot">
                        <a class="btn icon" href="{{ route('admin.teams', ['edit' => $t->id]) }}" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                        <form method="post" action="{{ route('admin.teams.delete', $t) }}" data-confirm="Delete this team?">@csrf @method('delete')<button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty">No teams yet. Add your first team above.</div>
        @endforelse
    </div>
</div>
@endsection
