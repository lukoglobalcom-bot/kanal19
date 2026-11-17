<?php
    use App\Http\Controllers\AuthController as A;
    $tabs = ['driver' => __('Kierowca'), 'company' => __('Firma – dodaję ogłoszenia'), 'advertiser' => __('Reklamodawca')];
    $oldArr = fn ($k) => (array) old($k, []);
?>
<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('Załóż konto')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Załóż konto'))]); ?>
    <div class="mx-auto max-w-2xl px-4 pt-12 sm:px-6">
        <h1 class="font-display text-[clamp(30px,4vw,42px)] font-extrabold"><?php echo e(__('Załóż konto za darmo')); ?></h1>

        <nav class="mt-5 flex flex-wrap gap-2" aria-label="<?php echo e(__('Rodzaj konta')); ?>">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('register', ['konto' => $key])); ?>"
                   class="k19-sign <?php echo e($role === $key ? '' : 'bg-k19-ink opacity-80 hover:opacity-100'); ?>"
                   <?php if($role === $key): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <form method="POST" action="<?php echo e(route('register')); ?>" class="k19-card mt-5 flex flex-col gap-5 p-6 sm:p-8">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="role" value="<?php echo e($role); ?>">

            <?php if($role === 'driver'): ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="k19-label"><?php echo e(__('Imię')); ?></label>
                        <input id="first_name" name="first_name" value="<?php echo e(old('first_name')); ?>" required class="k19-input">
                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="last_name" class="k19-label"><?php echo e(__('Nazwisko')); ?></label>
                        <input id="last_name" name="last_name" value="<?php echo e(old('last_name')); ?>" required class="k19-input">
                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div>
                    <label for="experience" class="k19-label"><?php echo e(__('Staż jako kierowca')); ?></label>
                    <select id="experience" name="experience" required class="k19-input">
                        <?php $__currentLoopData = A::EXPERIENCE; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('experience') === $k): echo 'selected'; endif; ?>><?php echo e(__($l)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['experience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <fieldset>
                    <legend class="k19-label"><?php echo e(__('Na czym jeździłeś')); ?></legend>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = A::VEHICLES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="k19-pill"><input type="checkbox" name="vehicles[]" value="<?php echo e($k); ?>" class="sr-only" <?php if(in_array($k, $oldArr('vehicles'))): echo 'checked'; endif; ?>><span><?php echo e(__($l)); ?></span></label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['vehicles'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </fieldset>
                <fieldset>
                    <legend class="k19-label"><?php echo e(__('Uprawnienia')); ?></legend>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = A::LICENCES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="k19-pill"><input type="checkbox" name="licences[]" value="<?php echo e($l); ?>" class="sr-only" <?php if(in_array($l, $oldArr('licences'))): echo 'checked'; endif; ?>><span><?php echo e($l); ?></span></label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php $__errorArgs = ['licences'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </fieldset>
                <label class="flex items-start gap-2.5"><input type="checkbox" name="in_database" value="1" class="mt-1 size-[18px] accent-k19-red" <?php if(old('in_database')): echo 'checked'; endif; ?>>
                    <span><?php echo e(__('Dodaj mnie do bazy kierowców. Firmy zobaczą moje imię, staż, pojazdy i uprawnienia (bez telefonu i e-maila) i mogą wysłać mi zaproszenie.')); ?></span></label>
            <?php elseif($role === 'company'): ?>
                <div>
                    <label for="company_name" class="k19-label"><?php echo e(__('Nazwa firmy')); ?></label>
                    <input id="company_name" name="company_name" value="<?php echo e(old('company_name')); ?>" required class="k19-input">
                    <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nip" class="k19-label"><?php echo e(__('NIP')); ?></label>
                        <input id="nip" name="nip" value="<?php echo e(old('nip')); ?>" required inputmode="numeric" maxlength="10" class="k19-input">
                        <?php $__errorArgs = ['nip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="business_type" class="k19-label"><?php echo e(__('Rodzaj działalności')); ?></label>
                        <select id="business_type" name="business_type" required class="k19-input">
                            <?php $__currentLoopData = A::BUSINESS_TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('business_type') === $k): echo 'selected'; endif; ?>><?php echo e(__($l)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            <?php else: ?>
                <div>
                    <label for="brand" class="k19-label"><?php echo e(__('Marka / nazwa firmy')); ?></label>
                    <input id="brand" name="brand" value="<?php echo e(old('brand')); ?>" required class="k19-input">
                    <?php $__errorArgs = ['brand'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nip" class="k19-label"><?php echo e(__('NIP')); ?></label>
                        <input id="nip" name="nip" value="<?php echo e(old('nip')); ?>" required inputmode="numeric" maxlength="10" class="k19-input">
                        <?php $__errorArgs = ['nip'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="website" class="k19-label"><?php echo e(__('Strona www')); ?></label>
                        <input id="website" name="website" type="url" value="<?php echo e(old('website')); ?>" placeholder="https://" class="k19-input">
                        <?php $__errorArgs = ['website'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
                <div>
                    <label for="ad_format" class="k19-label"><?php echo e(__('Interesująca forma reklamy')); ?></label>
                    <select id="ad_format" name="ad_format" class="k19-input">
                        <?php $__currentLoopData = A::AD_FORMATS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('ad_format') === $k): echo 'selected'; endif; ?>><?php echo e(__($l)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            <?php endif; ?>

            <hr class="border-[#E2E0DA]">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="k19-label"><?php echo e(__('Telefon (przyjdzie kod SMS)')); ?></label>
                    <input id="phone" name="phone" type="tel" value="<?php echo e(old('phone')); ?>" required placeholder="+48 600 000 000" autocomplete="tel" class="k19-input">
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="locale" class="k19-label"><?php echo e(__('Język konta i aplikacji')); ?></label>
                    <select id="locale" name="locale" required class="k19-input">
                        <?php $__currentLoopData = \App\Support\Languages::ALL; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php if(old('locale', app()->getLocale()) === $code): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div>
                <label for="email" class="k19-label"><?php echo e(__('E-mail')); ?></label>
                <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" class="k19-input">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="k19-label"><?php echo e(__('Hasło (min. 8 znaków)')); ?></label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="k19-input">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="password_confirmation" class="k19-label"><?php echo e(__('Powtórz hasło')); ?></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="k19-input">
                </div>
            </div>
            <label class="flex items-start gap-2.5"><input type="checkbox" name="terms" value="1" required class="mt-1 size-[18px] accent-k19-red">
                <span><?php echo e(__('Akceptuję regulamin i politykę prywatności.')); ?></span></label>
            <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="k19-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <button class="k19-btn-red"><?php echo e(__('Załóż konto i wyślij kod SMS')); ?></button>
            <p class="text-center text-k19-muted"><?php echo e(__('Masz już konto?')); ?> <a href="<?php echo e(route('login')); ?>" class="font-semibold text-k19-red"><?php echo e(__('Zaloguj się')); ?></a></p>
        </form>
    </div>
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
<?php /**PATH /home/claude/kanal19-build/dist/kanal19/resources/views/auth/register.blade.php ENDPATH**/ ?>