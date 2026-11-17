<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** CB Kanał 19 – krótkie wiadomości kierowców na kanałach krajów (widoczne 3 godziny). */
    public function up(): void
    {
        Schema::create('cb_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 2)->index();
            $table->string('road', 12)->nullable();
            $table->string('body', 280);
            $table->boolean('hidden')->default(false);
            $table->timestamps();
            $table->index(['channel', 'created_at']);
        });

        Schema::create('cb_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cb_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['cb_message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cb_reports');
        Schema::dropIfExists('cb_messages');
    }
};
