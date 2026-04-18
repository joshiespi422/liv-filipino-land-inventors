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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');              // payable_type + payable_id
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('status_id')->constrained();
            $table->date('payment_date')->nullable();
            $table->unsignedBigInteger('amount');   // store in cents

            $table->string('gateway')->nullable();
            $table->string('gateway_payment_intent_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('meta')->nullable();

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
