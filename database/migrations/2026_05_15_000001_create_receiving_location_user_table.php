<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('receiving_location_user')) {
            return;
        }

        Schema::create('receiving_location_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['receiving_location_id', 'user_id'], 'receiving_location_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_location_user');
    }
};
