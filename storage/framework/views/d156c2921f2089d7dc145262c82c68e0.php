<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => 'Home Feed']); ?>

    <div x-data="{ isMusicShareModalOpen: false }">

        <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

                <div class="hidden md:block col-span-2">
                    <div class="sticky top-0 pt-4">
                        <?php echo $__env->make('layouts.navigation-social', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-7">

                    <?php if (isset($component)) { $__componentOriginal2c3d6958985310adad7f89141edf5da6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c3d6958985310adad7f89141edf5da6 = $attributes; } ?>
<?php $component = App\View\Components\PostComposer::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('post-composer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PostComposer::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c3d6958985310adad7f89141edf5da6)): ?>
<?php $attributes = $__attributesOriginal2c3d6958985310adad7f89141edf5da6; ?>
<?php unset($__attributesOriginal2c3d6958985310adad7f89141edf5da6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c3d6958985310adad7f89141edf5da6)): ?>
<?php $component = $__componentOriginal2c3d6958985310adad7f89141edf5da6; ?>
<?php unset($__componentOriginal2c3d6958985310adad7f89141edf5da6); ?>
<?php endif; ?>

                    <div class="bg-white shadow-sm rounded-xl divide-y">
                        <?php $__currentLoopData = $shares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.share-card','data' => ['share' => $share]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('share-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['share' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($share)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a)): ?>
<?php $attributes = $__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a; ?>
<?php unset($__attributesOriginald55b37bda88b0e2e13b258f7fcfb7e9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a)): ?>
<?php $component = $__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a; ?>
<?php unset($__componentOriginald55b37bda88b0e2e13b258f7fcfb7e9a); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="mt-4">
                        <?php echo e($shares->links()); ?>

                    </div>

                </div>
                <div class="hidden md:block col-span-3">
                    <div class="sticky top-0 pt-4">
                        <div class="p-6 text-gray-900 mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Who to Follow</h3>

                            <?php $__empty_1 = true; $__currentLoopData = $usersToSuggest; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex items-center justify-between mb-4 last:mb-0">
                                    <div class="flex items-center">
                                        <img class="w-10 h-10 rounded-full mr-3" src="<?php echo e($suggestedUser->profile_picture_url ?: asset('images/default-profile.png')); ?>" alt="<?php echo e($suggestedUser->name); ?>">
                                        <div>
                                            <a href="<?php echo e(route('profile.show', $suggestedUser->name)); ?>" class="font-semibold text-gray-800 hover:underline"><?php echo e($suggestedUser->name); ?></a>
                                            <p class="text-sm text-gray-500"><?php echo e(' @' . $suggestedUser->username); ?></p>
                                        </div>
                                    </div>
                                    <form action="<?php echo e(route('users.follow', $suggestedUser)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold py-1 px-3 rounded-full transition duration-150">
                                            Follow
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p>No new users to suggest right now.</p>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($component)) { $__componentOriginaleb110d187bacbd2efbc61217697b3215 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleb110d187bacbd2efbc61217697b3215 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-right','data' => ['recommendedShares' => $recommendedShares]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['recommendedShares' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recommendedShares)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaleb110d187bacbd2efbc61217697b3215)): ?>
<?php $attributes = $__attributesOriginaleb110d187bacbd2efbc61217697b3215; ?>
<?php unset($__attributesOriginaleb110d187bacbd2efbc61217697b3215); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaleb110d187bacbd2efbc61217697b3215)): ?>
<?php $component = $__componentOriginaleb110d187bacbd2efbc61217697b3215; ?>
<?php unset($__componentOriginaleb110d187bacbd2efbc61217697b3215); ?>
<?php endif; ?>
                        <div class="mt-4">
                            <button @click="isMusicShareModalOpen = true" class="bg-custom-mid-blue hover:bg-custom-dark-blue p-3 rounded-full shadow-lg transition w-full flex items-center justify-center">
                                <img src="<?php echo e(asset('icons/share.png')); ?>" alt="Share Music" class="w-8 h-8 mr-2">
                                <span class="text-white font-semibold">Share Music</span>
                            </button>
                        </div>
                    </div>
                </div>
                </div>
        </div>

        <?php if (isset($component)) { $__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.music-share-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('music-share-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be)): ?>
<?php $attributes = $__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be; ?>
<?php unset($__attributesOriginal6e29d87fe5237fabdc8f45f8dc8c27be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be)): ?>
<?php $component = $__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be; ?>
<?php unset($__componentOriginal6e29d87fe5237fabdc8f45f8dc8c27be); ?>
<?php endif; ?>

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
<?php endif; ?><?php /**PATH C:\laragon\www\musicsocial-main\resources\views/dashboard.blade.php ENDPATH**/ ?>