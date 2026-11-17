<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Dane przykładowe wgrywamy tylko lokalnie. Na produkcji baza startuje pusta,
     * a konto admina zakłada się komendą: php artisan kanal19:admin twoj@email.pl
     */
    public function run(): void
    {
        if (app()->environment('local', 'testing')) {
            $this->call(DemoSeeder::class);
        }
    }
}
