<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kampanie reklamodawców: na każdy wybrany język jedna reklama i jeden artykuł.
     * Po akceptacji przez reklamodawcę kampania jest zablokowana – zmienia ją tylko admin.
     * Na stronie pokazuje się dopiero, gdy admin ją włączy (np. po opłaceniu).
     */
    public function up(): void
    {
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('format', 20);              // billboard, box
            $table->string('target_url');
            $table->json('languages');
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        Schema::create('ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('headline', 80);
            $table->string('text', 200);
            $table->string('image')->nullable();
            $table->string('article_title', 150);
            $table->text('article_body');
            $table->timestamps();
            $table->unique(['ad_campaign_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_creatives');
        Schema::dropIfExists('ad_campaigns');
    }
};
