@php
    $logo = ! empty($site['logo']) ? asset('storage/'.$site['logo']) : asset('images/logo.svg');
    $isAdmin = request()->routeIs('admin.*');
    $nav = $isAdmin
        ? ['admin.dashboard' => 'Dashboard', 'admin.players' => 'Players', 'admin.teams' => 'Teams', 'admin.auction' => 'Auction', 'admin.matches' => 'Matches & Score', 'admin.settings' => 'Settings', 'home' => 'View Website']
        : ['home' => 'Home', 'register' => 'Registration', 'players' => 'Players', 'teams' => 'Teams', 'auction' => 'Auction', 'live' => 'Live Score', 'matches' => 'Matches', 'points' => 'Points Table', 'cric11.index' => 'Cric11', 'about' => 'About', 'contact' => 'Contact'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1f3a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PCT">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <title>@yield('title', 'Home') | {{ $site['title'] }}</title>
    <meta property="og:title" content="{{ $site['title'] }}">
    <meta property="og:description" content="{{ $site['venue'] }} | Organised by {{ $site['organizer'] }}">
    <meta property="og:image" content="{{ $logo }}">
    <link rel="icon" href="{{ $logo }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="topbar">
    <div class="wrap bar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ $logo }}" alt="Logo">
            <span><strong>{{ $site['title'] }}</strong><small>{{ $site['tagline'] }}</small></span>
        </a>
        <button class="menu-btn" type="button" aria-label="Menu" data-menu>&#9776;</button>
    </div>
    <nav class="nav" id="nav">
        <div class="wrap">
            @foreach ($nav as $name => $label)
                @if (in_array($name, ['about', 'contact']))
                    <a href="{{ route('page', $name) }}" @class(['active' => request()->is($name)])>{{ $label }}</a>
                @else
                    <a href="{{ route($name) }}" @class(['active' => request()->routeIs($name)])>{{ $label }}</a>
                @endif
            @endforeach
            @if ($isAdmin)
                <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="linklike">Logout</button></form>
            @endif
        </div>
    </nav>
</header>

<main class="wrap">
    <div class="install" id="install" hidden>
        <img src="{{ asset('images/icon-192.png') }}" alt="">
        <span><strong>Get the app</strong><small id="install-help">Add it to your phone home screen.</small></span>
        <button type="button" class="btn gold small" id="install-btn">Install</button>
        <button type="button" class="close" id="install-close" aria-label="Close">&times;</button>
    </div>
    @if (session('success'))
        <div class="alert ok">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert bad">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif
    @yield('content')
</main>

<footer class="footer">
    <div class="wrap">
        <h3>{{ $site['title'] }}</h3>
        <p>Organised by {{ $site['organizer'] }} &middot; {{ $site['venue'] }}</p>
        <p class="links">
            <a href="{{ route('page', 'about') }}">About</a>
            <a href="{{ route('page', 'contact') }}">Contact Us</a>
            <a href="{{ route('page', 'terms') }}">Terms &amp; Conditions</a>
            <a href="{{ route('page', 'privacy') }}">Privacy Policy</a>
            <a href="{{ route('page', 'refunds') }}">Cancellations &amp; Refunds</a>
            <a href="{{ route('login') }}">Admin</a>
        </p>
        <p class="copy">&copy; {{ date('Y') }} {{ $site['brand'] }}. All rights reserved.</p>
    </div>
</footer>
@php
    $tabs = $isAdmin
        ? ['admin.dashboard' => ['🏠', 'Home'], 'admin.players' => ['🧢', 'Players'], 'admin.auction' => ['🔨', 'Auction'], 'admin.matches' => ['🏏', 'Scoring']]
        : ['home' => ['🏠', 'Home'], 'matches' => ['📅', 'Matches'], 'live' => ['🔴', 'Live'], 'cric11.index' => ['⚡', 'Cric11'], 'register' => ['📝', 'Register']];
    // Scoring screen belongs to the Scoring tab.
    $current = request()->routeIs('admin.score') ? 'admin.matches' : (request()->routeIs('match') ? 'matches' : (request()->routeIs('cric11.*') ? 'cric11.index' : null));
@endphp
<nav class="tabbar" aria-label="App menu">
    @foreach ($tabs as $name => [$icon, $label])
        <a href="{{ route($name) }}" @class(['active' => request()->routeIs($name) || $current === $name])><i aria-hidden="true">{{ $icon }}</i>{{ $label }}</a>
    @endforeach
</nav>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
