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
        Schema::create('m_rooms', function (Blueprint $table) {
            $table->id();

            // Room Information
            $table->string('name', 100)->comment('Room name');
            $table->string('location', 100)->comment('Room location (e.g., Lantai 1)');
            $table->text('description')->nullable()->comment('Room description');
            $table->unsignedSmallInteger('capacity')->default(0)->comment('Room capacity (max people)');
            $table->boolean('is_maintenance')->default(false)->comment('Maintenance status');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('s_users')->onDelete('set null');

            // Indexes for performance
            $table->index('name');
            $table->index('location');
            $table->index('is_maintenance');
            $table->index(['deleted_at', 'is_maintenance']); // Composite index for filtering available rooms
        });

        // Set timezone to UTC for this table
        DB::statement('ALTER TABLE m_rooms MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE m_rooms MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE m_rooms MODIFY deleted_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_rooms');
    }
};
