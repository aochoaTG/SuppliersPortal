<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadir columnas de seguimiento de inactividad
        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('received_at');
            $table->timestamp('inactivity_warning_sent_at')->nullable()->after('closed_at');
        });

        // 2. En SQL Server el ENUM se implementa como VARCHAR + CHECK CONSTRAINT.
        //    Eliminamos el constraint existente y creamos uno nuevo con el valor adicional.
        $constraint = DB::select("
            SELECT name
            FROM sys.check_constraints
            WHERE parent_object_id = OBJECT_ID('odc_direct_purchase_orders')
              AND OBJECT_DEFINITION(object_id) LIKE '%status%'
        ");

        if (!empty($constraint)) {
            DB::statement("ALTER TABLE odc_direct_purchase_orders DROP CONSTRAINT [{$constraint[0]->name}]");
        }

        DB::statement("
            ALTER TABLE odc_direct_purchase_orders
            ADD CONSTRAINT chk_odc_dpo_status CHECK (status IN (
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

    public function down(): void
    {
        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'inactivity_warning_sent_at']);
        });

        DB::statement("ALTER TABLE odc_direct_purchase_orders DROP CONSTRAINT chk_odc_dpo_status");

        DB::statement("
            ALTER TABLE odc_direct_purchase_orders
            ADD CONSTRAINT chk_odc_dpo_status_orig CHECK (status IN (
                'DRAFT',
                'PENDING_APPROVAL',
                'APPROVED',
                'REJECTED',
                'RETURNED',
                'ISSUED',
                'RECEIVED',
                'CANCELLED'
            ))
        ");
    }
};
