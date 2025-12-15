<?php

namespace App\Actions\RedProviderPortal;

use App\Enums\Order\Status;
use App\Jobs\RedProviderPortal\SynchronizeOrder;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class QueryOrderUpdates
{
    const CACHE_LOCK_KEY = 'query_order_updates_lock';

    public function handle(): void
    {
        Cache::lock(self::CACHE_LOCK_KEY, 10)->get(function () {
            Order::query()
                ->where('status', '!=', Status::COMPLETED)
                ->whereNotNull('red_provider_portal_id')
                ->where('updated_at', '<=', Carbon::now()->subMinutes(5)) // TODO define sensible default matching allowed API limits
                ->chunkById(
                    100,
                    function (Collection $collection): void {
                        $collection->each(fn (Order $order) => SynchronizeOrder::dispatch($order));
                    },
                );
        });
    }
}
