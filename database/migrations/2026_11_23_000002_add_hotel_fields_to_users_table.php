<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null for super admins, who are not attached to a single hotel.
            $table->foreignId('hotel_id')->nullable()->after('id')
                ->constrained('hotels')->nullOnDelete();
            $table->string('role')->default('receptionist')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('locale', 2)->default('fr')->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropColumn([
                'hotel_id', 'role', 'is_active', 'last_login_at', 'last_login_ip', 'locale',
            ]);
        });
    }
};
