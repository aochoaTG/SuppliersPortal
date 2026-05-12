<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        Schema::table('quotation_summaries', function (Blueprint $table) {
            $table->foreignId('rfq_id')->nullable()->after('requisition_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->after('selected_supplier_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('selected_by_user_id')->nullable()->after('requested_by_user_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('current_approver_user_id')->nullable()->after('selected_by_user_id')->constrained('users')->noActionOnDelete();
            $table->foreignId('authorizer_role_id')->nullable()->after('current_approver_user_id')->constrained('authorizer_roles')->nullOnDelete();
            $table->decimal('effective_authorization_limit', 14, 2)->nullable()->after('authorizer_role_id');
            $table->longText('approval_chain_snapshot')->nullable()->after('effective_authorization_limit');
            $table->text('resolution_notes')->nullable()->after('approval_chain_snapshot');
            $table->timestamp('budget_reserved_at')->nullable()->after('resolution_notes');
            $table->timestamp('budget_released_at')->nullable()->after('budget_reserved_at');
        });

        Schema::table('quotation_summaries', function (Blueprint $table) {
            $table->dropUnique('quotation_summaries_requisition_id_unique');
        });

        DB::table('quotation_summaries')
            ->whereNull('requested_by_user_id')
            ->update([
                'requested_by_user_id' => DB::raw('(select requested_by from requisitions where requisitions.id = quotation_summaries.requisition_id)'),
            ]);

        DB::table('quotation_summaries')
            ->whereNull('selected_by_user_id')
            ->update([
                'selected_by_user_id' => DB::raw('(select created_by from requisitions where requisitions.id = quotation_summaries.requisition_id)'),
            ]);

        if ($driver === 'sqlsrv') {
            DB::statement('
                UPDATE qs
                SET qs.rfq_id = src.rfq_id
                FROM quotation_summaries qs
                OUTER APPLY (
                    SELECT TOP 1 id AS rfq_id
                    FROM rfqs
                    WHERE rfqs.requisition_id = qs.requisition_id
                    ORDER BY rfqs.id ASC
                ) src
                WHERE qs.rfq_id IS NULL
            ');

            DB::statement('CREATE UNIQUE INDEX quotation_summaries_rfq_id_unique ON quotation_summaries (rfq_id) WHERE rfq_id IS NOT NULL');
        } else {
            DB::table('quotation_summaries')
                ->select('id', 'requisition_id')
                ->orderBy('id')
                ->get()
                ->each(function ($summary) {
                    $rfqId = DB::table('rfqs')
                        ->where('requisition_id', $summary->requisition_id)
                        ->orderBy('id')
                        ->value('id');

                    if ($rfqId) {
                        DB::table('quotation_summaries')
                            ->where('id', $summary->id)
                            ->update(['rfq_id' => $rfqId]);
                    }
                });

            if ($driver === 'sqlite') {
                DB::statement('CREATE UNIQUE INDEX quotation_summaries_rfq_id_unique ON quotation_summaries (rfq_id) WHERE rfq_id IS NOT NULL');
            } else {
                Schema::table('quotation_summaries', function (Blueprint $table) {
                    $table->unique('rfq_id', 'quotation_summaries_rfq_id_unique');
                });
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement('DROP INDEX quotation_summaries_rfq_id_unique ON quotation_summaries');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX quotation_summaries_rfq_id_unique');
        } else {
            Schema::table('quotation_summaries', function (Blueprint $table) {
                $table->dropUnique('quotation_summaries_rfq_id_unique');
            });
        }

        Schema::table('quotation_summaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rfq_id');
            $table->dropConstrainedForeignId('requested_by_user_id');
            $table->dropConstrainedForeignId('selected_by_user_id');
            $table->dropConstrainedForeignId('current_approver_user_id');
            $table->dropConstrainedForeignId('authorizer_role_id');
            $table->dropColumn([
                'effective_authorization_limit',
                'approval_chain_snapshot',
                'resolution_notes',
                'budget_reserved_at',
                'budget_released_at',
            ]);
        });

        Schema::table('quotation_summaries', function (Blueprint $table) {
            $table->unique('requisition_id');
        });
    }
};
