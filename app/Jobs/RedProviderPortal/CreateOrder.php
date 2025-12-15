<?php

namespace App\Jobs\RedProviderPortal;

use App\Enums\Order\Status;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CreateOrder implements ShouldQueue
{
    use Queueable;

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
        return now()->plus(minutes: 60);
    }

    public function __construct(public Order $order)
    {
        //
    }

    public function handle(RedProviderClient $apiClient): void
    {
        DB::transaction(function () use ($apiClient) {
            $this->order->status = Status::PROCESSING;
            $this->order->saveOrFail();
            $apiClient->createOrder($this->order->type);
        });
    }
}
