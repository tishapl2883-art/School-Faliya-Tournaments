<?php $__env->startSection('title', 'Auction'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Live Auction</h1>
<div data-refresh="<?php echo e(route('auction.data')); ?>" data-every="3000"><?php echo $__env->make('partials.auction-board', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/auction.blade.php ENDPATH**/ ?>