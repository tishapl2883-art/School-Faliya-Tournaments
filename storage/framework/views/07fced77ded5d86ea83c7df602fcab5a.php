<?php $__env->startSection('title', $entry->team_name.' | Cric11'); ?>
<?php $__env->startSection('content'); ?>
<div class="actions">
    <a class="btn small" href="<?php echo e(route('cric11.match', $fixture)); ?>">&larr; Back to Match Leaderboard</a>
    <?php if($canEdit): ?>
        <a class="btn small gold" href="<?php echo e(route('cric11.edit', $entry)); ?>">Edit Team</a>
    <?php endif; ?>
</div>

<div class="card scorehead">
    <div class="meta">
        <span>Match <?php echo e($fixture->match_no); ?> &middot; <?php echo e($fixture->teamA->name); ?> vs <?php echo e($fixture->teamB->name); ?></span>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['badge', 'live' => $fixture->status === 'live']); ?>"><?php echo e(ucfirst($fixture->status)); ?></span>
    </div>
    <div class="total" style="border-top:0;padding-bottom:0">
        <div>
            <h1 style="font-size:26px"><?php echo e($entry->team_name); ?></h1>
            <small>Manager: <?php echo e($entry->participant_name); ?> &middot; <?php echo e(number_format($entry->total_credits, 1)); ?> Cr used</small>
        </div>
        <div style="text-align:right">
            <strong><?php echo e(\App\Services\FantasyPointsCalculator::format($totalPoints)); ?></strong>
            <small style="display:block">Total Points</small>
        </div>
    </div>
</div>

<div class="pitch" style="margin-bottom:16px">
    <?php $__currentLoopData = ['WK' => 'Wicket Keepers', 'BAT' => 'Batters', 'AR' => 'All Rounders', 'BOWL' => 'Bowlers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($byRole[$code]->count()): ?>
            <div class="pitch-row">
                <div class="pitch-label"><?php echo e($label); ?></div>
                <div class="pitch-players">
                    <?php $__currentLoopData = $byRole[$code]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $base = $pointsById[$p->id]['total'] ?? 0;
                            $isC = (int) $entry->captain_id === $p->id;
                            $isVC = (int) $entry->vice_captain_id === $p->id;
                            $pts = $base * ($isC ? 2.0 : ($isVC ? 1.5 : 1.0));
                        ?>
                        <div class="pitch-node">
                            <?php if($isC): ?><span class="cap-badge">C 2x</span><?php elseif($isVC): ?><span class="cap-badge vc">VC 1.5x</span><?php endif; ?>
                            <img src="<?php echo e($p->photoUrl()); ?>" alt="<?php echo e($p->name); ?>">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['pitch-tag', 'away' => $p->team_id === $fixture->team_b_id]); ?>"><?php echo e($p->name); ?></span>
                            <span class="pitch-pts"><?php echo e(\App\Services\FantasyPointsCalculator::format($pts)); ?> pts</span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<h2 class="title">Points Breakdown</h2>
<div class="card scroll">
    <table>
        <thead><tr><th>Player</th><th>Team</th><th>Role</th><th>Play</th><th>Bat</th><th>Bowl</th><th>Field</th><th>Mult</th><th>Points</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $entry->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $pt = $pointsById[$p->id] ?? ['playing' => 0, 'batting' => 0, 'bowling' => 0, 'fielding' => 0, 'total' => 0];
                    $isC = (int) $entry->captain_id === $p->id;
                    $isVC = (int) $entry->vice_captain_id === $p->id;
                    $mult = $isC ? 2.0 : ($isVC ? 1.5 : 1.0);
                    $final = $pt['total'] * $mult;
                ?>
                <tr>
                    <td>
                        <strong><?php echo e($p->name); ?></strong>
                        <?php if($isC): ?><span class="badge pending">C (2x)</span><?php elseif($isVC): ?><span class="badge">VC (1.5x)</span><?php endif; ?>
                    </td>
                    <td><?php echo e($p->team?->short_name); ?></td>
                    <td><?php echo e($p->roleCode()); ?></td>
                    <td><?php echo e($pt['playing']); ?></td>
                    <td><?php echo e($pt['batting']); ?></td>
                    <td><?php echo e($pt['bowling']); ?></td>
                    <td><?php echo e($pt['fielding']); ?></td>
                    <td><?php echo e($mult); ?>x</td>
                    <td><strong><?php echo e(\App\Services\FantasyPointsCalculator::format($final)); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/cric11/entry.blade.php ENDPATH**/ ?>