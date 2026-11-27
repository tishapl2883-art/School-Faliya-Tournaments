<?php $__env->startSection('title', 'Settings'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Settings</h1>
<form class="card form" method="post" action="<?php echo e(route('admin.settings.save')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php $__currentLoopData = ['title' => ['Main title', 'text'], 'brand' => ['Brand', 'text'], 'tagline' => ['Line above the title', 'text'], 'organizer' => ['Organiser', 'text'], 'venue' => ['Venue', 'text'], 'start_date' => ['Start date', 'date'], 'end_date' => ['End date', 'date'], 'total_teams' => ['Total teams', 'number'], 'registration_fee' => ['Registration fee (0 = free)', 'number'], 'upi_id' => ['UPI ID', 'text'], 'contact_phone' => ['Contact phone', 'text'], 'team_purse' => ['Default team purse (points)', 'number'], 'base_price' => ['Auction base price', 'number'], 'bid_increment' => ['Bid increment', 'number'], 'min_squad' => ['Minimum squad', 'number'], 'max_squad' => ['Maximum squad', 'number']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $type]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label><?php echo e($label); ?><input type="<?php echo e($type); ?>" name="<?php echo e($key); ?>" value="<?php echo e(old($key, $site[$key])); ?>"></label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <label>Registration
        <select name="registration_open">
            <option value="1" <?php if(old('registration_open', $site['registration_open']) === '1'): echo 'selected'; endif; ?>>Open</option>
            <option value="0" <?php if(old('registration_open', $site['registration_open']) === '0'): echo 'selected'; endif; ?>>Closed</option>
        </select>
    </label>
    <label class="full">About text<textarea name="about" rows="4"><?php echo e(old('about', $site['about'])); ?></textarea></label>
    <label class="full">Replace logo (optional)<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <button class="btn gold full">Save Settings</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/settings.blade.php ENDPATH**/ ?>