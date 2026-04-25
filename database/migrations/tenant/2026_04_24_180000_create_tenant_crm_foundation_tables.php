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
        Schema::create('organization_profile', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('display_name');
            $table->string('legal_name')->nullable();
            $table->string('timezone', 64);
            $table->string('locale', 35)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->string('country_code', 2)->nullable();
            $table->string('website')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('primary_phone_e164', 20)->nullable()->index();
            $table->jsonb('address')->nullable();
            $table->jsonb('branding_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'archived_at']);
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_team')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(['team_id', 'user_id']);
            $table->index(['user_id', 'is_primary']);
        });

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX team_user_one_primary_team_per_user
            ON team_user (user_id)
            WHERE is_primary = true
        SQL);

        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_active')->default(true);
        });

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX lead_statuses_single_default
            ON lead_statuses (is_default)
            WHERE is_default = true
        SQL);

        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_active')->default(true);
        });

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX ticket_statuses_single_default
            ON ticket_statuses (is_default)
            WHERE is_default = true
        SQL);

        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX pipelines_single_default
            ON pipelines (is_default)
            WHERE is_default = true
        SQL);

        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('probability')->nullable();
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unique(['pipeline_id', 'code']);
            $table->index(['pipeline_id', 'sort_order']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('primary_phone')->nullable();
            $table->string('primary_phone_e164', 20)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('status', 32)->default('active');
            $table->jsonb('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('primary_email');
            $table->index('primary_phone_e164');
            $table->index(['owner_user_id', 'deleted_at']);
            $table->index(['team_id', 'deleted_at']);
            $table->index(['status', 'deleted_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE accounts
            ADD CONSTRAINT accounts_status_check
            CHECK (status IN ('active', 'inactive', 'archived'))
        SQL);

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_e164', 20)->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone_e164');
            $table->index(['account_id', 'deleted_at']);
            $table->index(['owner_user_id', 'deleted_at']);
            $table->index(['team_id', 'deleted_at']);
            $table->index(['status', 'deleted_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE contacts
            ADD CONSTRAINT contacts_status_check
            CHECK (status IN ('active', 'inactive', 'do_not_contact'))
        SQL);

        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('pipeline_id')->constrained('pipelines')->restrictOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages')->restrictOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('primary_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->date('expected_close_date')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_stage_id', 'owner_user_id', 'deleted_at']);
            $table->index(['team_id', 'assigned_user_id', 'deleted_at']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('lead_status_id')->constrained('lead_statuses')->restrictOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_e164', 20)->nullable();
            $table->string('source')->nullable();
            $table->integer('score')->nullable();
            $table->foreignId('converted_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_deal_id')->nullable()->constrained('deals')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone_e164');
            $table->index(['lead_status_id', 'owner_user_id', 'deleted_at']);
            $table->index(['team_id', 'assigned_user_id', 'deleted_at']);
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('ticket_status_id')->constrained('ticket_statuses')->restrictOnDelete();
            $table->foreignId('ticket_priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_status_id', 'assigned_user_id', 'deleted_at']);
            $table->index(['team_id', 'deleted_at']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('activity_type', 32);
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_user_id', 'status', 'deleted_at']);
            $table->index(['activity_type', 'status', 'deleted_at']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE activities
            ADD CONSTRAINT activities_status_check
            CHECK (status IN ('pending', 'in_progress', 'completed', 'cancelled'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE activities
            ADD CONSTRAINT activities_type_check
            CHECK (activity_type IN ('task', 'call', 'meeting', 'follow_up', 'note', 'email'))
        SQL);

        Schema::create('activity_links', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('entity_type', 32);
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();

            $table->unique(['activity_id', 'entity_type', 'entity_id'], 'activity_links_unique_link');
            $table->index(['entity_type', 'entity_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE activity_links
            ADD CONSTRAINT activity_links_entity_type_check
            CHECK (entity_type IN ('account', 'contact', 'lead', 'deal', 'ticket'))
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_links');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('ticket_priorities');
        Schema::dropIfExists('ticket_statuses');
        Schema::dropIfExists('lead_statuses');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('organization_profile');
    }
};
