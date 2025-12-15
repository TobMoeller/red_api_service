<?php

namespace App\Jobs\RedProviderPortal;

use App\Jobs\RedProviderPortal\Traits\DefaultConfig;
use App\Jobs\RedProviderPortal\Traits\UniqueForOrder;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class DeleteOrder implements ShouldBeUnique, ShouldQueue
{
    use DefaultConfig, Queueable, UniqueForOrder;

    public function __construct(public Order $order)
    {
        //
    }

    public function handle(RedProviderClient $apiClient): void
    {
        if (empty($providerId = $this->order->red_provider_portal_id)) {
            throw new Exception('Missing RED Provider Portal ID');
        }

        DB::transaction(function () use ($apiClient, $providerId) {
            $this->order->deleteOrFail();
            $apiClient->deleteOrder($providerId);
        });
    }
}
