<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('legal_name', 200)->nullable();
            $table->string('rfc', 13)->nullable();

            $table->string('locale', 10)->default('es_MX');
            $table->string('timezone', 50)->default('America/Mexico_City');
            $table->string('currency_code', 10)->default('MXN');

            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('domain', 150)->nullable();
            $table->string('website', 150)->nullable();

            $table->string('logo_path', 255)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
