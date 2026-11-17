<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Aktualności i poradniki dodawane przez administratora, osobno dla każdego języka. */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20)->index();
            $table->string('locale', 5)->index();
            $table->string('title', 180);
            $table->string('excerpt', 300)->nullable();
            $table->text('body');
            $table->string('image')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
