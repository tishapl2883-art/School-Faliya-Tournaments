@extends('layouts.app')
@section('title', 'Matches')
@section('content')
<h1 class="title">Matches</h1>
@foreach ($groups as $label => $list)
    @if ($list->count())
        <h2 class="sub">{{ $label }}</h2>
        <div class="grid">@foreach ($list as $m)@include('partials.match-card', ['m' => $m])@endforeach</div>
    @endif
@endforeach
@if (collect($groups)->every->isEmpty())<div class="card empty">The schedule will be announced soon.</div>@endif
@endsection
