<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One anonymous row per purged request (KTD9): enough to count what was
     * asked and sold, nothing that points back to a person (R19).
     */
    public function up(): void
    {
        Schema::create('request_stats', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->foreignId('cake_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20)->index();
            // The department only, never the commune.
            $table->string('department_code', 3)->nullable();
            $table->string('fulfilment', 10);
            $table->unsignedInteger('total_cents')->nullable();
            $table->boolean('paid');
            // Y-m: no date finer than the month.
            $table->string('event_month', 7)->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_stats');
    }
};
