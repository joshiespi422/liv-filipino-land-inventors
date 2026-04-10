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
        Schema::create('loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained();
            $table->integer('month_no');
            $table->decimal('beginning_balance', 10, 2);
            $table->decimal('interest_amount', 10, 2);
            $table->decimal('principal_amount', 10, 2);
            $table->decimal('total_payment', 10, 2);
            $table->decimal('ending_balance', 10, 2);
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_schedules');
    }
};
