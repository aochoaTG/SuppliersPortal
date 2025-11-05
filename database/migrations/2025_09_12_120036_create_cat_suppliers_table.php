<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cat_suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identidad de origen (sin índice único)
            $table->string('source_system', 100);     // antes: software_origin
            $table->string('source_company', 150);    // antes: company
            $table->string('source_external_id', 64); // antes: external_id (string)

            // Datos generales
            $table->string('name', 200)->nullable();
            $table->string('rfc', 13)->index();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();

            // Contacto
            $table->string('email', 254)->nullable();
            $table->string('website', 200)->nullable();

            // Bancarios
            $table->string('bank', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('clabe', 18)->nullable();

            // Pago/Moneda
            $table->string('payment_method', 50)->nullable();
            $table->string('currency', 10)->nullable();

            // Clasificación y otros
            $table->string('category', 100)->nullable();
            $table->text('notes')->nullable();

            $table->boolean('active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            // (Opcional) índices simples para acelerar búsquedas por origen
            $table->index(['source_system', 'source_company']);
            $table->index('source_external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_suppliers');
    }
};
