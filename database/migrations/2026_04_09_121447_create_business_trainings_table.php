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
        Schema::create('business_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_training_category_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('module'); 
            $table->json('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trainings');
    }
};
