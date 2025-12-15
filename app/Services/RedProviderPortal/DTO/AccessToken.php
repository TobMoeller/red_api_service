<?php

namespace App\Services\RedProviderPortal\DTO;

use Illuminate\Support\Carbon;

class AccessToken
{
    public function __construct(
        public string $token,
        public Carbon $expiresAt,
    ) {
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->isPast();
    }
}
