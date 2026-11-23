<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('CM');
            $table->string('currency', 3)->default('XAF');
            $table->string('timezone')->default('Africa/Douala');
            $table->string('locale', 2)->default('fr');
            $table->string('status')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
