<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantWorkflowSeeder extends Seeder
{
    /**
     * Seed baseline CRM workflow rows for a fresh tenant.
     */
    public function run(): void
    {
        DB::table('lead_statuses')->upsert([
            [
                'code' => 'new',
                'name' => 'New',
                'sort_order' => 10,
                'is_default' => true,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'working',
                'name' => 'Working',
                'sort_order' => 20,
                'is_default' => false,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'qualified',
                'name' => 'Qualified',
                'sort_order' => 30,
                'is_default' => false,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'closed_lost',
                'name' => 'Closed Lost',
                'sort_order' => 40,
                'is_default' => false,
                'is_closed' => true,
                'is_active' => true,
            ],
        ], ['code'], ['name', 'sort_order', 'is_default', 'is_closed', 'is_active']);

        DB::table('ticket_statuses')->upsert([
            [
                'code' => 'open',
                'name' => 'Open',
                'sort_order' => 10,
                'is_default' => true,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'in_progress',
                'name' => 'In Progress',
                'sort_order' => 20,
                'is_default' => false,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'waiting_customer',
                'name' => 'Waiting On Customer',
                'sort_order' => 30,
                'is_default' => false,
                'is_closed' => false,
                'is_active' => true,
            ],
            [
                'code' => 'resolved',
                'name' => 'Resolved',
                'sort_order' => 40,
                'is_default' => false,
                'is_closed' => true,
                'is_active' => true,
            ],
            [
                'code' => 'closed',
                'name' => 'Closed',
                'sort_order' => 50,
                'is_default' => false,
                'is_closed' => true,
                'is_active' => true,
            ],
        ], ['code'], ['name', 'sort_order', 'is_default', 'is_closed', 'is_active']);

        DB::table('ticket_priorities')->upsert([
            ['code' => 'low', 'name' => 'Low', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'medium', 'name' => 'Medium', 'sort_order' => 20, 'is_active' => true],
            ['code' => 'high', 'name' => 'High', 'sort_order' => 30, 'is_active' => true],
            ['code' => 'urgent', 'name' => 'Urgent', 'sort_order' => 40, 'is_active' => true],
        ], ['code'], ['name', 'sort_order', 'is_active']);

        $pipelineId = DB::table('pipelines')->where('name', 'Sales Pipeline')->value('id');

        if (! $pipelineId) {
            $pipelineId = DB::table('pipelines')->insertGetId([
                'public_id' => (string) Str::uuid(),
                'name' => 'Sales Pipeline',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('pipeline_stages')->upsert([
            [
                'pipeline_id' => $pipelineId,
                'code' => 'prospecting',
                'name' => 'Prospecting',
                'sort_order' => 10,
                'probability' => 10,
                'is_won' => false,
                'is_lost' => false,
                'is_active' => true,
            ],
            [
                'pipeline_id' => $pipelineId,
                'code' => 'qualification',
                'name' => 'Qualification',
                'sort_order' => 20,
                'probability' => 25,
                'is_won' => false,
                'is_lost' => false,
                'is_active' => true,
            ],
            [
                'pipeline_id' => $pipelineId,
                'code' => 'proposal',
                'name' => 'Proposal',
                'sort_order' => 30,
                'probability' => 50,
                'is_won' => false,
                'is_lost' => false,
                'is_active' => true,
            ],
            [
                'pipeline_id' => $pipelineId,
                'code' => 'negotiation',
                'name' => 'Negotiation',
                'sort_order' => 40,
                'probability' => 75,
                'is_won' => false,
                'is_lost' => false,
                'is_active' => true,
            ],
            [
                'pipeline_id' => $pipelineId,
                'code' => 'won',
                'name' => 'Won',
                'sort_order' => 50,
                'probability' => 100,
                'is_won' => true,
                'is_lost' => false,
                'is_active' => true,
            ],
            [
                'pipeline_id' => $pipelineId,
                'code' => 'lost',
                'name' => 'Lost',
                'sort_order' => 60,
                'probability' => 0,
                'is_won' => false,
                'is_lost' => true,
                'is_active' => true,
            ],
        ], ['pipeline_id', 'code'], ['name', 'sort_order', 'probability', 'is_won', 'is_lost', 'is_active']);
    }
}
