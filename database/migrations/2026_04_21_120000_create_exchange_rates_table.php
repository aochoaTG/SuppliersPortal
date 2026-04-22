<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('currency_from', 3);
            $table->char('currency_to', 3);
            $table->decimal('rate', 10, 4);
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['currency_from', 'currency_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
