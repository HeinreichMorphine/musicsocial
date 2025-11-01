<div class="bg-custom-periwinkle/50 rounded-xl shadow-lg p-5 mt-6 space-y-3">
    <h3 class="text-xl font-bold text-custom-dark-blue">Suggested for you</h3>
    <?php $__empty_1 = true; $__currentLoopData = $recommendedShares->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex items-center space-x-3">
            <img src="<?php echo e($share->album_art_url); ?>" alt="Album Art" class="w-12 h-12 rounded">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate"><?php echo e($share->track_name); ?></p>
                <p class="text-xs text-gray-600 truncate"><?php echo e($share->artist_name); ?></p>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\musicsocial-main\resources\views/components/sidebar-right.blade.php ENDPATH**/ ?>