<?php $__env->startSection('title', 'Teams'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Teams</h1>
<div class="p-grid">
    <?php $__empty_1 = true; $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="p-card">
            <div class="p-card-top">
                <?php if($t->logo): ?>
                    <img class="logo" src="<?php echo e(asset('storage/'.$t->logo)); ?>" alt="<?php echo e($t->name); ?>" loading="lazy">
                <?php else: ?>
                    <div class="p-card-initials"><?php echo e($t->short_name); ?></div>
                <?php endif; ?>
            </div>
            <div class="p-card-body">
                <div class="p-card-name"><?php echo e($t->name); ?></div>
                <div class="p-card-pill"><?php echo e($t->short_name); ?> &middot; <?php echo e($t->players_count); ?> <?php echo e($t->players_count === 1 ? 'Player' : 'Players'); ?></div>
                <div class="p-card-rows">
                    <?php if($t->owner_name): ?><div class="p-card-row"><span>👤</span><span><b>Owner:</b> <?php echo e($t->owner_name); ?></span></div><?php endif; ?>
                    <?php if($t->captain_name): ?><div class="p-card-row"><span>🧢</span><span><b>Captain:</b> <?php echo e($t->captain_name); ?></span></div><?php endif; ?>
                </div>
                <div class="p-card-foot">
                    <a class="btn orange small" href="<?php echo e(route('team', $t)); ?>">View Squad</a>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card empty">Teams will be announced soon.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/teams.blade.php ENDPATH**/ ?>