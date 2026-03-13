<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Buscar y eliminar el CHECK CONSTRAINT existente en la columna status
        $constraint = DB::select("
            SELECT name
            FROM sys.check_constraints
            WHERE parent_object_id = OBJECT_ID('odc_direct_purchase_orders')
              AND OBJECT_DEFINITION(object_id) LIKE '%status%'
        ");

        if (! empty($constraint)) {
            DB::statement("ALTER TABLE odc_direct_purchase_orders DROP CONSTRAINT [{$constraint[0]->name}]");
        }

        // Recrear el constraint incluyendo PARTIALLY_RECEIVED y CLOSED_BY_INACTIVITY
        DB::statement("
            ALTER TABLE odc_direct_purchase_orders
            ADD CONSTRAINT chk_odc_status CHECK (status IN (
                'DRAFT',
                'PENDING_APPROVAL',
                'APPROVED',
                'REJECTED',
                'RETURNED',
                'ISSUED',
                'PARTIALLY_RECEIVED',
                'RECEIVED',
                'CANCELLED',
                'CLOSED_BY_INACTIVITY'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE odc_direct_purchase_orders DROP CONSTRAINT chk_odc_status");

        DB::statement("
            ALTER TABLE odc_direct_purchase_orders
            ADD CONSTRAINT chk_odc_status CHECK (status IN (
                'DRAFT',
                'PENDING_APPROVAL',
                'APPROVED',
                'REJECTED',
                'RETURNED',
                'ISSUED',
                'RECEIVED',
                'CANCELLED',
                'CLOSED_BY_INACTIVITY'
            ))
        ");
    }
};
