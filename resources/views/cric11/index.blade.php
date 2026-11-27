@extends('layouts.app')
@section('title', 'Cric11 Fantasy')
@section('content')
<section class="cric-hero">
    <div>
        <p class="kicker" style="margin-top:0;color:var(--gold)">Cric11 Fantasy League</p>
        <h1>Build Your Dream XI</h1>
        <p>Pick {{ \App\Services\FantasyRules::STANDARD_SIZE }} players within 100 credits, choose your Captain (2x) and Vice-Captain (1.5x), and track live points ball-by-ball.</p>
    </div>
    <div class="actions" style="margin:0">
        <a class="btn gold" href="#matches-hub">Pick a Match</a>
    </div>
</section>

<form class="card filters" method="post" action="{{ route('cric11.profile') }}">
    @csrf
    <input type="text" name="participant_mobile" value="{{ old('participant_mobile', $myMobile) }}" placeholder="Enter 10-digit mobile to view My Teams" pattern="[6-9][0-9]{9}" maxlength="10" required>
    <input type="text" name="participant_name" value="{{ old('participant_name', session('cric11_name')) }}" placeholder="Your name (optional)" maxlength="80">
    <button class="btn">Show My Teams</button>
</form>

@if ($myEntries->count())
    <h2 class="title">My Cric11 Teams</h2>
    <div class="grid">
        @foreach ($myEntries as $e)
            <div class="card">
                <div class="meta">
                    <span>Match {{ $e->fixture->match_no }} &middot; {{ $e->fixture->teamA->short_name }} vs {{ $e->fixture->teamB->short_name }}</span>
                    <span @class(['badge', 'live' => $e->fixture->status === 'live'])>{{ ucfirst($e->fixture->status) }}</span>
                </div>
                <h3 style="margin-top:6px">{{ $e->team_name }}</h3>
                <p style="font-size:13px;color:var(--muted)">
                    C: <strong>{{ $e->captain->name }}</strong> &middot;
                    VC: <strong>{{ $e->viceCaptain->name }}</strong> &middot;
                    {{ number_format($e->total_credits, 1) }} Cr
                </p>
                <div class="actions" style="margin:10px 0 0">
                    <a class="btn small" href="{{ route('cric11.entry', $e) }}">View Pitch</a>
                    <a class="btn small gold" href="{{ route('cric11.match', $e->fixture) }}">Leaderboard</a>
                    @if ($e->fixture->status === 'upcoming')
                        <a class="btn small" href="{{ route('cric11.edit', $e) }}">Edit</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="matches-hub">
@foreach ($groups as $label => $list)
    @if ($list->count())
        <h2 class="title">{{ $label }} Matches</h2>
        <div class="grid">
            @foreach ($list as $m)
                <div class="card">
                    <div class="meta">
                        <span>Match {{ $m->match_no }} &middot; {{ $m->stage }}</span>
                        <span @class(['badge', 'live' => $m->status === 'live'])>{{ ucfirst($m->status) }}</span>
                    </div>
                    <div class="versus">
                        <strong>{{ $m->teamA->name }}</strong><em>vs</em><strong>{{ $m->teamB->name }}</strong>
                    </div>
                    <div class="meta">
                        <span>{{ $m->starts_at->format('D, d M Y h:i A') }}</span>
                        <span>{{ $m->fantasy_entries_count }} {{ $m->fantasy_entries_count === 1 ? 'team' : 'teams' }}</span>
                    </div>
                    @if ($m->result_text)<p class="result">{{ $m->result_text }}</p>@endif
                    <div class="actions" style="margin:12px 0 0">
                        @if ($m->status === 'upcoming')
                            <a class="btn gold small" href="{{ route('cric11.create', $m) }}">+ Create Team</a>
                        @endif
                        <a class="btn small" href="{{ route('cric11.match', $m) }}">
                            {{ $m->status === 'upcoming' ? 'Contests & Rules' : 'Live Leaderboard' }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endforeach
@if (collect($groups)->every->isEmpty())
    <div class="card empty">No matches scheduled yet. Once matches are added in Admin, you can build your Cric11 Dream XI here!</div>
@endif
</div>

@if (count($overall))
    <h2 class="title">Tournament Cric11 Leaderboard</h2>
    <div class="card scroll">
        <table>
            <thead><tr><th>Rank</th><th>Manager</th><th>Matches</th><th>Total Points</th></tr></thead>
            <tbody>
                @foreach ($overall as $idx => $row)
                    <tr @class(['rank-1' => $idx === 0])>
                        <td><span @class(['rank-pill', 'r1' => $idx === 0, 'r2' => $idx === 1, 'r3' => $idx === 2])>#{{ $idx + 1 }}</span></td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['matches'] }}</td>
                        <td><strong>{{ \App\Services\FantasyPointsCalculator::format($row['points']) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
