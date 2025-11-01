<div class="bg-white rounded-xl shadow-lg p-5 space-y-4">
    <h3 class="text-xl font-bold text-gray-900">Who to Follow</h3>
    <div class="space-y-3">
        <?php
            // [NEW] Logic to find users NOT followed by the current user
            $suggestedUsers = \App\Models\User::where('id', '!=', auth()->id())
                ->whereDoesntHave('followers', function ($query) {
                    $query->where('follower_id', auth()->id());
                })
                ->inRandomOrder()
                ->limit(10)
                ->get();
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $suggestedUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('profile.show', $user->name)); ?>" class="flex items-center space-x-3">
                    <img src="<?php echo e($user->profile_picture ?? 'https://via.placeholder.com/50'); ?>" alt="avatar" class="w-10 h-10 rounded-full">
                    <span class="font-semibold text-gray-900"><?php echo e($user->name); ?></span>
                </a>

                <form method="POST" action="<?php echo e(route('users.follow', $user)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-sm font-bold text-white bg-custom-mid-blue hover:bg-custom-dark-blue px-3 py-1 rounded-full transition">
                        Follow
                    </button>
                </form>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-500 text-sm">No new users to suggest right now.</p>
        <?php endif; ?>
    </div>
</div>

<div class="bg-custom-periwinkle/50 rounded-xl shadow-lg p-5 mt-6 space-y-3">
    <h3 class="text-xl font-bold text-custom-dark-blue">Discovery Engine</h3>
    <p class="text-sm text-gray-700">
        This area will feature **recommendations based on similar taste** and collaborative filtering[cite: 22].
        (e.g., "People who listen to X also like Y").
    </p>
    <p class="text-sm italic text-gray-700">
        Future implementation of the CF microservice will populate this area.
    </p>
</div><?php /**PATH C:\laragon\www\musicsocial\resources\views/layouts/sidebar-right.blade.php ENDPATH**/ ?>