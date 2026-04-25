<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class TenantDomain extends BaseDomain
{
    use HasUuids;

    protected $table = 'tenant_domains';

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }
}
