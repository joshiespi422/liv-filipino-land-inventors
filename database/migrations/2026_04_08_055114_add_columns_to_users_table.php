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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->after('id')->default(4)->constrained('user_types')->onDelete('restrict');
            $table->boolean('is_active')->after('user_type_id')->default(true);
            $table->string('phone', 20)->unique()->nullable()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('gender', ['Male', 'Female', 'Other', 'Prefer not to say'])->nullable()->after('phone_verified_at');
            $table->string('region')->nullable()->after('gender');
            $table->string('province')->nullable()->after('region');
            $table->string('city')->nullable()->after('province');
            $table->string('barangay')->nullable()->after('city');
            $table->string('street')->nullable()->after('barangay');
            $table->string('postal_code', 20)->nullable()->after('street');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn([
                'user_type_id',
                'phone',
                'region',
                'province',
                'city',
                'barangay',
                'street',
                'postal_code',
            ]);
        });
    }
};
