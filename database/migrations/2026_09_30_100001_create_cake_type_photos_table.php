<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cake_type_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_type_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_type_photos');
    }
};
