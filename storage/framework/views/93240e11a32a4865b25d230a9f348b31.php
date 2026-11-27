<?php $__env->startSection('title', 'Cric11 Fantasy'); ?>
<?php $__env->startSection('content'); ?>
<section class="cric-hero">
    <div>
        <p class="kicker" style="margin-top:0;color:var(--gold)">Cric11 Fantasy League</p>
        <h1>Build Your Dream XI</h1>
        <p>Pick <?php echo e(\App\Services\FantasyRules::STANDARD_SIZE); ?> players within 100 credits, choose your Captain (2x) and Vice-Captain (1.5x), and track live points ball-by-ball.</p>
    </div>
    <div class="actions" style="margin:0">
        <a class="btn gold" href="#matches-hub">Pick a Match</a>
    </div>
</section>

<form class="card filters" method="post" action="<?php echo e(route('cric11.profile')); ?>">
    <?php echo csrf_field(); ?>
    <input type="text" name="participant_mobile" value="<?php echo e(old('participant_mobile', $myMobile)); ?>" placeholder="Enter 10-digit mobile to view My Teams" pattern="[6-9][0-9]{9}" maxlength="10" required>
    <input type="text" name="participant_name" value="<?php echo e(old('participant_name', session('cric11_name'))); ?>" placeholder="Your name (optional)" maxlength="80">
    <button class="btn">Show My Teams</button>
</form>

<?php if($myEntries->count()): ?>
    <h2 class="title">My Cric11 Teams</h2>
    <div class="grid">
        <?php $__currentLoopData = $myEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <div class="meta">
                    <span>Match <?php echo e($e->fixture->match_no); ?> &middot; <?php echo e($e->fixture->teamA->short_name); ?> vs <?php echo e($e->fixture->teamB->short_name); ?></span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['badge', 'live' => $e->fixture->status === 'live']); ?>"><?php echo e(ucfirst($e->fixture->status)); ?></span>
                </div>
                <h3 style="margin-top:6px"><?php echo e($e->team_name); ?></h3>
                <p style="font-size:13px;color:var(--muted)">
                    C: <strong><?php echo e($e->captain->name); ?></strong> &middot;
                    VC: <strong><?php echo e($e->viceCaptain->name); ?></strong> &middot;
                    <?php echo e(number_format($e->total_credits, 1)); ?> Cr
                </p>
                <div class="actions" style="margin:10px 0 0">
                    <a class="btn small" href="<?php echo e(route('cric11.entry', $e)); ?>">View Pitch</a>
                    <a class="btn small gold" href="<?php echo e(route('cric11.match', $e->fixture)); ?>">Leaderboard</a>
                    <?php if($e->fixture->status === 'upcoming'): ?>
                        <a class="btn small" href="<?php echo e(route('cric11.edit', $e)); ?>">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div id="matches-hub">
<?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($list->count()): ?>
        <h2 class="title"><?php echo e($label); ?> Matches</h2>
        <div class="grid">
            <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card">
                    <div class="meta">
                        <span>Match <?php echo e($m->match_no); ?> &middot; <?php echo e($m->stage); ?></span>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['badge', 'live' => $m->status === 'live']); ?>"><?php echo e(ucfirst($m->status)); ?></span>
                    </div>
                    <div class="versus">
                        <strong><?php echo e($m->teamA->name); ?></strong><em>vs</em><strong><?php echo e($m->teamB->name); ?></strong>
                    </div>
                    <div class="meta">
                        <span><?php echo e($m->starts_at->format('D, d M Y h:i A')); ?></span>
                        <span><?php echo e($m->fantasy_entries_count); ?> <?php echo e($m->fantasy_entries_count === 1 ? 'team' : 'teams'); ?></span>
                    </div>
                    <?php if($m->result_text): ?><p class="result"><?php echo e($m->result_text); ?></p><?php endif; ?>
                    <div class="actions" style="margin:12px 0 0">
                        <?php if($m->status === 'upcoming'): ?>
                            <a class="btn gold small" href="<?php echo e(route('cric11.create', $m)); ?>">+ Create Team</a>
                        <?php endif; ?>
                        <a class="btn small" href="<?php echo e(route('cric11.match', $m)); ?>">
                            <?php echo e($m->status === 'upcoming' ? 'Contests & Rules' : 'Live Leaderboard'); ?>

                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if(collect($groups)->every->isEmpty()): ?>
    <div class="card empty">No matches scheduled yet. Once matches are added in Admin, you can build your Cric11 Dream XI here!</div>
<?php endif; ?>
</div>

<?php if(count($overall)): ?>
    <h2 class="title">Tournament Cric11 Leaderboard</h2>
    <div class="card scroll">
        <table>
            <thead><tr><th>Rank</th><th>Manager</th><th>Matches</th><th>Total Points</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $overall; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rank-1' => $idx === 0]); ?>">
                        <td><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rank-pill', 'r1' => $idx === 0, 'r2' => $idx === 1, 'r3' => $idx === 2]); ?>">#<?php echo e($idx + 1); ?></span></td>
                        <td><?php echo e($row['name']); ?></td>
                        <td><?php echo e($row['matches']); ?></td>
                        <td><strong><?php echo e(\App\Services\FantasyPointsCalculator::format($row['points'])); ?></strong></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/cric11/index.blade.php ENDPATH**/ ?>