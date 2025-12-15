<?php

use App\Actions\RedProviderPortal\QueryOrderUpdates;
use App\Enums\Order\Status;
use App\Jobs\RedProviderPortal\SynchronizeOrder;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

it('dispatches synchronize jobs for in-progress orders with remote id', function () {
    Queue::fake();

    Carbon::setTestNow(now());

    // Should be synced
    $order = Order::factory()->create([
        'status' => Status::ORDERED,
        'red_provider_portal_id' => 'remote-1',
        'updated_at' => now()->subMinutes(10),
    ]);

    $order2 = Order::factory()->create([
        'status' => Status::PROCESSING,
        'red_provider_portal_id' => 'remote-2',
        'updated_at' => now()->subMinutes(10),
    ]);

    // Should be ignored (completed)
    Order::factory()->create([
        'status' => Status::COMPLETED,
        'red_provider_portal_id' => 'remote-10',
        'updated_at' => now()->subMinutes(10),
    ]);

    // Should be ignored (no remote id)
    Order::factory()->create([
        'status' => Status::PROCESSING,
        'red_provider_portal_id' => null,
        'updated_at' => now()->subMinutes(10),
    ]);

    // Should be ignored (recently updated)
    Order::factory()->create([
        'status' => Status::PROCESSING,
        'red_provider_portal_id' => 'remote-11',
        'updated_at' => now()->subMinutes(4),
    ]);

    app(QueryOrderUpdates::class)->handle();

    Queue::assertPushed(SynchronizeOrder::class, fn (SynchronizeOrder $job) => $job->order->is($order));
    Queue::assertPushed(SynchronizeOrder::class, fn (SynchronizeOrder $job) => $job->order->is($order2));

    Queue::assertPushed(\App\Jobs\RedProviderPortal\SynchronizeOrder::class, 2);
});

it('does not dispatch when lock is already held', function () {
    Queue::fake();

    $lock = new class
    {
        public int $called = 0;

        public function get($callback = null)
        {
            $this->called++;

            return false;
        }
    };

    Cache::shouldReceive('lock')
        ->once()
        ->with(QueryOrderUpdates::CACHE_LOCK_KEY, 10)
        ->andReturn($lock);

    app(QueryOrderUpdates::class)->handle();

    expect($lock->called)->toBe(1);
    Queue::assertNothingPushed();
});
