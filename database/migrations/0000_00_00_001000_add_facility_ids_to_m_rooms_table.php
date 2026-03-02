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
        Schema::table('m_rooms', function (Blueprint $table) {
            $table->json('facility_ids')->nullable()->after('capacity')->comment('Daftar fasilitas ruangan (JSON array)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_rooms', function (Blueprint $table) {
            $table->dropColumn('facility_ids');
        });
    }
};
