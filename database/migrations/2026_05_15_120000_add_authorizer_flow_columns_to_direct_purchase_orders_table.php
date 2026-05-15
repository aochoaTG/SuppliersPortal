<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->foreignId('authorizer_role_id')
                ->nullable()
                ->after('assigned_approver_id')
                ->constrained('authorizer_roles')
                ->noActionOnDelete();
            $table->decimal('effective_authorization_limit', 14, 2)
                ->nullable()
                ->after('authorizer_role_id');
            $table->longText('approval_chain_snapshot')
                ->nullable()
                ->after('effective_authorization_limit');
            $table->text('resolution_notes')
                ->nullable()
                ->after('approval_chain_snapshot');
            $table->timestamp('budget_reserved_at')
                ->nullable()
                ->after('resolution_notes');
            $table->timestamp('budget_released_at')
                ->nullable()
                ->after('budget_reserved_at');
        });
    }

    public function down(): void
    {
        Schema::table('odc_direct_purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('authorizer_role_id');
            $table->dropColumn([
                'effective_authorization_limit',
                'approval_chain_snapshot',
                'resolution_notes',
                'budget_reserved_at',
                'budget_released_at',
            ]);
        });
    }
};
