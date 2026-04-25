<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Stancl\Tenancy\Database\Concerns\GeneratesIds;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Stancl\Tenancy\Database\Concerns\InvalidatesResolverCache;
use Stancl\Tenancy\Database\Concerns\TenantRun;
use Stancl\Tenancy\Database\TenantCollection;
use Stancl\Tenancy\Events;

/**
 * @property string $id
 * @property string $slug
 * @property string $display_name
 * @property string|null $legal_name
 * @property string $state
 * @property string $database_name
 * @property string $region
 * @property string $timezone
 * @property string|null $locale
 * @property array|null $settings
 * @property Carbon|null $activated_at
 * @property Carbon|null $suspended_at
 * @property Carbon|null $archived_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static TenantCollection all($columns = ['*'])
 */
class Tenant extends Model implements TenantWithDatabase
{
    use CentralConnection;
    use GeneratesIds;
    use HasDatabase;
    use HasDomains;
    use HasInternalKeys {
        getInternal as protected getTraitInternal;
        setInternal as protected setTraitInternal;
    }
    use TenantRun;
    use InvalidatesResolverCache;

    protected static $modelsShouldPreventAccessingMissingAttributes = false;

    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function getInternal(string $key)
    {
        if ($key === 'db_name') {
            return $this->database_name;
        }

        return $this->getTraitInternal($key);
    }

    public function setInternal(string $key, $value)
    {
        if ($key === 'db_name') {
            $this->database_name = $value;

            return $this;
        }

        return $this->setTraitInternal($key, $value);
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(TenantDomain::class, 'tenant_id')->where('is_primary', true);
    }

    public function provisioningRuns(): HasMany
    {
        return $this->hasMany(TenantProvisioningRun::class);
    }

    public function latestProvisioningRun(): HasOne
    {
        return $this->hasOne(TenantProvisioningRun::class)
            ->orderByDesc('created_at')
            ->orderByDesc('started_at');
    }

    protected $dispatchesEvents = [
        'saving' => Events\SavingTenant::class,
        'saved' => Events\TenantSaved::class,
        'creating' => Events\CreatingTenant::class,
        'created' => Events\TenantCreated::class,
        'updating' => Events\UpdatingTenant::class,
        'updated' => Events\TenantUpdated::class,
        'deleting' => Events\DeletingTenant::class,
        'deleted' => Events\TenantDeleted::class,
    ];
}
