<?php

namespace App\Exceptions;

use App\Models\TenantProvisioningRun;
use RuntimeException;
use Throwable;

class TenantProvisioningException extends RuntimeException
{
    public function __construct(
        public readonly TenantProvisioningRun $run,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public static function fromRun(TenantProvisioningRun $run, Throwable $previous): self
    {
        $step = $run->current_step ?: 'unknown step';
        $error = trim($previous->getMessage());

        return new self(
            run: $run,
            message: "Provisioning failed during {$step}. The tenant remains in provisioning. {$error}",
            previous: $previous,
        );
    }
}
