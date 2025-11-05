<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidents', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reporter_name',120);
            $table->string('reporter_email',190);
            $table->string('module',190);
            $table->enum('severity',['bloqueante','alta','media','baja']);
            $table->string('title',120);
            $table->text('steps');
            $table->text('expected');
            $table->text('actual');
            $table->enum('reproducibility',['siempre','frecuente','intermitente','raro']);
            $table->enum('impact',['todos','equipo','usuario']);
            $table->timestamp('happened_at')->nullable();
            $table->string('current_url',500)->nullable();
            $table->string('user_agent',1000)->nullable();
            $table->string('status',50)->default('nuevo');
            $table->string('image_path', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
