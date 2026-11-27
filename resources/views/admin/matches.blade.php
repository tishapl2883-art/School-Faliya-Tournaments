@extends('layouts.app')
@section('title', 'Matches')
@section('content')
<h1 class="title">Matches &amp; Scoring</h1>
@if ($teams->count() < 2)
    <div class="card empty">Add at least two teams first.</div>
@else
<form class="card form" method="post" action="{{ route('admin.matches.save', $editing?->id) }}">
    @csrf
    <h3 class="full">{{ $editing ? 'Edit match' : 'Add match' }}</h3>
    <label>Match number<input type="number" name="match_no" value="{{ old('match_no', $editing?->match_no ?? $nextNo) }}" required></label>
    <label>Stage<select name="stage">@foreach (\App\Models\Fixture::STAGES as $s)<option @selected(old('stage', $editing?->stage) === $s)>{{ $s }}</option>@endforeach</select></label>
    @foreach (['team_a_id' => 'Team A', 'team_b_id' => 'Team B'] as $field => $label)
        <label>{{ $label }}<select name="{{ $field }}" required><option value="">Select</option>@foreach ($teams as $t)<option value="{{ $t->id }}" @selected((int) old($field, $editing?->$field) === $t->id)>{{ $t->name }}</option>@endforeach</select></label>
    @endforeach
    <label>Date and time<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $editing?->starts_at->format('Y-m-d\TH:i') ?? $site['start_date'].'T09:00') }}" required></label>
    <label>Overs<input type="number" name="overs" min="1" max="50" value="{{ old('overs', $editing?->overs ?? 8) }}" required></label>
    <label>Players per side<input type="number" name="players_per_side" min="2" max="11" value="{{ old('players_per_side', $editing?->players_per_side ?? 11) }}" required></label>
    <button class="btn gold full">Save Match</button>
</form>
@endif
<div class="card scroll"><table>
    <tr><th>#</th><th>Match</th><th>When</th><th>Status</th><th>Actions</th></tr>
    @forelse ($matches as $m)
        <tr><td>{{ $m->match_no }}</td><td><strong>{{ $m->teamA->short_name }} vs {{ $m->teamB->short_name }}</strong><small>{{ $m->result_text ?? $m->stage }}</small></td>
            <td>{{ $m->starts_at->format('d M, h:i A') }}</td><td><span @class(['badge', 'live' => $m->status === 'live'])>{{ ucfirst($m->status) }}</span></td>
            <td class="rowact"><a class="btn small gold" href="{{ route('admin.score', $m) }}">Score</a>
                @if ($m->status === 'upcoming')<a class="btn small" href="{{ route('admin.matches', ['edit' => $m->id]) }}">Edit</a>@endif
                <form method="post" action="{{ route('admin.matches.delete', $m) }}" data-confirm="Delete this match and its full score?">@csrf @method('delete')<button class="btn small red">Delete</button></form></td></tr>
    @empty
        <tr><td colspan="5">No matches yet.</td></tr>
    @endforelse
</table></div>
@endsection
