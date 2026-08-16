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
        Schema::create('s_users', function (Blueprint $table) {
            // Custom string ID (generated in code, not auto-increment)
            $table->string('id', 36)->primary()->comment('UUIDv7 generated in code');

            // User Information
            $table->string('employee_id', 25)->unique()->comment('Employee ID (DIV_CODE-YYYY-NNNNN or ADM-YYYY-NN)');
            $table->string('division_id', 10)->nullable()->comment('FK to m_divisions.id');
            $table->string('email', 100)->unique()->comment('Email address');
            $table->string('password')->comment('Hashed password');
            $table->string('first_name', 50)->comment('First name');
            $table->string('last_name', 50)->comment('Last name');
            $table->date('date_of_birth')->nullable()->comment('Date of birth');
            $table->boolean('is_admin')->default(false)->comment('Admin access flag');
            $table->boolean('is_active')->default(true)->comment('User active status');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 36)->nullable();

            // Foreign keys
            $table->foreign('division_id')->references('id')->on('m_divisions')->onDelete('set null');

            // Indexes for performance
            $table->index('employee_id');
            $table->index('division_id');
            $table->index('email');
            $table->index('is_admin');
            $table->index('is_active');
            $table->index(['deleted_at', 'is_admin']); // Composite index for filtering users by admin flag
        });

        // Set timezone to UTC for this table
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE s_users MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE s_users MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
            DB::statement('ALTER TABLE s_users MODIFY deleted_at TIMESTAMP NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_users');
    }
};
