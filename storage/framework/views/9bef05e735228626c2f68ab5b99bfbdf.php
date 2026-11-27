<?php $__env->startSection('title', $team->name); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title"><?php echo e($team->name); ?></h1>
<div class="chips">
    <?php if($team->owner_name): ?><span>Owner: <b><?php echo e($team->owner_name); ?></b></span><?php endif; ?>
    <?php if($team->captain_name): ?><span>Captain: <b><?php echo e($team->captain_name); ?></b></span><?php endif; ?>
    <span>Purse left: <b><?php echo e(number_format($team->remaining())); ?></b></span>
</div>
<div class="p-grid">
    <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="p-card">
            <div class="p-card-top">
                <img src="<?php echo e($p->photoUrl()); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
            </div>
            <div class="p-card-body">
                <div class="p-card-name"><?php echo e($p->name); ?></div>
                <div class="p-card-pill"><?php echo e($p->role); ?></div>
                <div class="p-card-rows">
                    <div class="p-card-row"><span>📍</span><span><b>Village:</b> <?php echo e($p->village); ?></span></div>
                    <div class="p-card-row"><span>💰</span><span><b>Auction Price:</b> <?php echo e(number_format((int) $p->sold_price)); ?> pts</span></div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card empty">No players in this team yet.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/team.blade.php ENDPATH**/ ?>