<div class="bg-custom-periwinkle/50 rounded-xl shadow-lg p-5 mt-6 space-y-3">
    <h3 class="text-xl font-bold text-custom-dark-blue">Suggested for you</h3>
    <?php if(isset($recommendedShares) && !$recommendedShares->isEmpty()): ?>
        <?php $__currentLoopData = $recommendedShares->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center space-x-3">
            <div class="relative" style="width: 48px; height: 48px;">
                <img class="absolute inset-0 w-full h-full object-cover rounded" src="<?php echo e($share->album_art_url); ?>" alt="Album Art">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate"><?php echo e($share->track_name); ?></p>
                <p class="text-xs text-gray-600 truncate"><?php echo e($share->artist_name); ?></p>
                <?php if(isset($share->reason)): ?>
                    <div class="mt-1 flex items-center text-xs text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1.017a7.002 7.002 0 016.29 6.291 1 1 0 11-1.98.208A5.002 5.002 0 0010 5V4a1 1 0 01-1-1zm0 16a7.002 7.002 0 01-6.29-6.291 1 1 0 111.98-.208A5.002 5.002 0 0010 15v1a1 1 0 01-1 1zm-5-8a1 1 0 00-1 1v1.017a7.002 7.002 0 006.29 6.291 1 1 0 10.208-1.98A5.002 5.002 0 015 10V9a1 1 0 00-1-1zm11 0a1 1 0 00-1 1v1.017a5.002 5.002 0 01-4.792 4.973 1 1 0 10.208 1.98A7.002 7.002 0 0015 11.017V10a1 1 0 00-1-1zM10 6a4 4 0 100 8 4 4 0 000-8z" /></svg>
                        <span><?php echo e($share->reason); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <p class="text-gray-500 text-sm">No recommendations for you right now.</p>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\musicsocial-main\resources\views/components/sidebar-right.blade.php ENDPATH**/ ?>