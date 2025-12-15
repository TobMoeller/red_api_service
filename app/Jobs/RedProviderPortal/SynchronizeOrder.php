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
use Illuminate\Support\Carbon;

class SynchronizeOrder implements ShouldBeUnique, ShouldQueue
{
    use DefaultConfig, Queueable, UniqueForOrder;

    public function __construct(public Order $order)
    {
        //
    }

    public function handle(RedProviderClient $apiClient): void
    {
        if (empty($this->order->red_provider_portal_id)) {
            throw new Exception('Missing RED Provider Portal ID');
        }

        $result = $apiClient->getOrder($this->order->red_provider_portal_id);

        if (($status = $result->getStatus()) && $status !== $this->order->status) {
            $this->order->status = $status;
        }
        if (($type = $result->getType()) && $type !== $this->order->type) {
            $this->order->type = $type;
        }
        $this->order->updated_at = Carbon::now();
        $this->order->saveOrFail();
    }
}
