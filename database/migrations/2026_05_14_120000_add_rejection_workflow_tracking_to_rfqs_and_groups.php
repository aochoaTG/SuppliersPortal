<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotation_groups', 'status')) {
            Schema::table('quotation_groups', function (Blueprint $table) {
                $table->string('status', 20)->default('ACTIVE')->after('notes');
                $table->timestamp('cancelled_at')->nullable()->after('status');
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->noActionOnDelete();
                $table->text('cancellation_reason')->nullable()->after('cancelled_by');
                $table->timestamp('rejected_at')->nullable()->after('cancellation_reason');
                $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->noActionOnDelete();
                $table->text('rejection_reason')->nullable()->after('rejected_by');
                $table->index('status');
            });
        }

        if (DB::getDriverName() === 'sqlsrv') {
            if ($this->sqlServerIndexExists('rfqs', 'rfqs_status_index')) {
                DB::statement('DROP INDEX rfqs_status_index ON rfqs');
            }

            $this->dropSqlServerCheckConstraints('rfqs', 'status');
        }

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('status', 40)->default('DRAFT')->change();
        });

        Schema::table('rfqs', function (Blueprint $table) {
            if (! Schema::hasColumn('rfqs', 'supersedes_rfq_id')) {
                $table->foreignId('supersedes_rfq_id')->nullable()->after('supplier_id')->constrained('rfqs')->noActionOnDelete();
            }

            if (! Schema::hasColumn('rfqs', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('cancelled_at');
            }

            if (! Schema::hasColumn('rfqs', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->noActionOnDelete();
            }

            if (! Schema::hasColumn('rfqs', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_by');
            }
        });

        if (! $this->sqlServerIndexExists('rfqs', 'rfqs_status_index')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlsrv') {
            if ($this->sqlServerIndexExists('rfqs', 'rfqs_status_index')) {
                DB::statement('DROP INDEX rfqs_status_index ON rfqs');
            }

            $this->dropSqlServerCheckConstraints('rfqs', 'status');
        }

        Schema::table('rfqs', function (Blueprint $table) {
            if (Schema::hasColumn('rfqs', 'supersedes_rfq_id')) {
                $table->dropConstrainedForeignId('supersedes_rfq_id');
            }

            if (Schema::hasColumn('rfqs', 'rejected_by')) {
                $table->dropConstrainedForeignId('rejected_by');
            }

            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('rfqs', 'rejected_at') ? 'rejected_at' : null,
                Schema::hasColumn('rfqs', 'rejection_reason') ? 'rejection_reason' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            $table->enum('status', [
                'DRAFT',
                'SENT',
                'RECEIVED',
                'EVALUATED',
                'COMPLETED',
                'CANCELLED',
            ])->default('DRAFT')->change();
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->index('status');
        });

        if (Schema::hasColumn('quotation_groups', 'status')) {
            Schema::table('quotation_groups', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropConstrainedForeignId('cancelled_by');
                $table->dropConstrainedForeignId('rejected_by');
                $table->dropColumn([
                    'status',
                    'cancelled_at',
                    'cancellation_reason',
                    'rejected_at',
                    'rejection_reason',
                ]);
            });
        }
    }

    private function dropSqlServerCheckConstraints(string $table, string $column): void
    {
        $constraints = DB::select(
            <<<'SQL'
            SELECT cc.name
            FROM sys.check_constraints cc
            INNER JOIN sys.columns c
                ON c.object_id = cc.parent_object_id
               AND cc.definition LIKE '%' + c.name + '%'
            WHERE cc.parent_object_id = OBJECT_ID(?)
              AND c.name = ?
            SQL,
            [$table, $column]
        );

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $table,
                $constraint->name
            ));
        }
    }

    private function sqlServerIndexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return false;
        }

        return (bool) DB::table('sys.indexes')
            ->where('object_id', DB::raw("OBJECT_ID('{$table}')"))
            ->where('name', $index)
            ->exists();
    }
};
