<?php $__env->startSection('title', 'Points Table'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Points Table</h1>
<div class="card scroll"><table>
    <tr><th>#</th><th>Team</th><th>P</th><th>W</th><th>L</th><th>T/NR</th><th>Pts</th><th>NRR</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td><?php echo e($loop->iteration); ?></td><td><a href="<?php echo e(route('team', $r['team'])); ?>"><?php echo e($r['team']->name); ?></a></td>
            <td><?php echo e($r['played']); ?></td><td><?php echo e($r['won']); ?></td><td><?php echo e($r['lost']); ?></td><td><?php echo e($r['tied']); ?></td>
            <td><strong><?php echo e($r['points']); ?></strong></td><td><?php echo e(sprintf('%+.3f', $r['nrr'])); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8">Teams will appear here.</td></tr>
    <?php endif; ?>
</table></div>
<div class="grid two">
    <?php $__currentLoopData = ['runs' => 'Top Run Scorers', 'wickets' => 'Top Wicket Takers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $heading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <section class="card"><h3><?php echo e($heading); ?></h3>
            <table>
                <?php $__empty_1 = true; $__currentLoopData = $leaders[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e($loop->iteration); ?></td><td><?php echo e($row['player']->name); ?><small><?php echo e($row['player']->team?->name); ?></small></td><td><strong><?php echo e($row['value']); ?></strong></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td>No data yet.</td></tr>
                <?php endif; ?>
            </table>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/points.blade.php ENDPATH**/ ?>