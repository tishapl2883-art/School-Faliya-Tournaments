@extends('layouts.app')
@section('title', 'Manage Players')
@section('content')
<h1 class="title">Players ({{ $players->total() }})</h1>
<form class="filters">
    <input name="q" value="{{ $search }}" placeholder="Name or mobile">
    <select name="status"><option value="">All</option>
        @foreach (['pending', 'approved', 'rejected'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <button class="btn">Filter</button>
    <a class="btn" href="{{ route('admin.players.export') }}">Download CSV</a>
</form>
<div class="card scroll desktop-only"><table>
    <tr><th>#</th><th>Player</th><th>Mobile</th><th>Payment</th><th>Status</th><th>Team</th><th>Actions</th></tr>
    @forelse ($players as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td><div class="who"><img src="{{ $p->photoUrl() }}" alt=""><span><strong>{{ $p->name }}</strong><small>{{ $p->role }} &middot; {{ $p->village }} &middot; {{ $p->tshirt_size }}</small></span></div></td>
            <td>{{ $p->mobile }}</td>
            <td>{{ $p->payment_ref ?: '-' }}@if ($p->payment_proof)<small><a target="_blank" href="{{ route('admin.players.payment', $p) }}">Screenshot</a></small>@endif</td>
            <td><span class="badge {{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
            <td>{{ $p->team?->short_name ?? '-' }}</td>
            <td class="rowact">
                @if ($p->status !== 'approved')
                    <form method="post" action="{{ route('admin.players.status', $p) }}">@csrf<input type="hidden" name="status" value="approved"><button class="btn icon green" title="Approve" aria-label="Approve"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></button></form>
                @endif
                @if ($p->status !== 'rejected')
                    <form method="post" action="{{ route('admin.players.status', $p) }}">@csrf<input type="hidden" name="status" value="rejected"><button class="btn icon" title="Reject" aria-label="Reject"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></form>
                @endif
                <a class="btn icon" href="{{ route('admin.players.edit', $p) }}" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                <form method="post" action="{{ route('admin.players.delete', $p) }}" data-confirm="Delete this player?">@csrf @method('delete')<button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7">No players found.</td></tr>
    @endforelse
</table></div>

<div class="mobile-only">
    <div class="p-grid">
        @forelse ($players as $p)
            <div class="p-card">
                <div class="p-card-top">
                    <span class="p-card-corner">#{{ $p->id }}</span>
                    <span class="badge {{ $p->status }} p-card-status">{{ ucfirst($p->status) }}</span>
                    <img src="{{ $p->photoUrl() }}" alt="{{ $p->name }}" loading="lazy">
                </div>
                <div class="p-card-body">
                    <div class="p-card-name">{{ $p->name }}</div>
                    <div class="p-card-pill">{{ $p->role }}@if ($p->team) &middot; {{ $p->team->short_name }}@endif</div>
                    <div class="p-card-rows">
                        <div class="p-card-row"><span>📍</span><span><b>Village:</b> {{ $p->village }}</span></div>
                        <div class="p-card-row"><span>📞</span><span><b>Mobile:</b> {{ $p->mobile }}</span></div>
                        <div class="p-card-row"><span>👕</span><span><b>T-Shirt:</b> {{ $p->tshirt_size }} &middot; {{ $p->batting_style }}</span></div>
                        @if ($p->payment_ref || $p->payment_proof)
                            <div class="p-card-row"><span>💳</span><span><b>Payment:</b> {{ $p->payment_ref ?: 'Uploaded' }} @if ($p->payment_proof)&middot; <a target="_blank" href="{{ route('admin.players.payment', $p) }}">Screenshot</a>@endif</span></div>
                        @endif
                    </div>
                    <div class="p-card-foot">
                        @if ($p->status !== 'approved')
                            <form method="post" action="{{ route('admin.players.status', $p) }}">@csrf<input type="hidden" name="status" value="approved"><button class="btn icon green" title="Approve" aria-label="Approve"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></button></form>
                        @endif
                        @if ($p->status !== 'rejected')
                            <form method="post" action="{{ route('admin.players.status', $p) }}">@csrf<input type="hidden" name="status" value="rejected"><button class="btn icon" title="Reject" aria-label="Reject"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></form>
                        @endif
                        <a class="btn icon" href="{{ route('admin.players.edit', $p) }}" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                        <form method="post" action="{{ route('admin.players.delete', $p) }}" data-confirm="Delete this player?">@csrf @method('delete')<button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty">No players found.</div>
        @endforelse
    </div>
</div>
{{ $players->links('partials.pagination') }}
@endsection
