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
        Schema::create('m_room_facilities', function (Blueprint $table) {
            $table->string('room_id', 36);
            $table->string('facility_id', 36);
            $table->timestamps();

            $table->primary(['room_id', 'facility_id']);
            $table->foreign('room_id')->references('id')->on('m_rooms')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('m_facilities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_room_facilities');
    }
};
