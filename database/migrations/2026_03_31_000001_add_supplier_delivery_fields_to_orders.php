<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos de entrega de proveedor a las tablas de órdenes de compra.
 *
 * Cuando un proveedor registra su entrega física (remisión) antes de que la
 * estación capture la recepción, la OC pasa al estatus DELIVERED_PENDING_RECEPTION.
 * Se inicia un contador de 3 días hábiles con alertas escalonadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- purchase_orders: agregar nuevo valor al enum de status ---
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM(
            'OPEN','ISSUED','PARTIALLY_RECEIVED','RECEIVED',
            'CANCELLED','PAID','CLOSED_BY_INACTIVITY',
            'DELIVERED_PENDING_RECEPTION'
        ) NOT NULL DEFAULT 'OPEN'");

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dateTime('supplier_delivered_at')->nullable()->after('received_at')
                  ->comment('Fecha en que el proveedor reportó la entrega física');
            $table->dateTime('reception_deadline_at')->nullable()->after('supplier_delivered_at')
                  ->comment('Fecha límite (3 días hábiles) para que la estación capture la recepción');
            $table->string('physical_receiver_name', 150)->nullable()->after('reception_deadline_at')
                  ->comment('Nombre de quien recibió físicamente en la estación');
            $table->text('delivery_observations')->nullable()->after('physical_receiver_name')
                  ->comment('Observaciones del proveedor al momento de la entrega');
        });

        // --- odc_direct_purchase_orders: agregar nuevo valor al enum de status ---
        DB::statement("ALTER TABLE odc_direct_purchase_orders MODIFY COLUMN status ENUM(
            'DRAFT','PENDING_APPROVAL','APPROVED','REJECTED','RETURNED',
            'ISSUED','PARTIALLY_RECEIVED','RECEIVED',
            'CANCELLED','CLOSED_BY_INACTIVITY',
            'DELIVERED_PENDING_RECEPTION'
        ) NOT NULL DEFAULT 'DRAFT'");

        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->dateTime('supplier_delivered_at')->nullable()->after('received_at')
                  ->comment('Fecha en que el proveedor reportó la entrega física');
            $table->dateTime('reception_deadline_at')->nullable()->after('supplier_delivered_at')
                  ->comment('Fecha límite (3 días hábiles) para que la estación capture la recepción');
            $table->string('physical_receiver_name', 150)->nullable()->after('reception_deadline_at')
                  ->comment('Nombre de quien recibió físicamente en la estación');
            $table->text('delivery_observations')->nullable()->after('physical_receiver_name')
                  ->comment('Observaciones del proveedor al momento de la entrega');
        });
    }

    public function down(): void
    {
        // --- purchase_orders: revertir ---
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_delivered_at',
                'reception_deadline_at',
                'physical_receiver_name',
                'delivery_observations',
            ]);
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM(
            'OPEN','ISSUED','PARTIALLY_RECEIVED','RECEIVED',
            'CANCELLED','PAID','CLOSED_BY_INACTIVITY'
        ) NOT NULL DEFAULT 'OPEN'");

        // --- odc_direct_purchase_orders: revertir ---
        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_delivered_at',
                'reception_deadline_at',
                'physical_receiver_name',
                'delivery_observations',
            ]);
        });

        DB::statement("ALTER TABLE odc_direct_purchase_orders MODIFY COLUMN status ENUM(
            'DRAFT','PENDING_APPROVAL','APPROVED','REJECTED','RETURNED',
            'ISSUED','PARTIALLY_RECEIVED','RECEIVED',
            'CANCELLED','CLOSED_BY_INACTIVITY'
        ) NOT NULL DEFAULT 'DRAFT'");
    }
};
