<?php

namespace App\Jobs\RedProviderPortal\Traits;

use DateTime;
use Illuminate\Support\Carbon;

trait DefaultConfig
{
    /**
     * TODO handle final job failure
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [1, 10, 60];
    }

    public function retryUntil(): DateTime
    {
        return Carbon::now()->plus(minutes: 60);
    }
}
