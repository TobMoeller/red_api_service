<?php

use App\Jobs\RedProviderPortal\CreateOrder;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;

it('creates an order', function () {
    $order = Order::factory()->create([
        'red_provider_portal_id' => 'remote-id',
    ]);

    $client = \Mockery::mock(RedProviderClient::class);
    $client->shouldReceive('createOrder')
        ->once()
        ->with($order->type)
        ->andReturn($dto = createOrderDTO('remote-id'));

    (new CreateOrder($order))->handle($client);

    expect($order->refresh())
        ->type->toBe($dto->getType())
        ->status->toBe($dto->getStatus());
});
