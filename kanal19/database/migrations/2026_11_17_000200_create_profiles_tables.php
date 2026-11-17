<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Osobne profile dla kierowcy, firmy i reklamodawcy.
     */
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('experience', 20); // none, lt1, 1-3, 3-5, 5-10, gt10
            $table->json('vehicles');          // na czym jeździł
            $table->json('licences');          // uprawnienia
            $table->boolean('in_database')->default(true); // zgoda na bazę kierowców
            $table->timestamps();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('nip', 10);
            $table->string('business_type', 40);
            $table->timestamps();
        });

        Schema::create('advertiser_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('nip', 10);
            $table->string('website')->nullable();
            $table->string('ad_format', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('sms_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_codes');
        Schema::dropIfExists('advertiser_profiles');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('driver_profiles');
    }
};
