@if (count($card['innings']))
    <div class="card">
        @foreach ($card['innings'] as $in)
            <div class="total" @if ($loop->first) style="border-top:0" @endif>
                <span><strong>{{ $in['model']->battingTeam->name }}</strong></span>
                <strong>{{ $in['score']['runs'] }}/{{ $in['score']['wickets'] }} <small>({{ $in['score']['overs'] }})</small></strong>
            </div>
        @endforeach
        @if ($card['chase'])<p class="chase">{{ $card['chase'] }}</p>@endif
    </div>
@endif

<h2 class="title">Cric11 Leaderboard</h2>
@if (count($leaderboard))
    <div class="card scroll">
        <table>
            <thead><tr><th>Rank</th><th>Team</th><th>C / VC</th><th>Points</th><th></th></tr></thead>
            <tbody>
                @foreach ($leaderboard as $row)
                    @php
                        $e = $row['entry'];
                        $isMine = (isset($ownedTokens[$e->id]) && hash_equals((string) $e->edit_token, (string) $ownedTokens[$e->id]))
                            || ($myMobile && $e->participant_mobile === $myMobile);
                        $canOpen = $fixture->status !== 'upcoming' || (isset($ownedTokens[$e->id]) && hash_equals((string) $e->edit_token, (string) $ownedTokens[$e->id]));
                    @endphp
                    <tr @class(['rank-1' => $row['rank'] === 1])>
                        <td><span @class(['rank-pill', 'r1' => $row['rank'] === 1, 'r2' => $row['rank'] === 2, 'r3' => $row['rank'] === 3])>#{{ $row['rank'] }}</span></td>
                        <td>
                            <strong>{{ $e->team_name }}</strong>
                            @if ($isMine)<span class="badge approved">You</span>@endif
                            <small>{{ $e->participant_name }}</small>
                        </td>
                        <td>
                            <small>C: <strong>{{ $e->captain->name }}</strong></small>
                            <small>VC: {{ $e->viceCaptain->name }}</small>
                        </td>
                        <td><strong>{{ \App\Services\FantasyPointsCalculator::format($row['points']) }}</strong></td>
                        <td>
                            @if ($canOpen)
                                <a class="btn small" href="{{ route('cric11.entry', $e) }}">Pitch</a>
                            @else
                                <small>Locked</small>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="card empty">
        No Cric11 teams joined yet.
        @if ($fixture->status === 'upcoming')
            <div style="margin-top:10px"><a class="btn gold" href="{{ route('cric11.create', $fixture) }}">Create the First Team</a></div>
        @endif
    </div>
@endif

@if ($dreamTeam)
    <h2 class="title">Dream Team (Best XI)</h2>
    <div class="card">
        <div class="meta" style="margin-bottom:8px">
            <span>Optimal XI Score: <strong>{{ \App\Services\FantasyPointsCalculator::format($dreamTeam['total_points']) }} pts</strong></span>
            <span>{{ number_format($dreamTeam['total_credits'], 1) }} Cr</span>
        </div>
        <div class="chips" style="margin-bottom:0">
            @foreach ($pool->whereIn('id', $dreamTeam['player_ids'])->sortByDesc(fn ($p) => $pointsById[$p->id]['total'] ?? 0) as $dp)
                <span style="border:1px solid var(--line)">
                    <strong>{{ $dp->name }}</strong>
                    @if ($dp->id === $dreamTeam['captain_id']) <b style="color:var(--green)">(C)</b>
                    @elseif ($dp->id === $dreamTeam['vice_captain_id']) <b style="color:var(--navy2)">(VC)</b>
                    @endif
                    &middot; {{ \App\Services\FantasyPointsCalculator::format($pointsById[$dp->id]['total'] ?? 0) }}
                </span>
            @endforeach
        </div>
    </div>
@endif

<h2 class="title">Player Fantasy Points</h2>
<div class="card scroll">
    <table>
        <thead><tr><th>Player</th><th>Team</th><th>Role</th><th>Cr</th><th>Play</th><th>Bat</th><th>Bowl</th><th>Field</th><th>Total</th></tr></thead>
        <tbody>
            @forelse ($playerStats as $ps)
                @php $p = $ps['player']; $pt = $ps['points']; @endphp
                <tr>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->team?->short_name }}</td>
                    <td>{{ $p->roleCode() }}</td>
                    <td>{{ number_format($p->fantasyCredits(), 1) }}</td>
                    <td>{{ $pt['playing'] }}</td>
                    <td>{{ $pt['batting'] }}</td>
                    <td>{{ $pt['bowling'] }}</td>
                    <td>{{ $pt['fielding'] }}</td>
                    <td><strong>{{ \App\Services\FantasyPointsCalculator::format($pt['total']) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="9" class="empty">No approved players in these teams yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
