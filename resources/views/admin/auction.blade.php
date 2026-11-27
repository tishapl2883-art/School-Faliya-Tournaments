@extends('layouts.app')
@section('title', 'Auction Control')
@section('content')
<h1 class="title">Auction Control</h1>
@if ($player)
    @php $top = $bids->first(); $next = \App\Services\AuctionRules::nextBid($top?->amount, (int) $site['base_price'], (int) $site['bid_increment']); @endphp
    <section class="card stage">
        <img src="{{ $player->photoUrl() }}" alt="">
        <div>
            <h2>{{ $player->name }}</h2><p>{{ $player->role }} &middot; {{ $player->village }}</p>
            <div class="bid"><small>{{ $top ? 'Current bid' : 'Base price' }}</small><strong>{{ number_format($top?->amount ?? $next) }}</strong><span>{{ $top?->team->name ?? 'No bid yet' }}</span></div>
        </div>
    </section>
    <form class="card" method="post" action="{{ route('admin.auction.bid') }}" data-once>
        @csrf<input type="hidden" name="player_id" value="{{ $player->id }}">
        <label>Custom amount (leave empty to bid {{ number_format($next) }})<input type="number" name="amount" min="1"></label>
        <p>Tap a team to place its bid:</p>
        <div class="actions">
            @foreach ($teams as $t)<button class="btn" name="team_id" value="{{ $t->id }}" @disabled($top?->team_id === $t->id)>{{ $t->name }}<small>left {{ number_format($t->remaining()) }}</small></button>@endforeach
        </div>
    </form>
    <div class="actions">
        @foreach (['sold' => ['SOLD', 'gold'], 'unsold' => ['Unsold', 'red']] as $action => [$label, $class])
            <form method="post" action="{{ route('admin.auction.'.$action) }}" data-confirm="Confirm: {{ $label }}?">@csrf<input type="hidden" name="player_id" value="{{ $player->id }}"><button class="btn {{ $class }}">{{ $label }}</button></form>
        @endforeach
        <form method="post" action="{{ route('admin.auction.undo') }}">@csrf<button class="btn">Undo last bid</button></form>
    </div>
    @if ($bids->count())<div class="card"><h3>Bid history</h3><table>@foreach ($bids as $b)<tr><td>{{ $b->team->name }}</td><td>{{ number_format($b->amount) }}</td></tr>@endforeach</table></div>@endif
@else
    <form class="card form" method="post" action="{{ route('admin.auction.start') }}" data-once>
        @csrf
        <label class="full">Choose player (or leave empty for a random waiting player)
            <select name="player_id"><option value="">Random player</option>
                @foreach ($queue as $p)<option value="{{ $p->id }}">{{ $p->name }} - {{ $p->role }}{{ $p->auction_status === 'unsold' ? ' (unsold)' : '' }}</option>@endforeach
            </select></label>
        <button class="btn gold full">Start bidding</button>
    </form>
@endif
<h2 class="title">Sold &amp; Assigned Players</h2>
<div class="card scroll"><table>
    <tr><th>Player</th><th>Team</th><th>Points</th><th></th></tr>
    @forelse ($sold as $p)
        <tr><td>{{ $p->name }}</td><td>{{ $p->team?->name }}</td><td>{{ number_format((int) $p->sold_price) }}</td>
            <td><form method="post" action="{{ route('admin.auction.revert', $p) }}" data-confirm="Cancel this sale?">@csrf<button class="btn small red">Cancel sale</button></form></td></tr>
    @empty
        <tr><td colspan="4">Nothing sold yet.</td></tr>
    @endforelse
</table></div>
@endsection
