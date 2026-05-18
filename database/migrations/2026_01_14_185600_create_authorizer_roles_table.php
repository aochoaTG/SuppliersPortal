<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorizer_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->decimal('approval_limit', 14, 2)->nullable();
            $table->string('matrix_sheet', 100)->nullable();
            $table->string('matrix_reference', 100)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorizer_roles');
    }
};
