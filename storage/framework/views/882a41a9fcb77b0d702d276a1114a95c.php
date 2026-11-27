<?php $__env->startSection('title', 'Auction Control'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Auction Control</h1>
<?php if($player): ?>
    <?php $top = $bids->first(); $next = \App\Services\AuctionRules::nextBid($top?->amount, (int) $site['base_price'], (int) $site['bid_increment']); ?>
    <section class="card stage">
        <img src="<?php echo e($player->photoUrl()); ?>" alt="">
        <div>
            <h2><?php echo e($player->name); ?></h2><p><?php echo e($player->role); ?> &middot; <?php echo e($player->village); ?></p>
            <div class="bid"><small><?php echo e($top ? 'Current bid' : 'Base price'); ?></small><strong><?php echo e(number_format($top?->amount ?? $next)); ?></strong><span><?php echo e($top?->team->name ?? 'No bid yet'); ?></span></div>
        </div>
    </section>
    <form class="card" method="post" action="<?php echo e(route('admin.auction.bid')); ?>" data-once>
        <?php echo csrf_field(); ?><input type="hidden" name="player_id" value="<?php echo e($player->id); ?>">
        <label>Custom amount (leave empty to bid <?php echo e(number_format($next)); ?>)<input type="number" name="amount" min="1"></label>
        <p>Tap a team to place its bid:</p>
        <div class="actions">
            <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button class="btn" name="team_id" value="<?php echo e($t->id); ?>" <?php if($top?->team_id === $t->id): echo 'disabled'; endif; ?>><?php echo e($t->name); ?><small>left <?php echo e(number_format($t->remaining())); ?></small></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </form>
    <div class="actions">
        <?php $__currentLoopData = ['sold' => ['SOLD', 'gold'], 'unsold' => ['Unsold', 'red']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => [$label, $class]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form method="post" action="<?php echo e(route('admin.auction.'.$action)); ?>" data-confirm="Confirm: <?php echo e($label); ?>?"><?php echo csrf_field(); ?><input type="hidden" name="player_id" value="<?php echo e($player->id); ?>"><button class="btn <?php echo e($class); ?>"><?php echo e($label); ?></button></form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <form method="post" action="<?php echo e(route('admin.auction.undo')); ?>"><?php echo csrf_field(); ?><button class="btn">Undo last bid</button></form>
    </div>
    <?php if($bids->count()): ?><div class="card"><h3>Bid history</h3><table><?php $__currentLoopData = $bids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($b->team->name); ?></td><td><?php echo e(number_format($b->amount)); ?></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></table></div><?php endif; ?>
<?php else: ?>
    <form class="card form" method="post" action="<?php echo e(route('admin.auction.start')); ?>" data-once>
        <?php echo csrf_field(); ?>
        <label class="full">Choose player (or leave empty for a random waiting player)
            <select name="player_id"><option value="">Random player</option>
                <?php $__currentLoopData = $queue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> - <?php echo e($p->role); ?><?php echo e($p->auction_status === 'unsold' ? ' (unsold)' : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select></label>
        <button class="btn gold full">Start bidding</button>
    </form>
<?php endif; ?>
<h2 class="title">Sold &amp; Assigned Players</h2>
<div class="card scroll"><table>
    <tr><th>Player</th><th>Team</th><th>Points</th><th></th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $sold; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><?php echo e($p->name); ?></td><td><?php echo e($p->team?->name); ?></td><td><?php echo e(number_format((int) $p->sold_price)); ?></td>
            <td><form method="post" action="<?php echo e(route('admin.auction.revert', $p)); ?>" data-confirm="Cancel this sale?"><?php echo csrf_field(); ?><button class="btn small red">Cancel sale</button></form></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4">Nothing sold yet.</td></tr>
    <?php endif; ?>
</table></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/admin/auction.blade.php ENDPATH**/ ?>