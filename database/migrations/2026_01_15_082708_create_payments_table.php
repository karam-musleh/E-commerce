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

            // Relations
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Internal reference
            $table->ulid('payment_reference')->unique();

            // Amount
            $table->bigInteger('amount');
            $table->string('currency', 10)->default('USD');

            // Method & gateway
            $table->string('payment_method');
            $table->string('payment_gateway')->nullable();

            // Status
            $table->string('status')->default('pending');

            // Gateway data
            $table->string('transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Debug
            $table->json('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
