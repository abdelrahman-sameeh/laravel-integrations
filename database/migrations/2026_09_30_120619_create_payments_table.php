<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->string('provider', 30);
            // Stripe PaymentIntent أو Paymob Intention
            $table->string('provider_payment_id')->nullable();

            // Stripe Checkout Session عند استخدام Checkout
            $table->string('provider_session_id')->nullable();

            // Paymob Transaction ID أو معرف المعاملة الفعلية
            $table->string('provider_transaction_id')->nullable();

            $table->unsignedBigInteger('amount');
            $table->char('currency', 3);
            $table->string('status', 30)
                ->default(PaymentStatus::Pending)
                ->index();

            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
