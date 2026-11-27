<?php $__env->startSection('title', $fixture->teamA->short_name.' vs '.$fixture->teamB->short_name.' | Cric11'); ?>
<?php $__env->startSection('content'); ?>
<div class="actions">
    <a class="btn small" href="<?php echo e(route('cric11.index')); ?>">&larr; All Cric11 Matches</a>
    <a class="btn small" href="<?php echo e(route('match', $fixture)); ?>">Full Scorecard</a>
    <?php if($fixture->status === 'upcoming'): ?>
        <a class="btn small gold" href="<?php echo e(route('cric11.create', $fixture)); ?>">+ Create Team</a>
    <?php endif; ?>
</div>

<div class="card scorehead">
    <div class="meta">
        <span>Match <?php echo e($fixture->match_no); ?> &middot; <?php echo e($fixture->stage); ?></span>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['badge', 'live' => $fixture->status === 'live']); ?>"><?php echo e(ucfirst($fixture->status)); ?></span>
    </div>
    <div class="versus">
        <strong><?php echo e($fixture->teamA->name); ?></strong><em>vs</em><strong><?php echo e($fixture->teamB->name); ?></strong>
    </div>
    <div class="meta">
        <span><?php echo e($fixture->starts_at->format('D, d M Y h:i A')); ?></span>
        <span><?php echo e(count($leaderboard)); ?> <?php echo e(count($leaderboard) === 1 ? 'Cric11 team' : 'Cric11 teams'); ?></span>
    </div>
    <?php if($fixture->result_text): ?><p class="result"><?php echo e($fixture->result_text); ?></p><?php endif; ?>
</div>

<div <?php if($fixture->status === 'live'): ?> data-refresh="<?php echo e(route('cric11.match.data', $fixture)); ?>" data-every="4000" <?php endif; ?>>
    <?php echo $__env->make('cric11.partials.match-live', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<details class="card" style="margin-top:16px">
    <summary>Cric11 Fantasy Points System</summary>
    <div class="scroll" style="margin-top:10px">
        <table>
            <thead><tr><th>Action</th><th>Points</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => $pts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($action); ?></td><td><strong><?php echo e($pts); ?></strong></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</details>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/cric11/match.blade.php ENDPATH**/ ?>