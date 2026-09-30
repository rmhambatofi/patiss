<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // Nullable so that purging a request (U9) keeps the accounting trace of the payment.
            $table->foreignId('deposit_link_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cake_request_id')->nullable()->constrained()->nullOnDelete();
            // One row per Checkout session: a replayed webhook cannot record or reserve twice (KTD5).
            $table->string('stripe_session_id')->unique();
            $table->string('payment_intent_id')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->string('status', 20);
            $table->string('refund_id')->nullable();
            // Personal data, kept apart so that the purge (U9) can blank it alone.
            $table->text('payer_email')->nullable();
            $table->timestamps();
        });

        // Delivery address typed on the Stripe page, encrypted like the rest of the request (R19).
        Schema::table('cake_requests', function (Blueprint $table) {
            $table->text('delivery_name')->nullable();
            $table->text('delivery_line1')->nullable();
            $table->text('delivery_line2')->nullable();
            $table->text('delivery_postal_code')->nullable();
            $table->text('delivery_city')->nullable();
            $table->boolean('delivery_commune_mismatch')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('cake_requests', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_name',
                'delivery_line1',
                'delivery_line2',
                'delivery_postal_code',
                'delivery_city',
                'delivery_commune_mismatch',
            ]);
        });

        Schema::dropIfExists('payments');
    }
};
