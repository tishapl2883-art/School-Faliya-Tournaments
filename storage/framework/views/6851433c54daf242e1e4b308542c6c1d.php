<?php $titles = ['about' => 'About', 'contact' => 'Contact Us', 'terms' => 'Terms & Conditions', 'privacy' => 'Privacy Policy', 'refunds' => 'Cancellations & Refunds']; ?>
<?php $__env->startSection('title', $titles[$page]); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title"><?php echo e($titles[$page]); ?></h1>
<div class="card prose">
<?php switch($page):
    case ('about'): ?>
        <p><?php echo nl2br(e($site['about'])); ?></p>
        <p><strong>Brand:</strong> <?php echo e($site['brand']); ?><br><strong>Organiser:</strong> <?php echo e($site['organizer']); ?></p>
        <?php break; ?>
    <?php case ('contact'): ?>
        <p><strong>Organiser:</strong> <?php echo e($site['organizer']); ?></p>
        <p><strong>Venue:</strong> <?php echo e($site['venue']); ?></p>
        <p><strong>Rajan:</strong> <a href="tel:8460102232">8460102232</a><br><strong>Jaymal:</strong> <a href="tel:8866751546">8866751546</a></p>
        <?php if($site['contact_phone']): ?><p><strong>Phone:</strong> <a href="tel:<?php echo e($site['contact_phone']); ?>"><?php echo e($site['contact_phone']); ?></a></p><?php endif; ?>
        <p><a class="btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo e(urlencode($site['venue'].' Parnera')); ?>">Find venue on map</a></p>
        <?php break; ?>
    <?php case ('terms'): ?>
        <ul>
            <li>Players must give correct details. Wrong details can lead to rejection.</li>
            <li>Registration is complete only after the organiser approves it.</li>
            <li>The umpire's and organiser's decisions are final.</li>
            <li>Every player is responsible for their own safety and playing kit.</li>
        </ul>
        <?php break; ?>
    <?php case ('privacy'): ?>
        <p>We collect your name, mobile number, date of birth, village, photo and payment details only to run this tournament. Your name, photo, village and playing details are shown on this website. Your mobile number, date of birth and payment details are not shown publicly. Contact the organiser to correct or remove your data.</p>
        <?php break; ?>
    <?php case ('refunds'): ?>
        <p>For questions about cancellation or refund of the registration fee, contact the organiser.</p>
        <?php break; ?>
<?php endswitch; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/page.blade.php ENDPATH**/ ?>