<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('status_id')->constrained();
            $table->date('payment_date')->nullable();
            $table->unsignedBigInteger('amount');

            $table->string('gateway')->nullable();
            $table->string('gateway_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('gateway_payment_intent_id')->nullable()->unique();
            $table->string('gateway_payment_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('meta')->nullable();

            $table->uuid('idempotency_key')->nullable()->unique();

            $table->timestamps();
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
