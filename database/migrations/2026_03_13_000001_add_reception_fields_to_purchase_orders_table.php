<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columnas de recepción
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('receiving_location_id')->nullable()->after('quotation_summary_id');
            $table->unsignedBigInteger('received_by')->nullable()->after('created_by');
            $table->timestamp('received_at')->nullable()->after('closed_at');
            $table->timestamp('issued_at')->nullable()->after('approved_at');
            $table->text('reception_notes')->nullable()->after('received_at');

            $table->foreign('receiving_location_id')
                ->references('id')
                ->on('receiving_locations')
                ->nullOnDelete();

            $table->foreign('received_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // 2. Reemplazar el CHECK CONSTRAINT de status para agregar ISSUED y PARTIALLY_RECEIVED
        $constraint = DB::select("
            SELECT name
            FROM sys.check_constraints
            WHERE parent_object_id = OBJECT_ID('purchase_orders')
              AND OBJECT_DEFINITION(object_id) LIKE '%status%'
        ");

        if (!empty($constraint)) {
            DB::statement("ALTER TABLE purchase_orders DROP CONSTRAINT [{$constraint[0]->name}]");
        }

        DB::statement("
            ALTER TABLE purchase_orders
            ADD CONSTRAINT chk_po_status CHECK (status IN (
                'OPEN',
                'ISSUED',
                'PARTIALLY_RECEIVED',
                'RECEIVED',
                'CANCELLED',
                'PAID',
                'CLOSED_BY_INACTIVITY'
            ))
        ");
    }

    public function down(): void
    {
        // 1. Revertir el CHECK CONSTRAINT de status
        DB::statement("ALTER TABLE purchase_orders DROP CONSTRAINT chk_po_status");

        DB::statement("
            ALTER TABLE purchase_orders
            ADD CONSTRAINT chk_po_status CHECK (status IN (
                'OPEN',
                'RECEIVED',
                'CANCELLED',
                'PAID',
                'CLOSED_BY_INACTIVITY'
            ))
        ");

        // 2. Eliminar FK constraints antes de las columnas (requerido por SQL Server)
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['receiving_location_id']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['receiving_location_id', 'received_by', 'received_at', 'issued_at', 'reception_notes']);
        });
    }
};
