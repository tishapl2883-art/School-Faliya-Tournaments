@extends('layouts.app')
@section('title', 'Scoring')
@section('content')
<h1 class="title">Scoring: Match {{ $fixture->match_no }}</h1>

@if ($fixture->status === 'upcoming')
    <form class="card form" method="post" action="{{ route('admin.score.toss', $fixture) }}" data-once>
        @csrf
        <label>Toss won by<select name="toss_winner_id" required>
            <option value="{{ $fixture->team_a_id }}">{{ $fixture->teamA->name }}</option>
            <option value="{{ $fixture->team_b_id }}">{{ $fixture->teamB->name }}</option></select></label>
        <label>Chose to<select name="toss_decision"><option value="bat">Bat</option><option value="bowl">Bowl</option></select></label>
        <button class="btn gold full">Start Match</button>
    </form>
@elseif ($current)
    @php $s = $current['score']; $inning = $current['model']; @endphp
    <div class="card scorehead">
        <div class="total"><span>{{ $inning->battingTeam->name }}</span><strong>{{ $s['runs'] }}/{{ $s['wickets'] }} <small>({{ $s['overs'] }}/{{ $fixture->overs }} ov)</small></strong></div>
        @if ($chase)<p class="chase">{{ $chase }}</p>@endif
        <div class="over"><span>This over:</span>@foreach ($s['timeline'] as $b)<b>{{ $b }}</b>@endforeach</div>
        @if ($s['free_hit'])<p class="chase">FREE HIT</p>@endif
    </div>
    <form class="card form" method="post" action="{{ route('admin.score.ball', $fixture) }}" data-once>
        @csrf
        <input type="hidden" name="inning_id" value="{{ $inning->id }}">
        <input type="hidden" name="ball_count" value="{{ $inning->balls()->count() }}">
        @foreach (['striker_id' => ['Striker', $batting], 'non_striker_id' => ['Non striker', $batting], 'bowler_id' => ['Bowler', $bowling]] as $field => [$label, $list])
            <label>{{ $label }}<select name="{{ $field }}" required><option value="">Select {{ strtolower($label) }}</option>
                @foreach ($list as $p)<option value="{{ $p->id }}" @selected((int) old($field, $s[$field]) === $p->id)>{{ $p->name }}</option>@endforeach
            </select></label>
        @endforeach
        <fieldset class="full pick"><legend>Extra</legend>
            @foreach (['' => 'None', 'wide' => 'Wide', 'noball' => 'No ball', 'bye' => 'Bye', 'legbye' => 'Leg bye'] as $v => $l)
                <label><input type="radio" name="extra_type" value="{{ $v }}" @checked($v === '')><span>{{ $l }}</span></label>
            @endforeach
        </fieldset>
        <label>Wicket<select name="wicket_type"><option value="">No wicket</option>@foreach (\App\Services\ScoreCalculator::WICKETS as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></label>
        <label>Who is out (run out only)<select name="out_who"><option value="striker">Striker</option><option value="non_striker">Non striker</option></select></label>
        <label>Fielder<select name="fielder_id"><option value="">None</option>@foreach ($bowling as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>
        <div class="full">
            <p>Tap the runs to save the ball. For a wide, choose only the extra runs run (the 1 penalty is added automatically). For a no ball, choose the runs off the bat.</p>
            <div class="runpad">@foreach (range(0, 7) as $r)<button name="runs" value="{{ $r }}">{{ $r }}</button>@endforeach</div>
        </div>
    </form>
@else
    <div class="card">
        @if ($fixture->status === 'live' && count($innings) === 1)
            <p>First innings is complete. Target: <strong>{{ $innings[0]['score']['runs'] + 1 }}</strong></p>
            <form method="post" action="{{ route('admin.score.second', $fixture) }}" data-once>@csrf<button class="btn gold">Start Second Innings</button></form>
        @else
            <p class="result">{{ $fixture->result_text }}</p>
        @endif
    </div>
@endif

@if (count($innings))
    <div class="actions">
        <form method="post" action="{{ route('admin.score.undo', $fixture) }}" data-confirm="Remove the last ball?">@csrf<button class="btn">Undo last ball</button></form>
        @if ($current)<form method="post" action="{{ route('admin.score.end', $fixture) }}" data-confirm="End this innings now?">@csrf<button class="btn red">End innings</button></form>@endif
        <a class="btn" href="{{ route('match', $fixture) }}" target="_blank">Public scorecard</a>
    </div>
@endif

<details class="card">
    <summary>Set result manually (rain, walkover, super over)</summary>
    <form class="form" method="post" action="{{ route('admin.score.result', $fixture) }}" data-confirm="Save this result and close the match?">
        @csrf
        <label>Result<select name="result_type"><option value="win">Win</option><option value="tie">Tie</option><option value="no_result">No result</option></select></label>
        <label>Winner<select name="winner_id"><option value="">None</option><option value="{{ $fixture->team_a_id }}">{{ $fixture->teamA->name }}</option><option value="{{ $fixture->team_b_id }}">{{ $fixture->teamB->name }}</option></select></label>
        <label class="full">Result text<input name="result_text" maxlength="150" required></label>
        <button class="btn full">Save Result</button>
    </form>
</details>

@if (count($innings))@include('partials.scorecard')@endif
@endsection
