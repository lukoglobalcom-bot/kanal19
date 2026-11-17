<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Oferty pracy (widoczne 30 dni, bez edycji po publikacji), aplikacje i zaproszenia z bazy kierowców.
     */
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // konto firmy
            $table->string('title');
            $table->string('category', 20)->index();
            $table->string('city');
            $table->string('country', 2)->default('PL');
            $table->unsignedInteger('salary_from')->nullable();
            $table->unsignedInteger('salary_to')->nullable();
            $table->string('currency', 3)->default('PLN');
            $table->string('salary_type', 10)->default('brutto'); // brutto, netto
            $table->string('contract', 20);
            $table->json('work_modes')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->boolean('no_cv')->default(false);
            $table->string('language', 5)->default('pl');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // kierowca
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['job_offer_id', 'user_id']);
        });

        Schema::create('contact_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_invitations');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_offers');
    }
};
