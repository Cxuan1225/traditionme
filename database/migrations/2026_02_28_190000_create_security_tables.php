<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mfa_factors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->text('secret');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index('type');
        });

        Schema::create('mfa_recovery_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mfa_factor_id')->constrained('mfa_factors')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'used_at']);
            $table->unique(['mfa_factor_id', 'code_hash']);
        });

        Schema::create('user_sessions', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_activity_at']);
            $table->index('revoked_at');
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 120);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index('event');
            $table->index(['actor_id', 'occurred_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('mfa_recovery_codes');
        Schema::dropIfExists('mfa_factors');
    }
};
