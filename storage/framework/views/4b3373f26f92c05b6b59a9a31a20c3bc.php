<?php $__env->startSection('title', 'Admin Login'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Admin Login</h1>
<form class="card form narrow" method="post" action="<?php echo e(route('login')); ?>">
    <?php echo csrf_field(); ?>
    <label class="full">Email<input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus></label>
    <label class="full">Password<input type="password" name="password" required></label>
    <label class="full check"><input type="checkbox" name="remember" value="1"> Remember me</label>
    <button class="btn gold full">Login</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/login.blade.php ENDPATH**/ ?>