<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zgłoszenia kierowców na mapie: wolne miejsca na parkingach, ceny paliwa i wezwania SOS.
     * Każde zgłoszenie wygasa samo (parking 6 h, paliwo 7 dni, SOS 6 h).
     */
    public function up(): void
    {
        Schema::create('map_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10)->index();      // parking, fuel, sos
            $table->decimal('lat', 9, 6);
            $table->decimal('lng', 9, 6);
            $table->string('name', 120)->nullable();  // nazwa parkingu / stacji
            $table->string('country', 2)->default('PL');
            $table->json('data');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        Schema::create('sos_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['map_report_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sos_responses');
        Schema::dropIfExists('map_reports');
    }
};
