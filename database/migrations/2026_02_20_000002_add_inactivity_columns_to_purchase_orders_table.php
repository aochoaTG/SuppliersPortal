<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadir columnas de seguimiento
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('approved_at');
            $table->timestamp('inactivity_warning_sent_at')->nullable()->after('closed_at');
        });

        // 2. Actualizar CHECK CONSTRAINT del status en SQL Server
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
                'RECEIVED',
                'CANCELLED',
                'PAID',
                'CLOSED_BY_INACTIVITY'
            ))
        ");
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'closed_at', 'inactivity_warning_sent_at']);
        });

        DB::statement("ALTER TABLE purchase_orders DROP CONSTRAINT chk_po_status");

        DB::statement("
            ALTER TABLE purchase_orders
            ADD CONSTRAINT chk_po_status_orig CHECK (status IN (
                'OPEN',
                'RECEIVED',
                'CANCELLED',
                'PAID'
            ))
        ");
    }
};
