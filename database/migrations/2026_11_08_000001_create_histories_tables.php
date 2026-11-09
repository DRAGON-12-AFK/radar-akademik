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
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('identifier', 50); // NIM, NIDN, atau email
            $table->string('role', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('api_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('filter_type', 50); // transkrip, mahasiswa, dosen
            $table->string('identifier', 50)->nullable(); // NIM / NIDN
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['success', 'failed', 'timeout'])->default('success');
            $table->unsignedInteger('records_count')->default(0);
            $table->unsignedInteger('duration_ms')->default(0); // Waktu proses (milidetik)
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sync_histories');
        Schema::dropIfExists('login_histories');
    }
};

