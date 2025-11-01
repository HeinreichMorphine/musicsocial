<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => ''.e($user->name).' is Following']); ?>
    <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    <?php
                        $isHome = Route::is('dashboard');
                        $isProfile = false;
                        $isSettings = false;
                        $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                        $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                    ?>
                    <?php echo $__env->make('layouts.navigation-social', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <div class="col-span-12 md:col-span-7">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                            <?php echo e($user->name); ?> is Following
                        </h2>

                        <ul class="space-y-4">
                            <?php $__currentLoopData = $following; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $followedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-center space-x-4">
                                    <a href="<?php echo e(route('profile.show', $followedUser->name)); ?>">
                                        <img src="<?php echo e($followedUser->profile_picture ? Storage::url($followedUser->profile_picture) : 'https://via.placeholder.com/40'); ?>" alt="<?php echo e($followedUser->name); ?>'s Avatar" class="h-10 w-10 rounded-full object-cover">
                                    </a>
                                    <a href="<?php echo e(route('profile.show', $followedUser->name)); ?>" class="font-semibold text-gray-800 hover:underline">
                                        <?php echo e($followedUser->name); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>

                        <?php echo e($following->links()); ?>

                    </div>
                </div>
            </div>

            <div class="hidden md:block col-span-3">
                <div class="sticky top-0 pt-4">
                    <?php echo $__env->make('layouts.sidebar-right', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\musicsocial\resources\views/profile/following.blade.php ENDPATH**/ ?>