<?php $__env->startSection('title', 'Live Score'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Live Score</h1>
<div data-refresh="<?php echo e(route('live.data')); ?>" data-every="4000"><?php echo $__env->make('partials.live-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/live.blade.php ENDPATH**/ ?>