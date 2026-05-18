<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_cedulas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_category_id')
                ->constrained('expense_categories')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->string('name', 200);

            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('no action')
                ->onUpdate('no action');

            $table->softDeletes();
            $table->timestamps();

            $table->index('expense_category_id');
            $table->index('status');
            $table->index('deleted_at');
        });

        $this->seedGasomexCedulas();
    }

    private function seedGasomexCedulas(): void
    {
        $now = now();
        $createdBy = 1;

        $categoryIds = DB::table('expense_categories')->pluck('id', 'code');

        $cedulas = [
            ['code' => 'C', 'name' => 'Cumplimiento Sat Anexo 30 y 31'],
            ['code' => 'E', 'name' => 'Maxima'],
            ['code' => 'E', 'name' => 'Diesel'],
            ['code' => 'F', 'name' => 'Cedula de Operaciones (COA)'],
            ['code' => 'H', 'name' => 'Mantenimiento de Estaciones'],
            ['code' => 'H', 'name' => 'Dictamen de Calidad de los Petroliferos NOM-016-CRE-2016'],
            ['code' => 'H', 'name' => 'Dictamen Instalacion Electricas NOM-001-SEDE-2012'],
        ];

        foreach ($cedulas as $cedula) {
            $categoryId = $categoryIds[$cedula['code']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $exists = DB::table('budget_cedulas')
                ->where('expense_category_id', $categoryId)
                ->where('name', $cedula['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('budget_cedulas')->insert([
                'expense_category_id' => $categoryId,
                'name'                => $cedula['name'],
                'status'              => 'ACTIVO',
                'created_by'          => $createdBy,
                'updated_by'          => $createdBy,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_cedulas');
    }
};
