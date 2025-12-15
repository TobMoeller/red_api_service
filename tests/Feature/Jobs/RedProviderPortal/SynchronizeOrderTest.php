<?php

use App\Enums\Order\Status;
use App\Enums\Order\Type;
use App\Jobs\RedProviderPortal\SynchronizeOrder;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use Illuminate\Support\Carbon;

it('synchronizes an order', function () {
    Carbon::setTestNow(now()->setMilli(0));

    $order = Order::factory()->create([
        'status' => Status::ORDERED,
        'type' => Type::CONNECTOR,
        'red_provider_portal_id' => 'remote-123',
        'updated_at' => now()->subDay(),
    ]);

    $client = \Mockery::mock(RedProviderClient::class);
    $client->shouldReceive('getOrder')
        ->once()
        ->with('remote-123')
        ->andReturn($dto = createOrderDTO('remote-123', Type::VPN_CONNECTION, Status::COMPLETED));

    (new SynchronizeOrder($order))->handle($client);

    expect($order->refresh())
        ->updated_at->eq(now())->toBeTrue()
        ->type->toBe($dto->getType())
        ->status->toBe($dto->getStatus());
});

it('refreshes the order updated_at timestamp when synchronization runs', function () {
    Carbon::setTestNow(now()->setMilli(0));

    $order = Order::factory()->create([
        'status' => $status = Status::ORDERED,
        'type' => $type = Type::CONNECTOR,
        'red_provider_portal_id' => 'remote-123',
        'updated_at' => now()->subDay(),
    ]);

    $client = \Mockery::mock(RedProviderClient::class);
    $client->shouldReceive('getOrder')
        ->once()
        ->with('remote-123')
        ->andReturn(createOrderDTO('remote-123', $type, $status));

    (new SynchronizeOrder($order))->handle($client);

    expect($order->refresh())
        ->updated_at->eq(now())->toBeTrue();
});
