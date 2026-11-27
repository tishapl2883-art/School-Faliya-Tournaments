@forelse ($cards as $card)
    @include('partials.scorecard', $card)
@empty
    <div class="card empty">No match is live right now. This page updates by itself when a match starts.</div>
@endforelse
