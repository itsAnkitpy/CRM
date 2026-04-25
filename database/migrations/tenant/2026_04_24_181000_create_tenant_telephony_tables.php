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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('provider');
            $table->string('provider_call_id');
            $table->string('direction', 16);
            $table->string('status', 32);
            $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('phone_from')->nullable();
            $table->string('phone_from_e164', 20)->nullable();
            $table->string('phone_to')->nullable();
            $table->string('phone_to_e164', 20)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->jsonb('provider_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_call_id']);
            $table->index('phone_from_e164');
            $table->index('phone_to_e164');
            $table->index(['user_id', 'started_at']);
            $table->index(['team_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE calls
            ADD CONSTRAINT calls_direction_check
            CHECK (direction IN ('inbound', 'outbound'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE calls
            ADD CONSTRAINT calls_status_check
            CHECK (status IN ('ringing', 'answered', 'completed', 'failed', 'missed', 'busy', 'no_answer', 'cancelled'))
        SQL);

        Schema::create('call_links', function (Blueprint $table) {
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->string('entity_type', 32);
            $table->unsignedBigInteger('entity_id');
            $table->string('relationship_type', 32)->nullable();
            $table->timestamps();

            $table->unique(['call_id', 'entity_type', 'entity_id'], 'call_links_unique_link');
            $table->index(['entity_type', 'entity_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE call_links
            ADD CONSTRAINT call_links_entity_type_check
            CHECK (entity_type IN ('account', 'contact', 'lead', 'deal', 'ticket'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE call_links
            ADD CONSTRAINT call_links_relationship_type_check
            CHECK (relationship_type IS NULL OR relationship_type IN ('primary', 'related', 'source', 'target'))
        SQL);

        Schema::create('call_recordings', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->string('provider_recording_id')->nullable()->unique();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('storage_disk')->nullable();
            $table->string('storage_path')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['call_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_recordings');
        Schema::dropIfExists('call_links');
        Schema::dropIfExists('calls');
    }
};
