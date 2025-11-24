<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Defer to SQL seed if domain tables are not yet available
        if (!Schema::hasTable('event') || !Schema::hasTable('user')) {
            return; // ER/EBD schema is created by SQL seed
        }

        // If already created (by seed or previous run), skip
        if (Schema::hasTable('invitation')) {
            return;
        }

        // Create table 
        Schema::create('invitation', function (Blueprint $table) {
            $table->id('id_invitation');
            $table->unsignedBigInteger('id_event');
            $table->unsignedBigInteger('id_invitee');
            $table->string('status', 16)->default('pending'); // pending, accepted, declined, canceled, expired
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->unique(['id_event', 'id_invitee']);

            $table->foreign('id_event')->references('id_event')->on('event')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_invitee')->references('id_user')->on('user')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Add CHECK constraint for status values (PostgreSQL specific; ignore errors on other DBs)
        try {
            DB::statement("ALTER TABLE invitation ADD CONSTRAINT chk_invitation_status CHECK (status IN ('pending','accepted','declined','canceled','expired'))");
        } catch (Throwable $e) {
            // silently ignore if not supported
        }

        // Ensure responded_at > sent_at if present
        try {
            DB::statement("ALTER TABLE invitation ADD CONSTRAINT chk_invitation_times CHECK (responded_at IS NULL OR responded_at > sent_at)");
        } catch (Throwable $e) {
            // ignore
        }

        // Composite index for faster lookups (id_event, status, id_invitee)
        Schema::table('invitation', function (Blueprint $table) {
            $table->index(['id_event','status','id_invitee'], 'idx_invitation_event_status_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation');
    }
};
