<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_divisions', function (Blueprint $table) {
            // Custom string ID: DIV-NN (e.g. DIV-01)
            $table->string('id', 10)->primary()->comment('Format: DIV-NN');

            $table->string('name', 100)->unique()->comment('Division full name');
            $table->string('code', 10)->unique()->comment('Short code used in employee ID (e.g. OPS, KNP, HRD)');
            $table->text('description')->nullable();

            // Audit fields
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->string('updated_by', 36)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by', 36)->nullable();

            $table->index('code');
        });

        DB::statement('ALTER TABLE m_divisions MODIFY created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE m_divisions MODIFY updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
        DB::statement('ALTER TABLE m_divisions MODIFY deleted_at TIMESTAMP NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('m_divisions');
    }
};
