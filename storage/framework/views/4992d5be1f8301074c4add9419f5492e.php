<?php if($paginator->hasPages()): ?>
    <nav class="pager">
        <?php if(! $paginator->onFirstPage()): ?><a class="btn small" href="<?php echo e($paginator->previousPageUrl()); ?>">&laquo; Previous</a><?php endif; ?>
        <span>Page <?php echo e($paginator->currentPage()); ?> of <?php echo e($paginator->lastPage()); ?></span>
        <?php if($paginator->hasMorePages()): ?><a class="btn small" href="<?php echo e($paginator->nextPageUrl()); ?>">Next &raquo;</a><?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/partials/pagination.blade.php ENDPATH**/ ?>