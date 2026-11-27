@extends('layouts.app')
@section('title', 'Edit Player')
@section('content')
<h1 class="title">Edit Player</h1>
<form class="card form" method="post" action="{{ route('admin.players.update', $player) }}">
    @csrf @method('put')
    <label>Name<input name="name" value="{{ old('name', $player->name) }}" required></label>
    <label>Mobile<input name="mobile" value="{{ old('mobile', $player->mobile) }}" required></label>
    <label>Village<input name="village" value="{{ old('village', $player->village) }}" required></label>
    @foreach (['role' => ['Role', \App\Models\Player::ROLES], 'batting_style' => ['Batting', \App\Models\Player::BATTING], 'bowling_style' => ['Bowling', \App\Models\Player::BOWLING], 'tshirt_size' => ['T-shirt', \App\Models\Player::SIZES]] as $field => [$label, $options])
        <label>{{ $label }}<select name="{{ $field }}">@foreach ($options as $o)<option @selected(old($field, $player->$field) === $o)>{{ $o }}</option>@endforeach</select></label>
    @endforeach
    <label>Status<select name="status">@foreach (['pending', 'approved', 'rejected'] as $s)<option value="{{ $s }}" @selected(old('status', $player->status) === $s)>{{ ucfirst($s) }}</option>@endforeach</select></label>
    <label>Team (for captains or retained players)<select name="team_id"><option value="">No team</option>@foreach ($teams as $t)<option value="{{ $t->id }}" @selected((int) old('team_id', $player->team_id) === $t->id)>{{ $t->name }}</option>@endforeach</select></label>
    <label>Price (points)<input type="number" min="0" name="sold_price" value="{{ old('sold_price', $player->sold_price) }}"></label>
    <label>Cric11 Credits (5.0 - 15.0)<input type="number" step="0.5" min="5" max="15" name="fantasy_credits" value="{{ old('fantasy_credits', $player->fantasy_credits) }}" placeholder="Auto ({{ $player->fantasyCredits() }})"></label>
    <button class="btn gold full">Save Player</button>
</form>
@endsection
