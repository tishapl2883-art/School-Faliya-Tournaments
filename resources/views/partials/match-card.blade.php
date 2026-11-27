<a class="card match" href="{{ route('match', $m) }}">
    <div class="meta"><span>Match {{ $m->match_no }} &middot; {{ $m->stage }}</span>
        <span @class(['badge', 'live' => $m->status === 'live'])>{{ ucfirst($m->status) }}</span></div>
    <div class="versus"><strong>{{ $m->teamA->name }}</strong><em>vs</em><strong>{{ $m->teamB->name }}</strong></div>
    <div class="meta"><span>{{ $m->starts_at->format('D, d M Y h:i A') }}</span><span>{{ $m->overs }} overs</span></div>
    @if ($m->result_text)<p class="result">{{ $m->result_text }}</p>@endif
</a>
