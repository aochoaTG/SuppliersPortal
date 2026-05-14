<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('status', 40)->default('DRAFT')->change();
            $table->foreignId('supersedes_rfq_id')->nullable()->after('supplier_id')->constrained('rfqs')->noActionOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('cancelled_at');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->noActionOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supersedes_rfq_id');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'rejected_at',
                'rejection_reason',
            ]);
            $table->enum('status', [
                'DRAFT',
                'SENT',
                'RECEIVED',
                'EVALUATED',
                'COMPLETED',
                'CANCELLED',
            ])->default('DRAFT')->change();
        });

        Schema::table('quotation_groups', function (Blueprint $table) {
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
};
