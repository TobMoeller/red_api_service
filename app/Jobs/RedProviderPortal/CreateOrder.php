<?php

namespace App\Jobs\RedProviderPortal;

use App\Jobs\RedProviderPortal\Traits\DefaultConfig;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CreateOrder implements ShouldQueue
{
    use Queueable, DefaultConfig;

    public function __construct(public Order $order)
    {
        //
    }

    public function handle(RedProviderClient $apiClient): void
    {
        DB::transaction(function () use ($apiClient) {
            $data = $apiClient->createOrder($this->order->type);

            if ($status = $data->getStatus()) {
                $this->order->status = $status;
            }
            if ($type = $data->getType()) {
                $this->order->type = $type;
            }
            $this->order->red_provider_portal_id = $data->id;
            $this->order->saveOrFail();
        });
    }
}
