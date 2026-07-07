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
        Schema::create('t_room_complaints', function (Blueprint $table) {
            // Custom primary key (generated in code, not auto-increment)
            $table->string('id', 25)->primary()->comment('Format: CMP-YYYYMMDD-XXX');

            // Core references
            $table->string('reservation_id', 20)->comment('Associated reservation');
            $table->string('reported_by', 36)->comment('User who submitted the complaint');
            $table->string('room_id', 36)->comment('Room being complained about');
            $table->string('facility_id', 36)->nullable()->comment('Specific facility in question (optional)');

            // Complaint content
            $table->string('title', 200)->comment('Brief title of the complaint');
            $table->text('description')->comment('Detailed description of the issue');
            $table->string('photo_path', 500)->nullable()->comment('Optional photo evidence (storage path)');

            // Status workflow: open → in_progress → resolved | rejected
            $table->enum('status', ['open', 'in_progress', 'resolved', 'rejected'])
                ->default('open')
                ->comment('Complaint handling status');

            // Resolution fields (filled when status becomes resolved/rejected)
            $table->text('resolution_notes')->nullable()->comment('Admin notes upon resolution or rejection');
            $table->timestamp('resolved_at')->nullable()->comment('Timestamp when complaint was closed');
            $table->string('resolved_by', 36)->nullable()->comment('Admin who resolved/rejected');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 36)->nullable();

            // Foreign keys
            $table->foreign('reservation_id')->references('id')->on('t_reservations')->onDelete('cascade');
            $table->foreign('reported_by')->references('id')->on('s_users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('m_rooms')->onDelete('cascade');
            $table->foreign('facility_id')->references('id')->on('m_facilities')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('s_users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('s_users')->onDelete('set null');

            // Indexes
            $table->index(['reservation_id', 'status', 'deleted_at'], 'idx_complaint_reservation');
            $table->index(['reported_by', 'status', 'deleted_at'], 'idx_complaint_reporter');
            $table->index(['room_id', 'status', 'deleted_at'], 'idx_complaint_room');
            $table->index(['status', 'created_at', 'deleted_at'], 'idx_complaint_status_time');
        });

        DB::statement('ALTER TABLE t_room_complaints MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE t_room_complaints MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE t_room_complaints MODIFY deleted_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE t_room_complaints MODIFY resolved_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_room_complaints');
    }
};
