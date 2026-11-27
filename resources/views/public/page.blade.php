@extends('layouts.app')
@php $titles = ['about' => 'About', 'contact' => 'Contact Us', 'terms' => 'Terms & Conditions', 'privacy' => 'Privacy Policy', 'refunds' => 'Cancellations & Refunds']; @endphp
@section('title', $titles[$page])
@section('content')
<h1 class="title">{{ $titles[$page] }}</h1>
<div class="card prose">
@switch($page)
    @case('about')
        <p>{!! nl2br(e($site['about'])) !!}</p>
        <p><strong>Brand:</strong> {{ $site['brand'] }}<br><strong>Organiser:</strong> {{ $site['organizer'] }}</p>
        @break
    @case('contact')
        <p><strong>Organiser:</strong> {{ $site['organizer'] }}</p>
        <p><strong>Venue:</strong> {{ $site['venue'] }}</p>
        <p><strong>Rajan:</strong> <a href="tel:8460102232">8460102232</a><br><strong>Jaymal:</strong> <a href="tel:8866751546">8866751546</a></p>
        @if ($site['contact_phone'])<p><strong>Phone:</strong> <a href="tel:{{ $site['contact_phone'] }}">{{ $site['contact_phone'] }}</a></p>@endif
        <p><a class="btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($site['venue'].' Parnera') }}">Find venue on map</a></p>
        @break
    @case('terms')
        <ul>
            <li>Players must give correct details. Wrong details can lead to rejection.</li>
            <li>Registration is complete only after the organiser approves it.</li>
            <li>The umpire's and organiser's decisions are final.</li>
            <li>Every player is responsible for their own safety and playing kit.</li>
        </ul>
        @break
    @case('privacy')
        <p>We collect your name, mobile number, date of birth, village, photo and payment details only to run this tournament. Your name, photo, village and playing details are shown on this website. Your mobile number, date of birth and payment details are not shown publicly. Contact the organiser to correct or remove your data.</p>
        @break
    @case('refunds')
        <p>For questions about cancellation or refund of the registration fee, contact the organiser.</p>
        @break
@endswitch
</div>
@endsection
