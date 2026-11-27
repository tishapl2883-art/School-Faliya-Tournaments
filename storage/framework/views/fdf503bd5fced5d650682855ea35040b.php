<?php $__env->startSection('content'); ?>
<?php
    $start = \Carbon\Carbon::parse($site['start_date']);
    $end = \Carbon\Carbon::parse($site['end_date']);
    if ($start->format('Ym') === $end->format('Ym') && $start->diffInDays($end) < 7) {
        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) { $days[] = $d->format('d'); }
        $dates = implode(' / ', $days).' '.$start->format('F Y');
    } else {
        $dates = $start->format('d M').' - '.$end->format('d M Y');
    }
?>
<section class="hero">
    <img src="<?php echo e(! empty($site['logo']) ? asset('storage/'.$site['logo']) : asset('images/logo.svg')); ?>" alt="">
    <p class="kicker"><?php echo e($site['tagline']); ?></p>
    <h1><?php echo e($site['title']); ?></h1>
    <p>Organised by <strong><?php echo e($site['organizer']); ?></strong></p>
    <div class="facts">
        <div><small>Date</small><b><?php echo e($dates); ?></b></div>
        <div><small>Venue</small><b><?php echo e($site['venue']); ?></b></div>
        <div><small>Total Teams</small><b><?php echo e($site['total_teams']); ?></b></div>
    </div>
    <div class="countdown" data-countdown="<?php echo e($start->format('Y-m-d')); ?>T00:00:00+05:30"></div>
    <?php if($site['registration_open'] === '1'): ?>
        <a class="btn gold" href="<?php echo e(route('register')); ?>">Register as Player</a>
    <?php else: ?>
        <span class="badge">Registration closed</span>
    <?php endif; ?>
</section>

<div class="tiles">
    <?php $__currentLoopData = ['register' => ['📝', 'Registration'], 'players' => ['🧢', 'Players'], 'teams' => ['🛡️', 'Teams'], 'auction' => ['🔨', 'Auction'], 'live' => ['🔴', 'Live Score'], 'matches' => ['📅', 'Matches'], 'points' => ['🏆', 'Points Table'], 'cric11.index' => ['⚡', 'Cric11 Fantasy']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r => [$icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($r)); ?>"><i><?php echo e($icon); ?></i><span><?php echo e($label); ?></span></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="stats">
    <div><strong><?php echo e($playerCount); ?></strong><span>Total Player Registration</span></div>
    <div><strong><?php echo e($teams->count()); ?> / <?php echo e($site['total_teams']); ?></strong><span>Teams Added</span></div>
</div>

<?php if($live->count()): ?>
    <h2 class="title">Live Now</h2>
    <div class="grid"><?php $__currentLoopData = $live; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo $__env->make('partials.match-card', ['m' => $m], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
<?php endif; ?>

<?php if($upcoming->count()): ?>
    <h2 class="title">Upcoming Matches</h2>
    <div class="grid"><?php $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo $__env->make('partials.match-card', ['m' => $m], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/home.blade.php ENDPATH**/ ?>