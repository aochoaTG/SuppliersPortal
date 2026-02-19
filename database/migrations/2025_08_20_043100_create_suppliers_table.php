<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            // Relación 1:1 con users (cada proveedor corresponde a un usuario que se registra)
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('company_name', 150);
            $table->string('rfc', 13);
            $table->text('address');
            $table->string('phone_number', 15);
            $table->string('email', 100); // si quieres, puedes omitirlo y usar el de users; aquí lo dejo por si contabilidad exige correo “facturación”
            $table->string('contact_person', 100);
            $table->string('contact_phone', 10)->nullable();
            $table->string('supplier_type', 20);   // ej.: 'product', 'service', 'both'
            $table->string('tax_regime', 20);      // ej.: 'individual', 'corporation', 'resico'
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('clabe', 18)->nullable();
            $table->string('currency', 3)->default('MXN')->nullable();
            $table->string('default_payment_terms', 30)->default('CASH'); // Condiciones de pago por defecto (PaymentTerm enum)
            // 🔹 Nuevos campos internacionales
            $table->string('swift_bic', 11)->nullable();        // código SWIFT (8 o 11 chars)
            $table->string('iban', 34)->nullable();             // máximo 34 caracteres (ISO 13616)
            $table->string('bank_address', 255)->nullable();    // ciudad y país del banco
            $table->string('aba_routing', 9)->nullable();       // Routing number en EE.UU.
            $table->string('us_bank_name', 100)->nullable();

            $table->string('status')->default('pending_docs');

            $table->boolean('provides_specialized_services')->default(false)->after('status');
            $table->string('repse_registration_number')->nullable()->after('provides_specialized_services');
            $table->date('repse_expiry_date')->nullable()->after('repse_registration_number');
            $table->json('specialized_services_types')->nullable()->after('repse_expiry_date');
            $table->string('economic_activity', 150)->nullable(); // 👈 Nuevo campo abierto


            $table->timestamps();

            // Índices y reglas típicas en MX
            $table->unique('rfc');
            $table->index(['company_name']);
        });

        DB::statement("
            CREATE UNIQUE INDEX suppliers_account_number_unique
            ON suppliers(account_number)
            WHERE account_number IS NOT NULL
        ");

    }

    public function down(): void
    {
        // por higiene, intenta borrar el índice filtrado antes de tirar la tabla
        try {
            DB::statement("DROP INDEX suppliers_account_number_unique ON suppliers");
        } catch (\Throwable $e) {
            // ignore si no existe
        }
        Schema::dropIfExists('suppliers');
    }
};
