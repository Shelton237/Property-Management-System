<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operational backbone the dashboard reads from: rooms, stays, money and the
 * activity trail. Every table is scoped to a hotel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 12);
            $table->unsignedInteger('base_rate')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'code']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('number', 20);
            $table->string('floor', 20)->nullable();
            $table->string('status')->default('vacant_clean');
            $table->string('housekeeping_status')->default('clean');
            $table->boolean('is_sellable')->default(true);
            $table->timestamps();

            $table->unique(['hotel_id', 'number']);
            $table->index(['hotel_id', 'status']);
        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('country', 2)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'country']);
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20);
            $table->string('source')->default('direct_phone');
            $table->string('status')->default('confirmed');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('room_rate')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'code']);
            $table->index(['hotel_id', 'status']);
            $table->index(['hotel_id', 'check_in_date']);
            $table->index(['hotel_id', 'check_out_date']);
        });

        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('status')->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['hotel_id', 'code']);
            $table->index(['hotel_id', 'status']);
        });

        Schema::create('folio_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('department');
            $table->string('description');
            // Minor units of the hotel currency. Signed: a refund or a waived
            // charge is negative, which the department breakdown must show.
            $table->bigInteger('amount');
            $table->date('posted_on');
            $table->timestamps();

            $table->index(['hotel_id', 'posted_on']);
            $table->index(['hotel_id', 'department']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->bigInteger('amount');
            $table->date('received_on');
            $table->timestamps();

            $table->index(['hotel_id', 'received_on']);
            $table->index(['hotel_id', 'method']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->string('description');
            $table->string('reference', 40)->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('folio_lines');
        Schema::dropIfExists('folios');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
