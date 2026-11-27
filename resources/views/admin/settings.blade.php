@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<h1 class="title">Settings</h1>
<form class="card form" method="post" action="{{ route('admin.settings.save') }}" enctype="multipart/form-data">
    @csrf
    @foreach (['title' => ['Main title', 'text'], 'brand' => ['Brand', 'text'], 'tagline' => ['Line above the title', 'text'], 'organizer' => ['Organiser', 'text'], 'venue' => ['Venue', 'text'], 'start_date' => ['Start date', 'date'], 'end_date' => ['End date', 'date'], 'total_teams' => ['Total teams', 'number'], 'registration_fee' => ['Registration fee (0 = free)', 'number'], 'upi_id' => ['UPI ID', 'text'], 'contact_phone' => ['Contact phone', 'text'], 'team_purse' => ['Default team purse (points)', 'number'], 'base_price' => ['Auction base price', 'number'], 'bid_increment' => ['Bid increment', 'number'], 'min_squad' => ['Minimum squad', 'number'], 'max_squad' => ['Maximum squad', 'number']] as $key => [$label, $type])
        <label>{{ $label }}<input type="{{ $type }}" name="{{ $key }}" value="{{ old($key, $site[$key]) }}"></label>
    @endforeach
    <label>Registration
        <select name="registration_open">
            <option value="1" @selected(old('registration_open', $site['registration_open']) === '1')>Open</option>
            <option value="0" @selected(old('registration_open', $site['registration_open']) === '0')>Closed</option>
        </select>
    </label>
    <label class="full">About text<textarea name="about" rows="4">{{ old('about', $site['about']) }}</textarea></label>
    <label class="full">Replace logo (optional)<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <label class="full">Payment QR image (optional)<input type="file" name="payment_qr" accept="image/jpeg,image/png,image/webp"></label>
    <button class="btn gold full">Save Settings</button>
</form>
@endsection
