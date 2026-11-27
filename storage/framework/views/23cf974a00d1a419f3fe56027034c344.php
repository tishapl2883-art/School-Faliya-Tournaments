<div class="chips">
    <span>Sold: <b><?php echo e($counts['sold'] ?? 0); ?></b></span>
    <span>Waiting: <b><?php echo e($counts['pending'] ?? 0); ?></b></span>
    <span>Unsold: <b><?php echo e($counts['unsold'] ?? 0); ?></b></span>
</div>
<?php if($player): ?>
    <section class="card stage">
        <img src="<?php echo e($player->photoUrl()); ?>" alt="<?php echo e($player->name); ?>">
        <div>
            <span class="badge live">On auction</span>
            <h2><?php echo e($player->name); ?></h2>
            <p><?php echo e($player->role); ?> &middot; <?php echo e($player->village); ?></p>
            <p><?php echo e($player->batting_style); ?> bat &middot; <?php echo e($player->bowling_style); ?></p>
            <div class="bid">
                <small><?php echo e($top ? 'Current bid' : 'Base price'); ?></small>
                <strong><?php echo e(number_format($top?->amount ?? (int) $site['base_price'])); ?></strong>
                <span><?php echo e($top ? $top->team->name : 'Waiting for first bid'); ?></span>
            </div>
        </div>
    </section>
<?php else: ?>
    <div class="card empty">Waiting for the next player. This page updates by itself.</div>
<?php endif; ?>

<h2 class="title">Team Purse</h2>
<div class="card scroll"><table>
    <tr><th>Team</th><th>Players</th><th>Remaining</th><th>Max bid</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><a href="<?php echo e(route('team', $t)); ?>"><?php echo e($t->name); ?></a></td><td><?php echo e($t->players_count); ?>/<?php echo e($site['max_squad']); ?></td>
            <td><?php echo e(number_format($t->balance)); ?></td><td><?php echo e($t->players_count >= (int) $site['max_squad'] ? 'Full' : number_format($t->max_bid)); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4">Teams will appear here.</td></tr>
    <?php endif; ?>
</table></div>

<?php if($recent->count()): ?>
    <h2 class="title">Recently Sold</h2>
    <div class="card scroll"><table>
        <tr><th>Player</th><th>Team</th><th>Points</th></tr>
        <?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($p->name); ?></td><td><?php echo e($p->team?->name); ?></td><td><?php echo e(number_format((int) $p->sold_price)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table></div>
<?php endif; ?>
<?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/partials/auction-board.blade.php ENDPATH**/ ?>