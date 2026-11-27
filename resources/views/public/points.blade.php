@extends('layouts.app')
@section('title', 'Points Table')
@section('content')
<h1 class="title">Points Table</h1>
<div class="card scroll"><table>
    <tr><th>#</th><th>Team</th><th>P</th><th>W</th><th>L</th><th>T/NR</th><th>Pts</th><th>NRR</th></tr>
    @forelse ($rows as $r)
        <tr><td>{{ $loop->iteration }}</td><td><a href="{{ route('team', $r['team']) }}">{{ $r['team']->name }}</a></td>
            <td>{{ $r['played'] }}</td><td>{{ $r['won'] }}</td><td>{{ $r['lost'] }}</td><td>{{ $r['tied'] }}</td>
            <td><strong>{{ $r['points'] }}</strong></td><td>{{ sprintf('%+.3f', $r['nrr']) }}</td></tr>
    @empty
        <tr><td colspan="8">Teams will appear here.</td></tr>
    @endforelse
</table></div>
<div class="grid two">
    @foreach (['runs' => 'Top Run Scorers', 'wickets' => 'Top Wicket Takers'] as $key => $heading)
        <section class="card"><h3>{{ $heading }}</h3>
            <table>
                @forelse ($leaders[$key] as $row)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $row['player']->name }}<small>{{ $row['player']->team?->name }}</small></td><td><strong>{{ $row['value'] }}</strong></td></tr>
                @empty
                    <tr><td>No data yet.</td></tr>
                @endforelse
            </table>
        </section>
    @endforeach
</div>
@endsection
