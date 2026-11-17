<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(isset($title) ? $title.' – ' : ''); ?>kanal19.pl – <?php echo e(__('Portal dla kierowców')); ?></title>
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600;700;800&display=swap">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-k19-paper font-sans text-k19-ink antialiased">

<header class="bg-k19-ink text-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $attributes = $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $component = $__componentOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>

        <div class="flex flex-wrap items-center gap-2.5">
            <form method="POST" action="<?php echo e(route('language')); ?>" class="flex items-center">
                <?php echo csrf_field(); ?>
                <label for="lang" class="sr-only"><?php echo e(__('Język')); ?></label>
                <select id="lang" name="locale" onchange="this.form.submit()"
                        class="min-h-11 rounded-xl border border-[#4A4F5A] bg-k19-ink px-3 font-bold text-white">
                    <?php $__currentLoopData = \App\Support\Languages::ALL; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($code); ?>" <?php if(app()->getLocale() === $code): echo 'selected'; endif; ?>><?php echo e(strtoupper($code)); ?> · <?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <noscript><button class="ml-2 underline">OK</button></noscript>
            </form>

            <?php if(auth()->guard()->check()): ?>
                <?php ($u = auth()->user()); ?>
                <?php if($u->isCompany()): ?>
                    <a href="<?php echo e(route('company.offers')); ?>" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline"><?php echo e(__('Moje oferty')); ?></a>
                <?php elseif($u->isDriver()): ?>
                    <a href="<?php echo e(route('driver.profile')); ?>" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline"><?php echo e(__('Mój profil')); ?></a>
                <?php elseif($u->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.index')); ?>" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline"><?php echo e(__('Panel admina')); ?></a>
                <?php elseif($u->isAdvertiser()): ?>
                    <a href="<?php echo e(route('advertiser.index')); ?>" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline"><?php echo e(__('Panel reklamodawcy')); ?></a>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="inline-flex min-h-11 items-center rounded-xl px-3 font-semibold text-[#C9CCD3] hover:text-white"><?php echo e(__('Wyloguj')); ?></button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="inline-flex min-h-11 items-center rounded-xl border border-[#4A4F5A] px-4 font-semibold text-white no-underline"><?php echo e(__('Zaloguj się')); ?></a>
                <a href="<?php echo e(route('register', ['konto' => 'company'])); ?>" class="k19-btn-red min-h-11 text-base"><?php echo e(__('Dodaj ogłoszenie za darmo')); ?></a>
            <?php endif; ?>
        </div>

        <nav aria-label="<?php echo e(__('Główne menu')); ?>" class="order-3 flex w-full flex-wrap gap-2.5 pt-1">
            <a href="<?php echo e(route('home')); ?>#oferty" class="k19-sign"><?php echo e(__('Oferty pracy')); ?></a>
            <a href="<?php echo e(route('home')); ?>#kategorie" class="k19-sign"><?php echo e(__('Kategorie')); ?></a>
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->isCompany()): ?>
                    <a href="<?php echo e(route('company.drivers')); ?>" class="k19-sign"><?php echo e(__('Baza kierowców')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo e(route('register', ['konto' => 'company'])); ?>" class="k19-sign"><?php echo e(__('Dla pracodawców')); ?></a>
        </nav>
    </div>
</header>

<?php if(session('status')): ?>
    <div role="status" class="mx-auto mt-6 max-w-6xl px-4 sm:px-6">
        <div class="rounded-xl border border-[#1E9E5A] bg-[#E8F6EE] px-4 py-3 font-semibold text-[#14532D]"><?php echo e(session('status')); ?></div>
    </div>
<?php endif; ?>

<main>
    <?php echo e($slot); ?>

</main>

<footer class="mt-20 bg-k19-ink text-[#C9CCD3]">
    <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-12 sm:px-6">
        <div class="flex flex-wrap justify-between gap-10">
            <div class="max-w-sm">
                <?php if (isset($component)) { $__componentOriginal987d96ec78ed1cf75b349e2e5981978f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.logo','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $attributes = $__attributesOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__attributesOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f)): ?>
<?php $component = $__componentOriginal987d96ec78ed1cf75b349e2e5981978f; ?>
<?php unset($__componentOriginal987d96ec78ed1cf75b349e2e5981978f); ?>
<?php endif; ?>
                <p class="mt-4 text-[15px] leading-relaxed"><?php echo e(__('Portal pracy dla kierowców zawodowych – od busa po ciągnik siodłowy.')); ?></p>
            </div>
            <nav aria-label="<?php echo e(__('Dla kierowców')); ?>" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-bold text-white"><?php echo e(__('Dla kierowców')); ?></span>
                <a href="<?php echo e(route('home')); ?>#oferty" class="text-[#C9CCD3] no-underline hover:text-white"><?php echo e(__('Oferty pracy')); ?></a>
                <a href="<?php echo e(route('register')); ?>" class="text-[#C9CCD3] no-underline hover:text-white"><?php echo e(__('Załóż konto za darmo')); ?></a>
            </nav>
            <nav aria-label="<?php echo e(__('Dla pracodawców')); ?>" class="flex flex-col gap-2.5 text-[15px]">
                <span class="font-bold text-white"><?php echo e(__('Dla pracodawców')); ?></span>
                <a href="<?php echo e(route('register', ['konto' => 'company'])); ?>" class="text-[#C9CCD3] no-underline hover:text-white"><?php echo e(__('Dodaj ogłoszenie')); ?></a>
                <a href="<?php echo e(route('register', ['konto' => 'advertiser'])); ?>" class="text-[#C9CCD3] no-underline hover:text-white"><?php echo e(__('Reklama dla marek')); ?></a>
            </nav>
        </div>
        <div class="flex flex-wrap justify-between gap-4 border-t border-[#2E323B] pt-5 text-[13px]">
            <span>© <?php echo e(date('Y')); ?> kanal19.pl</span>
            <span><?php echo e(__('Regulamin')); ?> · <?php echo e(__('Polityka prywatności')); ?></span>
        </div>
    </div>
</footer>
</body>
</html>
<?php /**PATH /home/claude/kanal19-build/dist/kanal19/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>