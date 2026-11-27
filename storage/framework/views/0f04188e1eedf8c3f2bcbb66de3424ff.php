<?php
    $logo = ! empty($site['logo']) ? asset('storage/'.$site['logo']) : asset('images/logo.svg');
    $isAdmin = request()->routeIs('admin.*');
    $nav = $isAdmin
        ? ['admin.dashboard' => 'Dashboard', 'admin.players' => 'Players', 'admin.teams' => 'Teams', 'admin.auction' => 'Auction', 'admin.matches' => 'Matches & Score', 'admin.settings' => 'Settings', 'home' => 'View Website']
        : ['home' => 'Home', 'register' => 'Registration', 'players' => 'Players', 'teams' => 'Teams', 'auction' => 'Auction', 'live' => 'Live Score', 'matches' => 'Matches', 'points' => 'Points Table', 'cric11.index' => 'Cric11', 'about' => 'About', 'contact' => 'Contact'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0b1f3a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PCT">
    <link rel="manifest" href="<?php echo e(asset('manifest.webmanifest')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/apple-touch-icon.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Home'); ?> | <?php echo e($site['title']); ?></title>
    <meta property="og:title" content="<?php echo e($site['title']); ?>">
    <meta property="og:description" content="<?php echo e($site['venue']); ?> | Organised by <?php echo e($site['organizer']); ?>">
    <meta property="og:image" content="<?php echo e($logo); ?>">
    <link rel="icon" href="<?php echo e($logo); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
</head>
<body>
<header class="topbar">
    <div class="wrap bar">
        <a class="brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e($logo); ?>" alt="Logo">
            <span><strong><?php echo e($site['title']); ?></strong><small><?php echo e($site['tagline']); ?></small></span>
        </a>
        <button class="menu-btn" type="button" aria-label="Menu" data-menu>&#9776;</button>
    </div>
    <nav class="nav" id="nav">
        <div class="wrap">
            <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(in_array($name, ['about', 'contact'])): ?>
                    <a href="<?php echo e(route('page', $name)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => request()->is($name)]); ?>"><?php echo e($label); ?></a>
                <?php else: ?>
                    <a href="<?php echo e(route($name)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => request()->routeIs($name)]); ?>"><?php echo e($label); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($isAdmin): ?>
                <form method="post" action="<?php echo e(route('admin.logout')); ?>"><?php echo csrf_field(); ?><button class="linklike">Logout</button></form>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="wrap">
    <div class="install" id="install" hidden>
        <img src="<?php echo e(asset('images/icon-192.png')); ?>" alt="">
        <span><strong>Get the app</strong><small id="install-help">Add it to your phone home screen.</small></span>
        <button type="button" class="btn gold small" id="install-btn">Install</button>
        <button type="button" class="close" id="install-close" aria-label="Close">&times;</button>
    </div>
    <?php if(session('success')): ?>
        <div class="alert ok"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert bad">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($error); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="footer">
    <div class="wrap">
        <h3><?php echo e($site['title']); ?></h3>
        <p>Organised by <?php echo e($site['organizer']); ?> &middot; <?php echo e($site['venue']); ?></p>
        <p class="links">
            <a href="<?php echo e(route('page', 'about')); ?>">About</a>
            <a href="<?php echo e(route('page', 'contact')); ?>">Contact Us</a>
            <a href="<?php echo e(route('page', 'terms')); ?>">Terms &amp; Conditions</a>
            <a href="<?php echo e(route('page', 'privacy')); ?>">Privacy Policy</a>
            <a href="<?php echo e(route('page', 'refunds')); ?>">Cancellations &amp; Refunds</a>
            <a href="<?php echo e(route('login')); ?>">Admin</a>
        </p>
        <p class="copy">&copy; <?php echo e(date('Y')); ?> <?php echo e($site['brand']); ?>. All rights reserved.</p>
    </div>
</footer>
<?php
    $tabs = $isAdmin
        ? ['admin.dashboard' => ['🏠', 'Home'], 'admin.players' => ['🧢', 'Players'], 'admin.auction' => ['🔨', 'Auction'], 'admin.matches' => ['🏏', 'Scoring']]
        : ['home' => ['🏠', 'Home'], 'matches' => ['📅', 'Matches'], 'live' => ['🔴', 'Live'], 'cric11.index' => ['⚡', 'Cric11'], 'register' => ['📝', 'Register']];
    // Scoring screen belongs to the Scoring tab.
    $current = request()->routeIs('admin.score') ? 'admin.matches' : (request()->routeIs('match') ? 'matches' : (request()->routeIs('cric11.*') ? 'cric11.index' : null));
?>
<nav class="tabbar" aria-label="App menu">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => [$icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($name)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active' => request()->routeIs($name) || $current === $name]); ?>"><i aria-hidden="true"><?php echo e($icon); ?></i><?php echo e($label); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<script src="<?php echo e(asset('js/app.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/layouts/app.blade.php ENDPATH**/ ?>