<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->string('provider', 30);
            $table->string('provider_event_id');
            $table->string('event_type', 100);

            $table->json('payload');

            $table->timestamp('processed_at')->nullable();
            $table->text('failure_message')->nullable();

            $table->timestamps();

            $table->unique([
                'provider',
                'provider_event_id',
            ]);

            $table->index([
                'payment_id',
                'event_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
