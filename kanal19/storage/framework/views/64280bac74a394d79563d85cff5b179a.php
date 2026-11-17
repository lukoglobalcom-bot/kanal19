<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tagline' => true, 'dark' => true]));

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

foreach (array_filter((['tagline' => true, 'dark' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-2.5 no-underline <?php echo e($dark ? 'text-white' : 'text-k19-ink'); ?>" aria-label="kanal19.pl – <?php echo e(__('Strona główna')); ?>">
    <span class="k19-badge h-[34px] w-[50px] text-2xl" aria-hidden="true">19</span>
    <span class="inline-flex flex-col gap-[9px]">
        <span class="font-display text-[28px] font-extrabold leading-none">kanal19<span class="text-k19-red-light">.pl</span></span>
        <?php if($tagline): ?>
            <span class="font-display text-[10px] font-semibold uppercase leading-none tracking-[0.28em] text-[#BDBDBD]"><?php echo e(__('Portal dla kierowców')); ?></span>
        <?php endif; ?>
    </span>
</a>
<?php /**PATH /home/claude/kanal19-build/dist/kanal19/resources/views/components/logo.blade.php ENDPATH**/ ?>