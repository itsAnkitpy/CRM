<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TenantProvisioningRun extends Model
{
    use CentralConnection;
    use HasUuids;

    protected $table = 'tenant_provisioning_runs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(LandlordUser::class, 'triggered_by_user_id');
    }
}
