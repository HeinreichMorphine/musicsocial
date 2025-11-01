<?php if (isset($component)) { $__componentOriginaleb110d187bacbd2efbc61217697b3215 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaleb110d187bacbd2efbc61217697b3215 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sidebar-right','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sidebar-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
<?php /**PATH C:\laragon\www\musicsocial-main\resources\views/layouts/sidebar-right.blade.php ENDPATH**/ ?>