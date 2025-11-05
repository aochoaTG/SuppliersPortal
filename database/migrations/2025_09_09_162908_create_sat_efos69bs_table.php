<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sat_efos_69b', function (Blueprint $table) {
            $table->id(); // [id] INT IDENTITY(1,1) PRIMARY KEY
            $table->integer('number')->nullable(); // [number] INT NULL
            $table->string('rfc', 13)->nullable(); // [rfc] VARCHAR(13) NULL
            $table->string('company_name', 255)->nullable(); // [company_name] NVARCHAR(255) NULL
            $table->string('situation', 100)->nullable(); // [situation] NVARCHAR(100) NULL
            $table->string('sat_presumption_notice_date', 100)->nullable(); // NVARCHAR(100) NULL
            $table->date('sat_presumed_publication_date')->nullable(); // DATE NULL
            $table->string('dof_presumption_notice_date', 100)->nullable(); // NVARCHAR(100) NULL
            $table->date('dof_presumed_pub_date')->nullable(); // DATE NULL
            $table->date('sat_definitive_publication_date')->nullable(); // DATE NULL
            $table->date('dof_definitive_publication_date')->nullable(); // DATE NULL

            // Timestamps con reglas específicas del esquema original
            $table->dateTime('updated_at')->nullable(); // [updated_at] DATETIME NULL
            $table->dateTime('created_at')->useCurrent(); // [created_at] DATETIME NOT NULL (default NOW)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sat_efos_69b');
    }
};
