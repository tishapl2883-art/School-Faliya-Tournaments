@php
    $name = fn ($id) => $names[$id] ?? 'Player';
    $howOut = function ($o) use ($name) {
        if (! $o) { return 'not out'; }
        $b = $name($o['bowler_id']);
        $f = $o['fielder_id'] ? $name($o['fielder_id']) : '';
        return match ($o['type']) {
            'bowled' => "b {$b}",
            'caught' => trim("c {$f}")." b {$b}",
            'lbw' => "lbw b {$b}",
            'stumped' => trim("st {$f}")." b {$b}",
            'hit_wicket' => "hit wicket b {$b}",
            default => 'run out'.($f ? " ({$f})" : ''),
        };
    };
@endphp
<div class="card scorehead">
    <div class="meta"><span>Match {{ $fixture->match_no }} &middot; {{ $fixture->stage }}</span>
        <span @class(['badge', 'live' => $fixture->status === 'live'])>{{ ucfirst($fixture->status) }}</span></div>
    <h2>{{ $fixture->teamA->name }} <em>vs</em> {{ $fixture->teamB->name }}</h2>
    @if ($fixture->tossWinner)<p>{{ $fixture->tossWinner->name }} won the toss and chose to {{ $fixture->toss_decision }}.</p>@endif
    @foreach ($innings as $i)
        <div class="total"><span>{{ $i['model']->battingTeam->name }}</span>
            <strong>{{ $i['score']['runs'] }}/{{ $i['score']['wickets'] }} <small>({{ $i['score']['overs'] }}/{{ $fixture->overs }} ov)</small></strong></div>
    @endforeach
    @if ($chase)<p class="chase">{{ $chase }}</p>@endif
    @if ($fixture->result_text)<p class="result">{{ $fixture->result_text }}</p>@endif
    @if ($fixture->status === 'upcoming')<p>Starts {{ $fixture->starts_at->format('d M Y, h:i A') }}</p>@endif
</div>

@foreach (array_reverse($innings) as $i)
    @php $s = $i['score']; @endphp
    <section class="card">
        <h3>{{ $i['model']->battingTeam->name }} innings &middot; Run rate {{ $s['run_rate'] }}</h3>
        @if (! $i['model']->completed && count($s['timeline']))
            <div class="over"><span>This over:</span>
                @foreach ($s['timeline'] as $ball)<b @class(['wkt' => $ball === 'W' || str_ends_with($ball, '+W'), 'four' => $ball === '4', 'six' => $ball === '6'])>{{ $ball }}</b>@endforeach
            </div>
        @endif
        @if (! $i['model']->completed && $s['free_hit'])<p class="chase">FREE HIT</p>@endif
        <div class="scroll"><table>
            <tr><th>Batter</th><th>R</th><th>B</th><th>4s</th><th>6s</th><th>SR</th></tr>
            @forelse ($s['batters'] as $id => $b)
                <tr>
                    <td><strong>{{ $name($id) }}@if (! $i['model']->completed && $s['striker_id'] === $id) *@endif</strong><small>{{ $howOut($b['out']) }}</small></td>
                    <td><strong>{{ $b['runs'] }}</strong></td><td>{{ $b['balls'] }}</td><td>{{ $b['fours'] }}</td><td>{{ $b['sixes'] }}</td>
                    <td>{{ $b['balls'] ? number_format($b['runs'] * 100 / $b['balls'], 1) : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No ball bowled yet.</td></tr>
            @endforelse
        </table></div>
        <p>Extras: <strong>{{ $s['extras_total'] }}</strong> (Wd {{ $s['extras']['wide'] }}, Nb {{ $s['extras']['noball'] }}, B {{ $s['extras']['bye'] }}, Lb {{ $s['extras']['legbye'] }})</p>
        <div class="scroll"><table>
            <tr><th>Bowler</th><th>O</th><th>M</th><th>R</th><th>W</th><th>Econ</th></tr>
            @foreach ($s['bowlers'] as $id => $b)
                <tr>
                    <td>{{ $name($id) }}</td><td>{{ intdiv($b['balls'], 6) }}.{{ $b['balls'] % 6 }}</td><td>{{ $b['maidens'] }}</td>
                    <td>{{ $b['runs'] }}</td><td><strong>{{ $b['wickets'] }}</strong></td>
                    <td>{{ $b['balls'] ? number_format($b['runs'] * 6 / $b['balls'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </table></div>
        @if ($s['fall'])
            <p><strong>Fall of wickets:</strong>
                @foreach ($s['fall'] as $w){{ $w['wicket'] }}-{{ $w['runs'] }} ({{ $name($w['player_id']) }}, {{ $w['overs'] }} ov)@if (! $loop->last), @endif @endforeach
            </p>
        @endif
    </section>
@endforeach
