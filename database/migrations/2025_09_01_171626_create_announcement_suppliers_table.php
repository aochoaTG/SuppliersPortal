<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcement_supplier', function (Blueprint $table) {
            $table->id();

            $table->foreignId('announcement_id')
                  ->constrained('announcements')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            // Tracking
            $table->dateTime('first_viewed_at')->nullable();
            $table->dateTime('last_viewed_at')->nullable();
            $table->boolean('is_dismissed')->default(false);  // "No mostrar más"
            $table->dateTime('dismissed_at')->nullable();

            $table->timestamps();

            $table->unique(['announcement_id', 'supplier_id']);
            $table->index(['supplier_id', 'is_dismissed']);
            $table->index('last_viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_supplier');
    }
};
