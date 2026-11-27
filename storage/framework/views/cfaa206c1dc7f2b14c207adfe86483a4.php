<?php $__env->startSection('title', 'Matches'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Matches &amp; Scoring</h1>
<?php if($teams->count() < 2): ?>
    <div class="card empty">Add at least two teams first.</div>
<?php else: ?>
<form class="card form" method="post" action="<?php echo e(route('admin.matches.save', $editing?->id)); ?>">
    <?php echo csrf_field(); ?>
    <h3 class="full"><?php echo e($editing ? 'Edit match' : 'Add match'); ?></h3>
    <label>Match number<input type="number" name="match_no" value="<?php echo e(old('match_no', $editing?->match_no ?? $nextNo)); ?>" required></label>
    <label>Stage<select name="stage"><?php $__currentLoopData = \App\Models\Fixture::STAGES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(old('stage', $editing?->stage) === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <?php $__currentLoopData = ['team_a_id' => 'Team A', 'team_b_id' => 'Team B']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label><?php echo e($label); ?><select name="<?php echo e($field); ?>" required><option value="">Select</option><?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t->id); ?>" <?php if((int) old($field, $editing?->$field) === $t->id): echo 'selected'; endif; ?>><?php echo e($t->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <label>Date and time<input type="datetime-local" name="starts_at" value="<?php echo e(old('starts_at', $editing?->starts_at->format('Y-m-d\TH:i') ?? $site['start_date'].'T09:00')); ?>" required></label>
    <label>Overs<input type="number" name="overs" min="1" max="50" value="<?php echo e(old('overs', $editing?->overs ?? 8)); ?>" required></label>
    <label>Players per side<input type="number" name="players_per_side" min="2" max="11" value="<?php echo e(old('players_per_side', $editing?->players_per_side ?? 11)); ?>" required></label>
    <button class="btn gold full">Save Match</button>
</form>
<?php endif; ?>
<div class="card scroll"><table>
    <tr><th>#</th><th>Match</th><th>When</th><th>Status</th><th>Actions</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $matches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><?php echo e($m->match_no); ?></td><td><strong><?php echo e($m->teamA->short_name); ?> vs <?php echo e($m->teamB->short_name); ?></strong><small><?php echo e($m->result_text ?? $m->stage); ?></small></td>
            <td><?php echo e($m->starts_at->format('d M, h:i A')); ?></td><td><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['badge', 'live' => $m->status === 'live']); ?>"><?php echo e(ucfirst($m->status)); ?></span></td>
            <td class="rowact"><a class="btn small gold" href="<?php echo e(route('admin.score', $m)); ?>">Score</a>
                <?php if($m->status === 'upcoming'): ?><a class="btn small" href="<?php echo e(route('admin.matches', ['edit' => $m->id])); ?>">Edit</a><?php endif; ?>
                <form method="post" action="<?php echo e(route('admin.matches.delete', $m)); ?>" data-confirm="Delete this match and its full score?"><?php echo csrf_field(); ?> <?php echo method_field('delete'); ?><button class="btn small red">Delete</button></form></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="5">No matches yet.</td></tr>
    <?php endif; ?>
</table></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/matches.blade.php ENDPATH**/ ?>