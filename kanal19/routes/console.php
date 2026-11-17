<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/*
 * Utworzenie konta administratora:
 *   php artisan kanal19:admin twoj@email.pl
 * Hasło zostanie wygenerowane i pokazane raz – zmień je po pierwszym logowaniu.
 */
Artisan::command('kanal19:admin {email}', function (string $email) {
    $password = Str::password(16);
    $user = User::firstOrNew(['email' => $email]);
    $user->name = $user->name ?: 'Administrator';
    $user->password = $password;
    $user->locale = $user->locale ?: 'pl';
    $user->role = 'admin';
    $user->phone_verified_at = now();
    $user->save();

    $this->info("Administrator: {$email}");
    $this->info("Hasło: {$password}");
})->purpose('Utwórz konto administratora kanal19.pl');
