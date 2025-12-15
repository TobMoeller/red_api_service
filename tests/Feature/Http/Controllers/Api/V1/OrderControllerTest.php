<?php

use App\Enums\Api\V1\Orders\SortOrders;
use App\Enums\Order\Status;
use App\Enums\Order\Type;
use App\Jobs\RedProviderPortal\CreateOrder;
use App\Jobs\RedProviderPortal\DeleteOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

function resourceStructure(): array
{
    return [
        'id',
        'name',
        'type',
        'status',
        'created_at',
        'updated_at',
    ];
}

beforeEach(function () {
    Queue::fake();
    Carbon::setTestNow(now()->setMilli(0));
});

it('requires authentication', function () {
    $this->getJson(route('api.v1.orders.index'))
        ->assertUnauthorized();
});

test('index with page filter', function () {
    Sanctum::actingAs(User::factory()->create());

    $orders = Order::factory()->count(9)->create();

    $response = $this
        ->get(route('api.v1.orders.index', ['page' => 2, 'perPage' => 3]))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => resourceStructure(),
            ],
            'meta',
            'links',
        ]);

    expect($response->json('data'))
        ->toMatchArray($orders->slice(3, 3)->values()->toArray());

    Queue::assertNothingPushed();
});

test('index with name filter', function () {
    Sanctum::actingAs(User::factory()->create());

    $order = Order::factory()->create([
        'name' => 'Foo',
    ]);
    Order::factory()->create([
        'name' => 'Bar',
    ]);

    $response = $this
        ->getJson(route('api.v1.orders.index', ['name' => 'oo']))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => resourceStructure(),
            ],
            'meta',
            'links',
        ]);

    expect($data = $response->json('data'))
        ->toHaveCount(1)
        ->and($data[0])
        ->toMatchArray($order->toResource()->response()->getData(true)['data']);

    Queue::assertNothingPushed();
});

test('index with ordering', function (SortOrders $sortOrder) {
    Sanctum::actingAs(User::factory()->create());

    Order::factory()->create([
        'name' => 'A',
        'created_at' => Carbon::now()->subDays(2),
    ]);
    Order::factory()->create([
        'name' => 'B',
        'created_at' => Carbon::now()->subDays(1),
    ]);

    $response = $this
        ->getJson(route('api.v1.orders.index', ['sort' => $sortOrder->value]))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => resourceStructure(),
            ],
            'meta',
            'links',
        ]);

    $data = collect($response->json('data'));
    if ($sortOrder->order() === 'asc') {
        expect($data->pluck('name'))
            ->toMatchArray(['A', 'B']);
    } else {
        expect($data->pluck('name'))
            ->toMatchArray(['B', 'A']);
    }

    Queue::assertNothingPushed();
})->with([
    SortOrders::NAME_ASC,
    SortOrders::NAME_DESC,
    SortOrders::CREATED_AT_ASC,
    SortOrders::CREATED_AT_DESC,
]);

it('rejects invalid filter queries', function (array $query, array $errors) {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson(route('api.v1.orders.index', $query))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);

    Queue::assertNothingPushed();
})->with([
    [
        'query' => ['name' => ''],
        'errors' => ['name'],
    ],
    [
        'query' => ['sort' => 'invalid'],
        'errors' => ['sort'],
    ],
    [
        'query' => ['sort' => ''],
        'errors' => ['sort'],
    ],
    [
        'query' => ['page' => 0],
        'errors' => ['page'],
    ],
    [
        'query' => ['perPage' => 0],
        'errors' => ['perPage'],
    ],
    [
        'query' => ['perPage' => 999],
        'errors' => ['perPage'],
    ],
]);

test('show', function () {
    Sanctum::actingAs(User::factory()->create());

    $order = Order::factory()->create();

    $response = $this
        ->getJson(route('api.v1.orders.show', $order))
        ->assertOk()
        ->assertJsonStructure([
            'data' => resourceStructure(),
        ]);

    expect($response->json('data'))
        ->toMatchArray($order->toResource()->response()->getData(true)['data']);

    Queue::assertNothingPushed();
});

test('create', function () {
    Sanctum::actingAs(User::factory()->create());

    $payload = [
        'name' => 'foobar',
        'type' => Type::CONNECTOR->value,
    ];

    $response = $this
        ->postJson(route('api.v1.orders.store', $payload))
        ->assertCreated()
        ->assertJsonStructure([
            'data' => resourceStructure(),
        ]);

    expect($response->json('data'))
        ->toMatchArray($payload);

    $this->assertDatabaseHas('orders', $payload);

    Queue::assertPushed(CreateOrder::class, fn (CreateOrder $job) => $job->order->name === 'foobar');
});

it('rejects invalid create payloads', function (array $payload, array $errors) {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.v1.orders.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errors);

    Queue::assertNothingPushed();
})->with([
    [
        'payload' => ['type' => Type::CONNECTOR->value], // missing name
        'errors' => ['name'],
    ],
    [
        'payload' => ['name' => 'foobar', 'type' => 'invalid'], // invalid type
        'errors' => ['type'],
    ],
    [
        'payload' => ['name' => 'foobar'], // missing type
        'errors' => ['type'],
    ],
]);


test('delete', function () {
    Sanctum::actingAs(User::factory()->create());

    $order = Order::factory()->create(['status' => Status::COMPLETED]);

    $this->deleteJson(route('api.v1.orders.destroy', $order))
        ->assertNoContent();

    Queue::assertPushed(DeleteOrder::class, fn (DeleteOrder $job) => $job->order->is($order));
});

it('rejects to delete uncompleted orders', function () {
    Sanctum::actingAs(User::factory()->create());

    $order = Order::factory()->create(['status' => Status::ORDERED]);

    $this->deleteJson(route('api.v1.orders.destroy', $order))
        ->assertUnprocessable();

    Queue::assertNothingPushed();
});

