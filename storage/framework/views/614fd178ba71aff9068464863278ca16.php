<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Admin Dashboard</h1>
<div class="stats">
    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><strong><?php echo e($value); ?></strong><span><?php echo e($label); ?></span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="actions">
    <a class="btn" href="<?php echo e(route('admin.players', ['status' => 'pending'])); ?>">Approve players</a>
    <a class="btn" href="<?php echo e(route('admin.teams')); ?>">Manage teams</a>
    <a class="btn" href="<?php echo e(route('admin.auction')); ?>">Run auction</a>
    <a class="btn" href="<?php echo e(route('admin.matches')); ?>">Matches &amp; scoring</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>