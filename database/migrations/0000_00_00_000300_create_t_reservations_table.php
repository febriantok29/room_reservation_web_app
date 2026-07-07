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
        Schema::create('t_reservations', function (Blueprint $table) {
            // Custom primary key (generated in code, not auto-increment)
            $table->string('id', 20)->primary()->comment('Format: RSV-YYYYMMDD-XX');

            // Reservation Information
            $table->string('user_id', 36)->comment('User who made the reservation');
            $table->string('room_id', 36)->comment('Room being reserved');
            $table->timestamp('start_time')->comment('Reservation start time (UTC)');
            $table->timestamp('end_time')->comment('Reservation end time (UTC)');
            $table->text('purpose')->nullable()->comment('Purpose of reservation');
            $table->unsignedSmallInteger('visitor_count')->default(1)->comment('Number of visitors');
            $table->boolean('with_snack')->default(false)->comment('Snack/refreshments requested');
            $table->boolean('with_lunch')->default(false)->comment('Lunch/meal requested');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])
                ->default('pending')
                ->comment('Reservation status');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 36)->nullable();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('s_users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('m_rooms')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('s_users')->onDelete('set null');

            // Critical indexes for CSP (Constraint Satisfaction Problem) checking
            // These indexes are crucial for checking time-slot conflicts efficiently
            $table->index(['room_id', 'start_time', 'end_time', 'status', 'deleted_at'], 'idx_csp_conflict_check');
            $table->index(['user_id', 'status', 'deleted_at'], 'idx_user_reservations');
            $table->index(['status', 'start_time', 'deleted_at'], 'idx_status_time');
            $table->index('start_time');
            $table->index('end_time');
        });

        // Set timezone to UTC for this table
        DB::statement('ALTER TABLE t_reservations MODIFY start_time TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE t_reservations MODIFY end_time TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE t_reservations MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE t_reservations MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE t_reservations MODIFY deleted_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_reservations');
    }
};
