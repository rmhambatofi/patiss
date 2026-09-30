<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('insee_code', 5)->unique();
            $table->string('name');
            $table->string('search_name')->index();
            $table->json('postal_codes');
            $table->string('department_code', 3);
            $table->decimal('latitude', 8, 5);
            $table->decimal('longitude', 8, 5);
            $table->decimal('distance_km', 6, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
