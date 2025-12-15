<?php

use App\Enums\Order\Status;
use App\Jobs\RedProviderPortal\DeleteOrder;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;

it('deletes an order', function () {
    $order = Order::factory()->create([
        'status' => Status::COMPLETED,
        'red_provider_portal_id' => 'remote-id',
    ]);

    $client = \Mockery::mock(RedProviderClient::class);
    $client->shouldReceive('deleteOrder')
        ->once()
        ->with('remote-id');

    (new DeleteOrder($order))->handle($client);

    expect($order->fresh())->toBeEmpty();
});
