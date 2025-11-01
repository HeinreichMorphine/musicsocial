<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['share']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['share']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="p-6 flex space-x-4" x-data="{ commentsOpen: false }">
    <img src="<?php echo e($share->user->profile_picture ? Storage::url($share->user->profile_picture) : 'https://via.placeholder.com/150'); ?>"
         alt="avatar"
         class="h-14 w-14 rounded-full object-cover">

    <div class="flex-1">
        <div class="flex justify-between items-center">
            <div>
                <a href="<?php echo e(route('profile.show', $share->user->name)); ?>" class="font-bold text-gray-900"><?php echo e($share->user->name); ?></a>
                <span class="text-gray-500 text-sm"> &middot; <?php echo e($share->created_at->diffForHumans()); ?></span>
            </div>
            <?php if($share->user->is(auth()->user())): ?>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-10" style="display: none;">
                        <form @submit.prevent="
                            if (!confirm('Are you sure you want to delete this share?')) return;
                            fetch('<?php echo e(route('shares.destroy', $share)); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            })
                            .then(response => {
                                if (response.ok) {
                                    $el.closest('.p-6.flex.space-x-4').remove();
                                }
                            })
                        ">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Delete Share
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <p class="mt-2 text-gray-800">
            <?php echo e($share->caption); ?>

        </p>

        <div class="mt-3 border rounded-lg overflow-hidden hover:bg-gray-50 transition">
            <div class="flex items-center space-x-4 p-4">
                <img src="<?php echo e($share->album_art_url); ?>" alt="Album Art" class="w-16 h-16 rounded shadow">
                <div class="flex-1 min-w-0">
                    <p class="text-lg font-bold text-gray-900 truncate"><?php echo e($share->track_name); ?></p>
                    <p class="text-sm text-gray-600 truncate"><?php echo e($share->artist_name); ?></p>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="<?php echo e($share->spotify_url); ?>" target="_blank" title="Listen on Spotify" class="hover:opacity-75">
                        <img src="<?php echo e(asset('icons/spotify_icon.png')); ?>" alt="Spotify Logo" class="w-8 h-8">
                    </a>

                    <?php if($share->youtube_url): ?>
                        <a href="<?php echo e($share->youtube_url); ?>" target="_blank" title="Watch on YouTube" class="hover:opacity-75">
                            <img src="<?php echo e(asset('icons/youtube_icon.png')); ?>" alt="YouTube Logo" class="w-8 h-8">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center space-x-6">
            <form @submit.prevent="
                fetch('<?php echo e(route('shares.like', $share)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    liked = data.liked;
                    likesCount = data.likesCount;
                })
            " x-data="{ liked: <?php echo e(auth()->check() && auth()->user()->likes->contains($share) ? 'true' : 'false'); ?>, likesCount: <?php echo e($share->likes->count()); ?> }">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex items-center text-gray-500 hover:text-custom-mid-blue">
                    <template x-if="liked">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-custom-mid-blue"><path d="M11.645 20.91a.75.75 0 0 1-1.29 0C8.343 16.63 3.75 12.55 3.75 8.25 3.75 5.399 5.399 3.75 8.25 3.75c1.74 0 3.333.92 4.25 2.336C13.417 4.67 15.01 3.75 16.75 3.75c2.851 0 4.5 1.649 4.5 4.5 0 4.3-4.593 8.38-6.605 10.369a.75.75 0 0 1-1.29-.012Z" /></svg>
                    </template>
                    <template x-if="!liked">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </template>
                    <span x-text="likesCount" class="ml-1 text-sm"></span>
                </button>
            </form>

            <button @click="commentsOpen = !commentsOpen" class="flex items-center text-gray-500 hover:text-custom-mid-blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.056 3 11.625c0 4.291 3.52 7.846 8.25 8.142.026.002.051.002.076.002Z" />
                </svg>
                <span class="ml-1 text-sm"><?php echo e($share->comments->count()); ?></span>
            </button>
        </div>

        <div x-show="commentsOpen" x-transition class="mt-4" style="display: none;" x-data="{ newComment: '' }">
            <form @submit.prevent="
                fetch('<?php echo e(route('shares.comments.store', $share)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        body: newComment
                    })
                })
                .then(response => response.text())
                .then(html => {
                    let commentSection = $el.nextElementSibling;
                    commentSection.insertAdjacentHTML('beforeend', html);
                    newComment = '';
                })
            " class="flex items-center space-x-2">
                <?php echo csrf_field(); ?>
                <img src="<?php echo e(auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150'); ?>" alt="your avatar" class="h-8 w-8 rounded-full">
                <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['xModel' => 'newComment','name' => 'body','class' => 'block w-full','placeholder' => 'Write a comment...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'newComment','name' => 'body','class' => 'block w-full','placeholder' => 'Write a comment...','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit','class' => 'bg-custom-mid-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'bg-custom-mid-blue']); ?>Post <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
            </form>

            <div class="mt-4 space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $share->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if (isset($component)) { $__componentOriginalfe4855bb643954c83a0cbd6710da1102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe4855bb643954c83a0cbd6710da1102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.comment','data' => ['comment' => $comment]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('comment'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['comment' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($comment)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfe4855bb643954c83a0cbd6710da1102)): ?>
<?php $attributes = $__attributesOriginalfe4855bb643954c83a0cbd6710da1102; ?>
<?php unset($__attributesOriginalfe4855bb643954c83a0cbd6710da1102); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfe4855bb643954c83a0cbd6710da1102)): ?>
<?php $component = $__componentOriginalfe4855bb643954c83a0cbd6710da1102; ?>
<?php unset($__componentOriginalfe4855bb643954c83a0cbd6710da1102); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-gray-500 text-center">No comments yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div><?php /**PATH C:\laragon\www\musicsocial\resources\views/components/share-card.blade.php ENDPATH**/ ?>