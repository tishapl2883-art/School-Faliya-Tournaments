@extends('layouts.app')
@section('title', $entry->team_name.' | Cric11')
@section('content')
<div class="actions">
    <a class="btn small" href="{{ route('cric11.match', $fixture) }}">&larr; Back to Match Leaderboard</a>
    @if ($canEdit)
        <a class="btn small gold" href="{{ route('cric11.edit', $entry) }}">Edit Team</a>
    @endif
</div>

<div class="card scorehead">
    <div class="meta">
        <span>Match {{ $fixture->match_no }} &middot; {{ $fixture->teamA->name }} vs {{ $fixture->teamB->name }}</span>
        <span @class(['badge', 'live' => $fixture->status === 'live'])>{{ ucfirst($fixture->status) }}</span>
    </div>
    <div class="total" style="border-top:0;padding-bottom:0">
        <div>
            <h1 style="font-size:26px">{{ $entry->team_name }}</h1>
            <small>Manager: {{ $entry->participant_name }} &middot; {{ number_format($entry->total_credits, 1) }} Cr used</small>
        </div>
        <div style="text-align:right">
            <strong>{{ \App\Services\FantasyPointsCalculator::format($totalPoints) }}</strong>
            <small style="display:block">Total Points</small>
        </div>
    </div>
</div>

<div class="pitch" style="margin-bottom:16px">
    @foreach (['WK' => 'Wicket Keepers', 'BAT' => 'Batters', 'AR' => 'All Rounders', 'BOWL' => 'Bowlers'] as $code => $label)
        @if ($byRole[$code]->count())
            <div class="pitch-row">
                <div class="pitch-label">{{ $label }}</div>
                <div class="pitch-players">
                    @foreach ($byRole[$code] as $p)
                        @php
                            $base = $pointsById[$p->id]['total'] ?? 0;
                            $isC = (int) $entry->captain_id === $p->id;
                            $isVC = (int) $entry->vice_captain_id === $p->id;
                            $pts = $base * ($isC ? 2.0 : ($isVC ? 1.5 : 1.0));
                        @endphp
                        <div class="pitch-node">
                            @if ($isC)<span class="cap-badge">C 2x</span>@elseif ($isVC)<span class="cap-badge vc">VC 1.5x</span>@endif
                            <img src="{{ $p->photoUrl() }}" alt="{{ $p->name }}">
                            <span @class(['pitch-tag', 'away' => $p->team_id === $fixture->team_b_id])>{{ $p->name }}</span>
                            <span class="pitch-pts">{{ \App\Services\FantasyPointsCalculator::format($pts) }} pts</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>

<h2 class="title">Points Breakdown</h2>
<div class="card scroll">
    <table>
        <thead><tr><th>Player</th><th>Team</th><th>Role</th><th>Play</th><th>Bat</th><th>Bowl</th><th>Field</th><th>Mult</th><th>Points</th></tr></thead>
        <tbody>
            @foreach ($entry->players as $p)
                @php
                    $pt = $pointsById[$p->id] ?? ['playing' => 0, 'batting' => 0, 'bowling' => 0, 'fielding' => 0, 'total' => 0];
                    $isC = (int) $entry->captain_id === $p->id;
                    $isVC = (int) $entry->vice_captain_id === $p->id;
                    $mult = $isC ? 2.0 : ($isVC ? 1.5 : 1.0);
                    $final = $pt['total'] * $mult;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $p->name }}</strong>
                        @if ($isC)<span class="badge pending">C (2x)</span>@elseif ($isVC)<span class="badge">VC (1.5x)</span>@endif
                    </td>
                    <td>{{ $p->team?->short_name }}</td>
                    <td>{{ $p->roleCode() }}</td>
                    <td>{{ $pt['playing'] }}</td>
                    <td>{{ $pt['batting'] }}</td>
                    <td>{{ $pt['bowling'] }}</td>
                    <td>{{ $pt['fielding'] }}</td>
                    <td>{{ $mult }}x</td>
                    <td><strong>{{ \App\Services\FantasyPointsCalculator::format($final) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
