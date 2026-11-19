<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Potwierdzenia utrudnień przez mijających kierowców („Nadal jest?” – tak / nie ma). */
    public function up(): void
    {
        Schema::create('map_report_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('still');
            $table->timestamps();
            $table->unique(['map_report_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_report_votes');
    }
};
