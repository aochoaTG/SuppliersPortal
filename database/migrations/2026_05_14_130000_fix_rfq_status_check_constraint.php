<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        $this->dropStatusConstraints();

        DB::statement(<<<'SQL'
            ALTER TABLE rfqs
            ADD CONSTRAINT CK_rfqs_status_allowed
            CHECK ([status] IN (N'DRAFT', N'SENT', N'RECEIVED', N'EVALUATED', N'COMPLETED', N'CANCELLED', N'REJECTED'))
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlsrv') {
            return;
        }

        DB::statement(<<<'SQL'
            IF EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_rfqs_status_allowed' AND parent_object_id = OBJECT_ID('rfqs'))
            ALTER TABLE rfqs DROP CONSTRAINT CK_rfqs_status_allowed
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE rfqs
            ADD CONSTRAINT CK_rfqs_status_allowed
            CHECK ([status] IN (N'DRAFT', N'SENT', N'RECEIVED', N'EVALUATED', N'COMPLETED', N'CANCELLED'))
        SQL);
    }

    private function dropStatusConstraints(): void
    {
        $constraints = DB::select(<<<'SQL'
            SELECT cc.name
            FROM sys.check_constraints cc
            WHERE cc.parent_object_id = OBJECT_ID('rfqs')
              AND cc.definition LIKE '%[status]%'
        SQL);

        foreach ($constraints as $constraint) {
            DB::statement('ALTER TABLE rfqs DROP CONSTRAINT '.$constraint->name);
        }
    }
};
