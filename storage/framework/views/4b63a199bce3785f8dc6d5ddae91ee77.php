<?php $__env->startSection('title', 'Players'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Registered Players (<?php echo e($players->total()); ?>)</h1>
<form class="filters">
    <input name="q" value="<?php echo e($search); ?>" placeholder="Search Player Name or Village...">
    <select name="role"><option value="">All roles</option>
        <?php $__currentLoopData = \App\Models\Player::ROLES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if($role === $r): echo 'selected'; endif; ?>><?php echo e($r); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button class="btn orange" style="padding:10px 18px">Search</button>
</form>

<div class="p-grid">
    <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="p-card">
            <div class="p-card-top">
                <img src="<?php echo e($p->photoUrl()); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
            </div>
            <div class="p-card-body">
                <div class="p-card-name"><?php echo e($p->name); ?></div>
                <div class="p-card-pill"><?php echo e($p->role); ?><?php if($p->team): ?> &middot; <?php echo e($p->team->short_name); ?><?php endif; ?></div>
                <div class="p-card-rows">
                    <div class="p-card-row"><span>📍</span><span><b>Village:</b> <?php echo e($p->village); ?></span></div>
                    <div class="p-card-row"><span>🏏</span><span><b>Batting:</b> <?php echo e($p->batting_style); ?></span></div>
                </div>
                <details class="p-more">
                    <summary class="btn orange small">View More</summary>
                    <div class="p-more-box">
                        <div><b>Bowling:</b> <?php echo e($p->bowling_style); ?></div>
                        <?php if($p->jersey_name || $p->jersey_number !== null): ?>
                            <div><b>Jersey:</b> <?php echo e($p->jersey_name ?: '-'); ?> <?php echo e($p->jersey_number !== null ? '#'.$p->jersey_number : ''); ?></div>
                        <?php endif; ?>
                        <div><b>Team:</b> <?php echo e($p->team?->name ?? 'Unassigned'); ?></div>
                    </div>
                </details>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card empty">No players yet.</div>
    <?php endif; ?>
</div>
<?php echo e($players->links('partials.pagination')); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/players.blade.php ENDPATH**/ ?>