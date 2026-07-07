<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 36)->comment('FK to s_users.id');
            // token is globally unique: one FCM token belongs to exactly one user/device
            $table->string('token', 512)->unique()->comment('Firebase Cloud Messaging device token');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('s_users')
                ->cascadeOnDelete();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('s_fcm_tokens');
    }
};
