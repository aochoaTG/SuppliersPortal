<?php

// database/migrations/2025_10_13_000000_create_taxes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            // porcentaje 0..100 con 2 decimales
            $table->decimal('rate_percent', 5, 2);
            $table->boolean('is_active')->default(true); // en SQL Server será bit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
