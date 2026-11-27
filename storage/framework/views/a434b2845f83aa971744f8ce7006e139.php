<?php $__env->startSection('title', 'Manage Teams'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Teams (<?php echo e($teams->count()); ?> / <?php echo e($site['total_teams']); ?>)</h1>
<?php if($editing || $teams->count() < (int) $site['total_teams']): ?>
<form class="card form" method="post" action="<?php echo e(route('admin.teams.save', $editing?->id)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <h3 class="full"><?php echo e($editing ? 'Edit team' : 'Add team'); ?></h3>
    <label>Team name<input name="name" value="<?php echo e(old('name', $editing?->name)); ?>" required></label>
    <label>Short name (max 6)<input name="short_name" maxlength="6" value="<?php echo e(old('short_name', $editing?->short_name)); ?>" required></label>
    <label>Owner<input name="owner_name" value="<?php echo e(old('owner_name', $editing?->owner_name)); ?>"></label>
    <label>Captain<input name="captain_name" value="<?php echo e(old('captain_name', $editing?->captain_name)); ?>"></label>
    <label>Purse (points)<input type="number" name="purse" value="<?php echo e(old('purse', $editing?->purse ?? $site['team_purse'])); ?>" required></label>
    <label>Logo<input type="file" name="logo" accept="image/jpeg,image/png,image/webp"></label>
    <button class="btn gold full">Save Team</button>
</form>
<?php endif; ?>
<div class="card scroll desktop-only"><table>
    <tr><th>Team</th><th>Players</th><th>Purse</th><th>Left</th><th>Actions</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><strong><?php echo e($t->name); ?></strong><small><?php echo e($t->short_name); ?></small></td><td><?php echo e($t->players_count); ?></td><td><?php echo e(number_format($t->purse)); ?></td><td><?php echo e(number_format($t->remaining())); ?></td>
            <td class="rowact">
                <a class="btn icon" href="<?php echo e(route('admin.teams', ['edit' => $t->id])); ?>" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                <form method="post" action="<?php echo e(route('admin.teams.delete', $t)); ?>" data-confirm="Delete this team?"><?php echo csrf_field(); ?> <?php echo method_field('delete'); ?><button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
            </td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="5">No teams yet. Add your first team above.</td></tr>
    <?php endif; ?>
</table></div>

<div class="mobile-only">
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
                        <div class="p-card-row"><span>💰</span><span><b>Purse:</b> <?php echo e(number_format($t->purse)); ?> &middot; <b>Left:</b> <?php echo e(number_format($t->remaining())); ?></span></div>
                    </div>
                    <div class="p-card-foot">
                        <a class="btn icon" href="<?php echo e(route('admin.teams', ['edit' => $t->id])); ?>" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                        <form method="post" action="<?php echo e(route('admin.teams.delete', $t)); ?>" data-confirm="Delete this team?"><?php echo csrf_field(); ?> <?php echo method_field('delete'); ?><button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card empty">No teams yet. Add your first team above.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/teams.blade.php ENDPATH**/ ?>