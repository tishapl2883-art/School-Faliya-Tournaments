@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 class="title">Admin Dashboard</h1>
<div class="stats">
    @foreach ($stats as $label => $value)<div><strong>{{ $value }}</strong><span>{{ $label }}</span></div>@endforeach
</div>
<div class="actions">
    <a class="btn" href="{{ route('admin.players', ['status' => 'pending']) }}">Approve players</a>
    <a class="btn" href="{{ route('admin.teams') }}">Manage teams</a>
    <a class="btn" href="{{ route('admin.auction') }}">Run auction</a>
    <a class="btn" href="{{ route('admin.matches') }}">Matches &amp; scoring</a>
</div>
@endsection
