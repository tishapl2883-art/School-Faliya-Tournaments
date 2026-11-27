<?php $__env->startSection('title', 'Matches'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Matches</h1>
<?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($list->count()): ?>
        <h2 class="sub"><?php echo e($label); ?></h2>
        <div class="grid"><?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo $__env->make('partials.match-card', ['m' => $m], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if(collect($groups)->every->isEmpty()): ?><div class="card empty">The schedule will be announced soon.</div><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/matches.blade.php ENDPATH**/ ?>