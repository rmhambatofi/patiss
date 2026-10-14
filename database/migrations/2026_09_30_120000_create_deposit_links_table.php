<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposit_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_request_id')->constrained()->cascadeOnDelete();
            // sha256 of the random token; the raw token only appears in the e-mailed URL (KTD4).
            $table->string('token_hash', 64)->unique();
            $table->unsignedInteger('total_cents');
            $table->unsignedInteger('deposit_cents');
            $table->json('allergens');
            $table->text('slot');
            $table->text('message')->nullable();
            $table->timestamp('expires_at');
            $table->string('status', 20)->default('active')->index();
            // Reserved for the Stripe Checkout session of U8.
            $table->string('stripe_session_id')->nullable();
            $table->timestamp('stripe_session_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_links');
    }
};
