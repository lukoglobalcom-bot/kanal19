<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konta kanal19.pl: rola, język, telefon i blokada konta.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('driver')->index(); // driver, company, advertiser, admin
            $table->string('locale', 5)->default('pl');
            $table->string('phone', 32)->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'locale', 'phone', 'phone_verified_at', 'blocked_at']);
        });
    }
};
