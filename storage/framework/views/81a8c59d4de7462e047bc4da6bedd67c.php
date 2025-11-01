<div class="flex space-x-3" x-data="{ openReply: false, openEdit: false }">
    <img src="<?php echo e($comment->user->profile_picture ? Storage::url($comment->user->profile_picture) : 'https://via.placeholder.com/150'); ?>"
         alt="avatar"
         class="h-10 w-10 rounded-full object-cover">
    <div class="flex-1">
        <div>
            <a href="<?php echo e(route('profile.show', $comment->user->name)); ?>" class="font-bold text-gray-900"><?php echo e($comment->user->name); ?></a>
            <span class="text-gray-500 text-sm"> &middot; <?php echo e($comment->created_at->diffForHumans()); ?></span>
        </div>
        <div x-show="!openEdit">
            <p class="mt-1 text-gray-800">
                <?php echo e($comment->body); ?>

            </p>
        </div>

        <div x-show="openEdit" x-transition class="mt-2" style="display: none;" x-data="{ editedBody: '<?php echo e($comment->body); ?>' }">
            <form @submit.prevent="
                fetch('<?php echo e(route('shares.comments.update', ['share' => $comment->share, 'comment' => $comment])); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({ body: editedBody, _method: 'PATCH' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.body) {
                        $el.closest('.flex-1').querySelector('p.mt-1.text-gray-800').innerText = data.body;
                        openEdit = false;
                    }
                })
            ">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <textarea x-model="editedBody" name="body" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                <div class="mt-2 space-x-2">
                    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit','class' => 'bg-custom-mid-blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'bg-custom-mid-blue']); ?>Save <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
                    <button type="button" @click="openEdit = false" class="text-gray-500">Cancel</button>
                </div>
            </form>
        </div>

        <div class="mt-2 flex items-center space-x-4 text-sm">
            <button @click="openReply = !openReply" class="text-gray-500 hover:text-gray-900">Reply</button>
            <?php if($comment->user->is(auth()->user())): ?>
                <button @click="openEdit = !openEdit" class="text-gray-500 hover:text-gray-900">Edit</button>
                <form @submit.prevent="
                    if (!confirm('Are you sure you want to delete this comment?')) return;
                    fetch('<?php echo e(route('shares.comments.destroy', ['share' => $comment->share, 'comment' => $comment])); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => {
                        if (response.ok) {
                            $el.closest('.flex.space-x-3').remove();
                        }
                    })
                ">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                </form>
            <?php endif; ?>
        </div>

        <div x-show="openReply" x-transition class="mt-2" style="display: none;" x-data="{ newReply: '' }">
            <form @submit.prevent="
                fetch('<?php echo e(route('shares.comments.store', $comment->share)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        body: newReply,
                        parent_id: <?php echo e($comment->id); ?>

                    })
                })
                .then(response => response.text())
                .then(html => {
                    let replySection = $el.parentElement.nextElementSibling;
                    replySection.insertAdjacentHTML('beforeend', html);
                    newReply = '';
                    openReply = false;
                })
            ">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="parent_id" value="<?php echo e($comment->id); ?>">
                <div class="flex items-center space-x-2">
                    <img src="<?php echo e(auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150'); ?>" alt="your avatar" class="h-8 w-8 rounded-full">
                    <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['xModel' => 'newReply','name' => 'body','class' => 'block w-full','placeholder' => 'Write a reply...','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-model' => 'newReply','name' => 'body','class' => 'block w-full','placeholder' => 'Write a reply...','required' => true]); ?>
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
                </div>
            </form>
        </div>

        <div class="mt-4 space-y-4 pl-8 border-l-2 border-gray-200">
            <?php $__currentLoopData = $comment->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalfe4855bb643954c83a0cbd6710da1102 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfe4855bb643954c83a0cbd6710da1102 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.comment','data' => ['comment' => $reply]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('comment'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['comment' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reply)]); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div><?php /**PATH C:\laragon\www\musicsocial\resources\views/components/comment.blade.php ENDPATH**/ ?>