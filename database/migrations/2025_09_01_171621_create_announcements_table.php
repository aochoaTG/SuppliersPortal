<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50);
            $table->string('description', 500);
            $table->string('cover_path')->nullable();     // optional image path
            $table->dateTime('published_at');
            $table->dateTime('visible_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('priority')->default(2); // 1: Baja, 2: Normal, 3: Alta, 4: Urgente
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'published_at']);
            $table->index('visible_until');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
