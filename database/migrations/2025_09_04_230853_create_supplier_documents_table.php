<?php

// database/migrations/2025_09_04_000000_create_supplier_documents_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('supplier_documents', function (Blueprint $table) {
            $table->id();

            // 🔧 NO CASCADE aquí para evitar multiple cascade paths
            $table->foreignId('supplier_id')
                ->constrained()                // por defecto a suppliers(id)
                ->noActionOnDelete();  // ✅ en SQL Server = ON DELETE NO ACTION

            // Estos pueden quedarse con SET NULL sin problema
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->string('doc_type', 50);
            $table->string('path_file');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('mime_type', 100)->nullable();

            $table->string('status', 20)->default('pending_review');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['supplier_id', 'doc_type']);
            $table->index(['status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('supplier_documents');
    }
};
