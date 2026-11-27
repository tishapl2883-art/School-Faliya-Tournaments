<div class="chips">
    <span>Sold: <b>{{ $counts['sold'] ?? 0 }}</b></span>
    <span>Waiting: <b>{{ $counts['pending'] ?? 0 }}</b></span>
    <span>Unsold: <b>{{ $counts['unsold'] ?? 0 }}</b></span>
</div>
@if ($player)
    <section class="card stage">
        <img src="{{ $player->photoUrl() }}" alt="{{ $player->name }}">
        <div>
            <span class="badge live">On auction</span>
            <h2>{{ $player->name }}</h2>
            <p>{{ $player->role }} &middot; {{ $player->village }}</p>
            <p>{{ $player->batting_style }} bat &middot; {{ $player->bowling_style }}</p>
            <div class="bid">
                <small>{{ $top ? 'Current bid' : 'Base price' }}</small>
                <strong>{{ number_format($top?->amount ?? (int) $site['base_price']) }}</strong>
                <span>{{ $top ? $top->team->name : 'Waiting for first bid' }}</span>
            </div>
        </div>
    </section>
@else
    <div class="card empty">Waiting for the next player. This page updates by itself.</div>
@endif

<h2 class="title">Team Purse</h2>
<div class="card scroll"><table>
    <tr><th>Team</th><th>Players</th><th>Remaining</th><th>Max bid</th></tr>
    @forelse ($teams as $t)
        <tr><td><a href="{{ route('team', $t) }}">{{ $t->name }}</a></td><td>{{ $t->players_count }}/{{ $site['max_squad'] }}</td>
            <td>{{ number_format($t->balance) }}</td><td>{{ $t->players_count >= (int) $site['max_squad'] ? 'Full' : number_format($t->max_bid) }}</td></tr>
    @empty
        <tr><td colspan="4">Teams will appear here.</td></tr>
    @endforelse
</table></div>

@if ($recent->count())
    <h2 class="title">Recently Sold</h2>
    <div class="card scroll"><table>
        <tr><th>Player</th><th>Team</th><th>Points</th></tr>
        @foreach ($recent as $p)
            <tr><td>{{ $p->name }}</td><td>{{ $p->team?->name }}</td><td>{{ number_format((int) $p->sold_price) }}</td></tr>
        @endforeach
    </table></div>
@endif
