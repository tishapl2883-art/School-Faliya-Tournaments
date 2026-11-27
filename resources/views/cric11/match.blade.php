@extends('layouts.app')
@section('title', $fixture->teamA->short_name.' vs '.$fixture->teamB->short_name.' | Cric11')
@section('content')
<div class="actions">
    <a class="btn small" href="{{ route('cric11.index') }}">&larr; All Cric11 Matches</a>
    <a class="btn small" href="{{ route('match', $fixture) }}">Full Scorecard</a>
    @if ($fixture->status === 'upcoming')
        <a class="btn small gold" href="{{ route('cric11.create', $fixture) }}">+ Create Team</a>
    @endif
</div>

<div class="card scorehead">
    <div class="meta">
        <span>Match {{ $fixture->match_no }} &middot; {{ $fixture->stage }}</span>
        <span @class(['badge', 'live' => $fixture->status === 'live'])>{{ ucfirst($fixture->status) }}</span>
    </div>
    <div class="versus">
        <strong>{{ $fixture->teamA->name }}</strong><em>vs</em><strong>{{ $fixture->teamB->name }}</strong>
    </div>
    <div class="meta">
        <span>{{ $fixture->starts_at->format('D, d M Y h:i A') }}</span>
        <span>{{ count($leaderboard) }} {{ count($leaderboard) === 1 ? 'Cric11 team' : 'Cric11 teams' }}</span>
    </div>
    @if ($fixture->result_text)<p class="result">{{ $fixture->result_text }}</p>@endif
</div>

<div @if ($fixture->status === 'live') data-refresh="{{ route('cric11.match.data', $fixture) }}" data-every="4000" @endif>
    @include('cric11.partials.match-live')
</div>

<details class="card" style="margin-top:16px">
    <summary>Cric11 Fantasy Points System</summary>
    <div class="scroll" style="margin-top:10px">
        <table>
            <thead><tr><th>Action</th><th>Points</th></tr></thead>
            <tbody>
                @foreach ($rules as $action => $pts)
                    <tr><td>{{ $action }}</td><td><strong>{{ $pts }}</strong></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>
@endsection
