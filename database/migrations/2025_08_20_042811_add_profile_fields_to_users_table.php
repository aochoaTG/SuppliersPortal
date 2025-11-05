<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('email');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->boolean('is_active')->default(true)->after('password');
            $table->string('avatar')->nullable()->after('is_active');
            $table->string('phone', 30)->nullable()->index()->after('avatar');
            $table->string('job_title', 100)->nullable()->after('phone');
            $table->dateTimeTz('last_login')->nullable()->after('remember_token'); // mapea a datetime2 en SQL Server
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name','last_name','is_active','avatar','phone','job_title','last_login']);
        });
    }
};
