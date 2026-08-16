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
        Schema::create('s_error_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('error_code', 6)->unique()->comment('Short unique tracking code shown to the user');
            $table->text('message')->comment('Internal exception message');
            $table->longText('stack_trace')->nullable();
            $table->string('exception_class', 200)->nullable();
            $table->string('user_id', 36)->nullable();
            $table->string('endpoint', 255);
            $table->string('http_method', 10);
            $table->text('request_body')->nullable()->comment('Sanitized request body (sensitive data stripped)');

            $table->timestamp('created_at')->useCurrent();

            $table->index('error_code');
            $table->index('endpoint');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s_error_logs');
    }
};
