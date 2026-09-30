<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cake_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 6)->unique();
            $table->string('kind', 10);
            $table->foreignId('cake_type_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('portions');
            $table->text('customization')->nullable();
            $table->date('event_date')->index();
            $table->string('fulfilment', 10);
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('delivery_fee_cents')->nullable();
            $table->text('allergies')->nullable();
            $table->timestamp('allergy_consent_at')->nullable();
            $table->string('name');
            $table->string('email');
            $table->text('phone');
            $table->string('source', 20);
            $table->boolean('out_of_lead_time')->default(false);
            $table->string('status', 20)->default('nouvelle')->index();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_requests');
    }
};
