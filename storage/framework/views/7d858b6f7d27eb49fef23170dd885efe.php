<?php if(count($card['innings'])): ?>
    <div class="card">
        <?php $__currentLoopData = $card['innings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $in): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="total" <?php if($loop->first): ?> style="border-top:0" <?php endif; ?>>
                <span><strong><?php echo e($in['model']->battingTeam->name); ?></strong></span>
                <strong><?php echo e($in['score']['runs']); ?>/<?php echo e($in['score']['wickets']); ?> <small>(<?php echo e($in['score']['overs']); ?>)</small></strong>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($card['chase']): ?><p class="chase"><?php echo e($card['chase']); ?></p><?php endif; ?>
    </div>
<?php endif; ?>

<h2 class="title">Cric11 Leaderboard</h2>
<?php if(count($leaderboard)): ?>
    <div class="card scroll">
        <table>
            <thead><tr><th>Rank</th><th>Team</th><th>C / VC</th><th>Points</th><th></th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $e = $row['entry'];
                        $isMine = (isset($ownedTokens[$e->id]) && hash_equals((string) $e->edit_token, (string) $ownedTokens[$e->id]))
                            || ($myMobile && $e->participant_mobile === $myMobile);
                        $canOpen = $fixture->status !== 'upcoming' || (isset($ownedTokens[$e->id]) && hash_equals((string) $e->edit_token, (string) $ownedTokens[$e->id]));
                    ?>
                    <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rank-1' => $row['rank'] === 1]); ?>">
                        <td><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rank-pill', 'r1' => $row['rank'] === 1, 'r2' => $row['rank'] === 2, 'r3' => $row['rank'] === 3]); ?>">#<?php echo e($row['rank']); ?></span></td>
                        <td>
                            <strong><?php echo e($e->team_name); ?></strong>
                            <?php if($isMine): ?><span class="badge approved">You</span><?php endif; ?>
                            <small><?php echo e($e->participant_name); ?></small>
                        </td>
                        <td>
                            <small>C: <strong><?php echo e($e->captain->name); ?></strong></small>
                            <small>VC: <?php echo e($e->viceCaptain->name); ?></small>
                        </td>
                        <td><strong><?php echo e(\App\Services\FantasyPointsCalculator::format($row['points'])); ?></strong></td>
                        <td>
                            <?php if($canOpen): ?>
                                <a class="btn small" href="<?php echo e(route('cric11.entry', $e)); ?>">Pitch</a>
                            <?php else: ?>
                                <small>Locked</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card empty">
        No Cric11 teams joined yet.
        <?php if($fixture->status === 'upcoming'): ?>
            <div style="margin-top:10px"><a class="btn gold" href="<?php echo e(route('cric11.create', $fixture)); ?>">Create the First Team</a></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($dreamTeam): ?>
    <h2 class="title">Dream Team (Best XI)</h2>
    <div class="card">
        <div class="meta" style="margin-bottom:8px">
            <span>Optimal XI Score: <strong><?php echo e(\App\Services\FantasyPointsCalculator::format($dreamTeam['total_points'])); ?> pts</strong></span>
            <span><?php echo e(number_format($dreamTeam['total_credits'], 1)); ?> Cr</span>
        </div>
        <div class="chips" style="margin-bottom:0">
            <?php $__currentLoopData = $pool->whereIn('id', $dreamTeam['player_ids'])->sortByDesc(fn ($p) => $pointsById[$p->id]['total'] ?? 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span style="border:1px solid var(--line)">
                    <strong><?php echo e($dp->name); ?></strong>
                    <?php if($dp->id === $dreamTeam['captain_id']): ?> <b style="color:var(--green)">(C)</b>
                    <?php elseif($dp->id === $dreamTeam['vice_captain_id']): ?> <b style="color:var(--navy2)">(VC)</b>
                    <?php endif; ?>
                    &middot; <?php echo e(\App\Services\FantasyPointsCalculator::format($pointsById[$dp->id]['total'] ?? 0)); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>

<h2 class="title">Player Fantasy Points</h2>
<div class="card scroll">
    <table>
        <thead><tr><th>Player</th><th>Team</th><th>Role</th><th>Cr</th><th>Play</th><th>Bat</th><th>Bowl</th><th>Field</th><th>Total</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $playerStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $p = $ps['player']; $pt = $ps['points']; ?>
                <tr>
                    <td><strong><?php echo e($p->name); ?></strong></td>
                    <td><?php echo e($p->team?->short_name); ?></td>
                    <td><?php echo e($p->roleCode()); ?></td>
                    <td><?php echo e(number_format($p->fantasyCredits(), 1)); ?></td>
                    <td><?php echo e($pt['playing']); ?></td>
                    <td><?php echo e($pt['batting']); ?></td>
                    <td><?php echo e($pt['bowling']); ?></td>
                    <td><?php echo e($pt['fielding']); ?></td>
                    <td><strong><?php echo e(\App\Services\FantasyPointsCalculator::format($pt['total'])); ?></strong></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="9" class="empty">No approved players in these teams yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/cric11/partials/match-live.blade.php ENDPATH**/ ?>