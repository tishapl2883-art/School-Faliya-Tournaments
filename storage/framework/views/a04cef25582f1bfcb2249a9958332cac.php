<?php $__env->startSection('title', 'Manage Players'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Players (<?php echo e($players->total()); ?>)</h1>
<form class="filters">
    <input name="q" value="<?php echo e($search); ?>" placeholder="Name or mobile">
    <select name="status"><option value="">All</option>
        <?php $__currentLoopData = ['pending', 'approved', 'rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if($status === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button class="btn">Filter</button>
    <a class="btn" href="<?php echo e(route('admin.players.export')); ?>">Download CSV</a>
</form>
<div class="card scroll desktop-only"><table>
    <tr><th>#</th><th>Player</th><th>Mobile</th><th>Payment</th><th>Status</th><th>Team</th><th>Actions</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($p->id); ?></td>
            <td><div class="who"><img src="<?php echo e($p->photoUrl()); ?>" alt=""><span><strong><?php echo e($p->name); ?></strong><small><?php echo e($p->role); ?> &middot; <?php echo e($p->village); ?> &middot; <?php echo e($p->tshirt_size); ?></small></span></div></td>
            <td><?php echo e($p->mobile); ?></td>
            <td><?php echo e($p->payment_ref ?: '-'); ?><?php if($p->payment_proof): ?><small><a target="_blank" href="<?php echo e(route('admin.players.payment', $p)); ?>">Screenshot</a></small><?php endif; ?></td>
            <td><span class="badge <?php echo e($p->status); ?>"><?php echo e(ucfirst($p->status)); ?></span></td>
            <td><?php echo e($p->team?->short_name ?? '-'); ?></td>
            <td class="rowact">
                <?php if($p->status !== 'approved'): ?>
                    <form method="post" action="<?php echo e(route('admin.players.status', $p)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="approved"><button class="btn icon green" title="Approve" aria-label="Approve"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></button></form>
                <?php endif; ?>
                <?php if($p->status !== 'rejected'): ?>
                    <form method="post" action="<?php echo e(route('admin.players.status', $p)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="rejected"><button class="btn icon" title="Reject" aria-label="Reject"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></form>
                <?php endif; ?>
                <a class="btn icon" href="<?php echo e(route('admin.players.edit', $p)); ?>" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                <form method="post" action="<?php echo e(route('admin.players.delete', $p)); ?>" data-confirm="Delete this player?"><?php echo csrf_field(); ?> <?php echo method_field('delete'); ?><button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7">No players found.</td></tr>
    <?php endif; ?>
</table></div>

<div class="mobile-only">
    <div class="p-grid">
        <?php $__empty_1 = true; $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="p-card">
                <div class="p-card-top">
                    <span class="p-card-corner">#<?php echo e($p->id); ?></span>
                    <span class="badge <?php echo e($p->status); ?> p-card-status"><?php echo e(ucfirst($p->status)); ?></span>
                    <img src="<?php echo e($p->photoUrl()); ?>" alt="<?php echo e($p->name); ?>" loading="lazy">
                </div>
                <div class="p-card-body">
                    <div class="p-card-name"><?php echo e($p->name); ?></div>
                    <div class="p-card-pill"><?php echo e($p->role); ?><?php if($p->team): ?> &middot; <?php echo e($p->team->short_name); ?><?php endif; ?></div>
                    <div class="p-card-rows">
                        <div class="p-card-row"><span>📍</span><span><b>Village:</b> <?php echo e($p->village); ?></span></div>
                        <div class="p-card-row"><span>📞</span><span><b>Mobile:</b> <?php echo e($p->mobile); ?></span></div>
                        <div class="p-card-row"><span>👕</span><span><b>T-Shirt:</b> <?php echo e($p->tshirt_size); ?> &middot; <?php echo e($p->batting_style); ?></span></div>
                        <?php if($p->payment_ref || $p->payment_proof): ?>
                            <div class="p-card-row"><span>💳</span><span><b>Payment:</b> <?php echo e($p->payment_ref ?: 'Uploaded'); ?> <?php if($p->payment_proof): ?>&middot; <a target="_blank" href="<?php echo e(route('admin.players.payment', $p)); ?>">Screenshot</a><?php endif; ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-card-foot">
                        <?php if($p->status !== 'approved'): ?>
                            <form method="post" action="<?php echo e(route('admin.players.status', $p)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="approved"><button class="btn icon green" title="Approve" aria-label="Approve"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></button></form>
                        <?php endif; ?>
                        <?php if($p->status !== 'rejected'): ?>
                            <form method="post" action="<?php echo e(route('admin.players.status', $p)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="status" value="rejected"><button class="btn icon" title="Reject" aria-label="Reject"><svg viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></form>
                        <?php endif; ?>
                        <a class="btn icon" href="<?php echo e(route('admin.players.edit', $p)); ?>" title="Edit" aria-label="Edit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                        <form method="post" action="<?php echo e(route('admin.players.delete', $p)); ?>" data-confirm="Delete this player?"><?php echo csrf_field(); ?> <?php echo method_field('delete'); ?><button class="btn icon red" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card empty">No players found.</div>
        <?php endif; ?>
    </div>
</div>
<?php echo e($players->links('partials.pagination')); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/players.blade.php ENDPATH**/ ?>