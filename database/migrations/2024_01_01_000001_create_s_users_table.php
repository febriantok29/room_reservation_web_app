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
        Schema::create('s_users', function (Blueprint $table) {
            $table->id();

            // User Information
            $table->string('employee_id', 20)->unique()->comment('Employee ID');
            $table->string('email', 100)->unique()->comment('Email address');
            $table->string('password')->comment('Hashed password');
            $table->string('first_name', 50)->comment('First name');
            $table->string('last_name', 50)->comment('Last name');
            $table->date('date_of_birth')->nullable()->comment('Date of birth');
            $table->enum('role', ['user', 'staff', 'admin'])->default('user')->comment('User role');

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Indexes for performance
            $table->index('employee_id');
            $table->index('email');
            $table->index('role');
            $table->index(['deleted_at', 'role']); // Composite index for filtering active users by role
        });

        // Set timezone to UTC for this table
        DB::statement('ALTER TABLE s_users MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE s_users MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE s_users MODIFY deleted_at TIMESTAMP NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_users');
    }
};
