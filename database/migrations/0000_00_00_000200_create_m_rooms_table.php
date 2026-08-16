<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('m_rooms', function (Blueprint $table) {
            // Custom string ID (generated in code, not auto-increment)
            $table->string('id', 36)->primary()->comment('Format: RM-LLXX');

            // Room Information
            $table->string('name', 100)->comment('Room name');
            $table->unsignedTinyInteger('floor')->comment('Floor number (1-99)');
            $table->text('description')->nullable()->comment('Room description');
            $table->string('image_path', 500)->nullable()->comment('Storage path of room image');
            $table->unsignedSmallInteger('capacity')->default(0)->comment('Room capacity (max people)');
            $table->boolean('is_maintenance')->default(false)->comment('Maintenance status');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 36)->nullable();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('s_users')->onDelete('set null');

            // Indexes for performance
            $table->unique('name');
            $table->index('floor');
            $table->index('is_maintenance');
            $table->index(['deleted_at', 'is_maintenance']); // Composite index for filtering available rooms
        });

        // Set timezone to UTC for this table
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE m_rooms MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE m_rooms MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE m_rooms MODIFY deleted_at TIMESTAMP NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_rooms');
    }
};
