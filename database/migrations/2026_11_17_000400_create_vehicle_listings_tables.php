<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Giełda pojazdów (jak Otomoto): nowe, używane i poleasingowe. Ogłoszenie widoczne 30 dni.
     */
    public function up(): void
    {
        Schema::create('vehicle_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();        // ciagnik, naczepa, ciezarowy, dostawczy, autobus, osobowy, maszyna
            $table->string('condition', 20)->index();   // new, used, leasing
            $table->string('make', 60);
            $table->string('model', 80);
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->unsignedSmallInteger('power_hp')->nullable();
            $table->string('fuel', 20)->nullable();
            $table->string('gearbox', 20)->nullable();
            $table->string('euro', 10)->nullable();
            $table->unsignedInteger('price');
            $table->string('currency', 3)->default('PLN');
            $table->string('price_type', 10)->default('netto');
            $table->boolean('vat_invoice')->default(false);
            $table->string('city');
            $table->string('country', 2)->default('PL');
            $table->text('description');
            $table->string('phone', 30);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_listing_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_photos');
        Schema::dropIfExists('vehicle_listings');
    }
};
