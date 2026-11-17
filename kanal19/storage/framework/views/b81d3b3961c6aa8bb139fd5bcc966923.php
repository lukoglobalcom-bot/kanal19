<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <section class="bg-k19-ink pb-16 pt-12 text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-10 px-4 sm:px-6">
            <div class="flex min-w-0 flex-[999_1_520px] flex-col gap-6">
                <p class="text-sm font-bold uppercase tracking-[0.12em] text-[#C9CCD3]"><?php echo e(__('Portal pracy dla kierowców zawodowych')); ?></p>
                <h1 class="font-display text-[clamp(40px,6vw,72px)] font-extrabold leading-[0.95]">
                    <?php echo e(__('Praca za kierownicą.')); ?><br><span class="text-k19-red-light"><?php echo e(__('W każdej kategorii.')); ?></span>
                </h1>
                <p class="max-w-2xl text-lg leading-relaxed text-[#C9CCD3]"><?php echo e(__('Oferty dla kierowców kat. B, C, C+E i D, taksówkarzy, kurierów, kierowców ADR i budowlanych – w kraju i za granicą. Aplikuj w kilka sekund, także bez CV.')); ?></p>
            </div>
            <figure class="m-0 flex-[1_1_300px] rounded-3xl border-4 border-k19-red bg-k19-paper p-3">
                <img src="<?php echo e(asset('images/kanal19-logo.png')); ?>" alt="<?php echo e(__('Logo kanal19.pl – portal dla kierowców')); ?>" class="block h-auto w-full rounded-2xl">
            </figure>

            <form role="search" method="GET" action="<?php echo e(route('home')); ?>#oferty" class="flex w-full flex-wrap items-end gap-3 rounded-2xl bg-white p-4 text-k19-ink">
                <div class="flex-[2_1_240px]">
                    <label for="q" class="k19-label text-k19-muted"><?php echo e(__('Stanowisko lub słowo kluczowe')); ?></label>
                    <input id="q" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" class="k19-input" placeholder="<?php echo e(__('np. kierowca C+E chłodnia')); ?>">
                </div>
                <div class="flex-[1_1_180px]">
                    <label for="miasto" class="k19-label text-k19-muted"><?php echo e(__('Lokalizacja')); ?></label>
                    <input id="miasto" name="miasto" value="<?php echo e($filters['miasto'] ?? ''); ?>" class="k19-input" placeholder="<?php echo e(__('Miasto lub województwo')); ?>">
                </div>
                <div class="flex-[1_1_180px]">
                    <label for="kategoria" class="k19-label text-k19-muted"><?php echo e(__('Kategoria')); ?></label>
                    <select id="kategoria" name="kategoria" class="k19-input">
                        <option value=""><?php echo e(__('Wszystkie kategorie')); ?></option>
                        <?php $__currentLoopData = \App\Models\JobOffer::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php if(($filters['kategoria'] ?? '') === $key): echo 'selected'; endif; ?>><?php echo e(__($label)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <button class="k19-btn-dark"><?php echo e(__('Szukaj ofert')); ?></button>
            </form>
        </div>
    </section>

    
    <section id="kategorie" class="mx-auto max-w-6xl px-4 pt-14 sm:px-6">
        <h2 class="font-display text-[clamp(28px,3.4vw,40px)] font-extrabold"><?php echo e(__('Wybierz swoją kategorię')); ?></h2>
        <div class="mt-6 grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-3">
            <?php $__currentLoopData = \App\Models\JobOffer::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('home', ['kategoria' => $key])); ?>#oferty"
                   class="k19-card flex min-h-24 flex-col justify-between gap-3 p-4 no-underline hover:border-k19-red <?php echo e(($filters['kategoria'] ?? '') === $key ? 'border-2 border-k19-red' : ''); ?>">
                    <span class="k19-badge h-8 self-start px-2.5 text-base" aria-hidden="true"><?php echo e(['b' => 'B', 'c' => 'C', 'ce' => 'C+E', 'd' => 'D', 'taxi' => 'TAXI', 'kurier' => 'BUS', 'adr' => 'ADR', 'bud' => 'HDS', 'abroad' => 'EU', 'newbie' => 'START'][$key]); ?></span>
                    <span class="font-display text-lg font-bold text-k19-ink"><?php echo e(__($label)); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    
    <section id="oferty" class="mx-auto flex max-w-6xl flex-wrap items-start gap-8 px-4 pt-12 sm:px-6">
        <form method="GET" action="<?php echo e(route('home')); ?>#oferty" aria-label="<?php echo e(__('Filtry')); ?>" class="k19-card flex flex-[1_1_240px] flex-col gap-6 p-5">
            <input type="hidden" name="q" value="<?php echo e($filters['q'] ?? ''); ?>">
            <input type="hidden" name="miasto" value="<?php echo e($filters['miasto'] ?? ''); ?>">
            <input type="hidden" name="kategoria" value="<?php echo e($filters['kategoria'] ?? ''); ?>">
            <fieldset class="flex flex-col gap-2">
                <legend class="mb-2 font-display text-lg font-bold"><?php echo e(__('Rodzaj umowy')); ?></legend>
                <?php $__currentLoopData = \App\Models\JobOffer::CONTRACTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="umowa[]" value="<?php echo e($key); ?>" class="size-[18px] accent-k19-red" <?php if(in_array($key, $filters['umowa'] ?? [])): echo 'checked'; endif; ?>><?php echo e(__($label)); ?></label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </fieldset>
            <fieldset class="flex flex-col gap-2">
                <legend class="mb-2 font-display text-lg font-bold"><?php echo e(__('Tryb pracy')); ?></legend>
                <?php $__currentLoopData = \App\Models\JobOffer::WORK_MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="tryb[]" value="<?php echo e($key); ?>" class="size-[18px] accent-k19-red" <?php if(in_array($key, $filters['tryb'] ?? [])): echo 'checked'; endif; ?>><?php echo e(__($label)); ?></label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <label class="flex min-h-8 items-center gap-2.5"><input type="checkbox" name="bez_cv" value="1" class="size-[18px] accent-k19-red" <?php if(!empty($filters['bez_cv'])): echo 'checked'; endif; ?>><?php echo e(__('Aplikuj bez CV')); ?></label>
            </fieldset>
            <div>
                <label for="od" class="k19-label font-display text-lg"><?php echo e(__('Wynagrodzenie od')); ?></label>
                <input id="od" name="od" type="number" min="0" value="<?php echo e($filters['od'] ?? ''); ?>" class="k19-input" placeholder="<?php echo e(__('np. 9 000')); ?>">
            </div>
            <button class="k19-btn-red"><?php echo e(__('Pokaż oferty')); ?></button>
        </form>

        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-[clamp(26px,3vw,34px)] font-extrabold">
                        <?php echo e(!empty($filters['kategoria']) ? __(\App\Models\JobOffer::CATEGORIES[$filters['kategoria']]) : __('Wszystkie oferty')); ?>

                    </h2>
                    <p class="text-k19-muted"><?php echo e(__('Liczba ofert:')); ?> <?php echo e($offers->total()); ?></p>
                </div>
                <form method="GET" action="<?php echo e(route('home')); ?>#oferty" class="flex items-center gap-2">
                    <?php $__currentLoopData = request()->except('sort', 'page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(is_array($v)): ?> <?php $__currentLoopData = $v; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>[]" value="<?php echo e($vv); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?> <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"> <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label for="sort" class="text-sm font-semibold text-k19-muted"><?php echo e(__('Sortuj')); ?></label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="k19-input min-h-11 w-auto">
                        <option value="new" <?php if(($filters['sort'] ?? 'new') === 'new'): echo 'selected'; endif; ?>><?php echo e(__('Najnowsze')); ?></option>
                        <option value="pay" <?php if(($filters['sort'] ?? '') === 'pay'): echo 'selected'; endif; ?>><?php echo e(__('Najwyższe wynagrodzenie')); ?></option>
                    </select>
                </form>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if (isset($component)) { $__componentOriginal6975f59140d56771fba5780875b75367 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6975f59140d56771fba5780875b75367 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.offer-card','data' => ['offer' => $offer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('offer-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['offer' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($offer)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6975f59140d56771fba5780875b75367)): ?>
<?php $attributes = $__attributesOriginal6975f59140d56771fba5780875b75367; ?>
<?php unset($__attributesOriginal6975f59140d56771fba5780875b75367); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6975f59140d56771fba5780875b75367)): ?>
<?php $component = $__componentOriginal6975f59140d56771fba5780875b75367; ?>
<?php unset($__componentOriginal6975f59140d56771fba5780875b75367); ?>
<?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-2xl border border-dashed border-[#CFCFC7] bg-white p-10 text-center text-k19-muted"><?php echo e(__('Brak ofert w tej kategorii.')); ?></div>
            <?php endif; ?>

            <?php echo e($offers->links()); ?>

        </div>
    </section>

    
    <section class="mx-auto max-w-6xl px-4 pt-16 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl bg-k19-red p-8 text-white sm:p-12">
            <div class="max-w-xl">
                <span class="inline-flex rounded-md bg-k19-ink px-2.5 py-1 font-display text-sm font-extrabold uppercase tracking-wider"><?php echo e(__('Ogłoszenie za 0 zł')); ?></span>
                <h2 class="mt-3 font-display text-[clamp(28px,3.6vw,44px)] font-extrabold leading-tight"><?php echo e(__('Szukasz kierowców? Dodaj ogłoszenie za darmo')); ?></h2>
                <p class="mt-2 text-lg leading-relaxed"><?php echo e(__('Darmowe ogłoszenie w każdej kategorii, widoczne przez 30 dni. Po publikacji nie można go edytować, więc przed dodaniem sprawdzisz je w podglądzie.')); ?></p>
            </div>
            <a href="<?php echo e(route('register', ['konto' => 'company'])); ?>" class="k19-btn bg-white text-k19-ink hover:bg-k19-paper"><?php echo e(__('Dodaj darmowe ogłoszenie')); ?></a>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /home/claude/kanal19-build/dist/kanal19/resources/views/home.blade.php ENDPATH**/ ?>