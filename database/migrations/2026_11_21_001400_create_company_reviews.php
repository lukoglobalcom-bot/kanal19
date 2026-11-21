<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Opinie kierowców o pracodawcach (jedna opinia kierowcy o firmie, firma może odpowiedzieć). */
    public function up(): void
    {
        Schema::create('company_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('pay')->nullable();
            $table->unsignedTinyInteger('vehicles')->nullable();
            $table->unsignedTinyInteger('atmosphere')->nullable();
            $table->boolean('worked_there')->default(true);
            $table->string('comment', 1000);
            $table->string('reply', 1000)->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->boolean('hidden')->default(false);
            $table->timestamps();
            $table->unique(['company_id', 'user_id']);
            $table->index(['company_id', 'hidden']);
        });

        Schema::create('company_review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['company_review_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_review_reports');
        Schema::dropIfExists('company_reviews');
    }
};
