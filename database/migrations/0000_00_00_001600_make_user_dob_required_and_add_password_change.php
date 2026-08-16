<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill placeholder DOB, make date_of_birth required, and add must_change_password flag.
     */
    public function up(): void
    {
        DB::statement("UPDATE s_users SET date_of_birth = '1900-01-01' WHERE date_of_birth IS NULL");

        Schema::table('s_users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('is_active')->comment('Force password change on next login');
        });

        Schema::table('s_users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });

        Schema::table('s_users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->change();
        });
    }
};
