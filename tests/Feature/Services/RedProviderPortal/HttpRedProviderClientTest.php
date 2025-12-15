<?php

use App\Enums\Order\Status;
use App\Enums\Order\Type;
use App\Services\RedProviderPortal\Clients\HttpRedProviderClient;
use App\Services\RedProviderPortal\DTO\AccessToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    Carbon::setTestNow(now());
});

function makeClient(): HttpRedProviderClient
{
    return new HttpRedProviderClient(
        baseUrl: 'https://example.test',
        clientId: 'client',
        clientSecret: 'secret',
        certPath: base_path('ssl_cert.pem')
    );
}

it('retrieves an access token from the api', function () {
    Http::fake([
        'https://example.test/api/v1/token' => Http::response([
            'access_token' => '::token::',
            'ttl' => 60,
        ], 200),
        'https://example.test/api/v1/orders' => Http::response([], 200),
    ]);

    makeClient()->listOrders();

    Http::assertSent(fn ($request) => $request->url() === 'https://example.test/api/v1/token');
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer ::token::'));
    expect(Cache::get(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY))
        ->toBeInstanceOf(AccessToken::class)
        ->token->toBe('::token::')
        ->expiresAt->eq(now()->addSeconds(50))->toBeTrue();
});

it('retrieves an access token from cache', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->addMinute()));
    Http::fake([
        'https://example.test/api/v1/orders' => Http::response([], 200),
    ]);

    makeClient()->listOrders();

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer ::token::'));
});

it('retrieves an access token from the api if cache is expired', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->subMinutes(5)));
    Http::fake([
        'https://example.test/api/v1/token' => Http::response([
            'access_token' => '::token::',
            'ttl' => 60,
        ], 200),
        'https://example.test/api/v1/orders' => Http::response([], 200),
    ]);

    makeClient()->listOrders();

    Http::assertSent(fn ($request) => $request->url() === 'https://example.test/api/v1/token');
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer ::token::'));
});

it('lists orders', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->addMinute()));
    Http::fake([
        'https://example.test/api/v1/orders' => Http::response([
            ['id' => '1', 'type' => Type::CONNECTOR->value, 'status' => Status::ORDERED->value],
            ['id' => '2', 'type' => Type::VPN_CONNECTION->value, 'status' => Status::COMPLETED->value],
        ], 200),
    ]);

    $orders = makeClient()->listOrders();

    expect($orders)->toHaveCount(2)
        ->and($orders[0]->id)->toBe('1')
        ->and($orders[0]->getType())->toBe(Type::CONNECTOR)
        ->and($orders[0]->getStatus())->toBe(Status::ORDERED)
        ->and($orders[1]->id)->toBe('2')
        ->and($orders[1]->getType())->toBe(Type::VPN_CONNECTION)
        ->and($orders[1]->getStatus())->toBe(Status::COMPLETED);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://example.test/api/v1/orders';
    });
});

it('creates an order', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->addMinute()));
    Http::fake([
        'https://example.test/api/v1/orders' => Http::response([
            'id' => '123',
            'type' => Type::CONNECTOR->value,
            'status' => Status::ORDERED->value,
        ], 201),
    ]);

    $order = makeClient()->createOrder(Type::CONNECTOR);

    expect($order->id)->toBe('123')
        ->and($order->getType())->toBe(Type::CONNECTOR)
        ->and($order->getStatus())->toBe(Status::ORDERED);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://example.test/api/v1/orders'
            && $request['type'] === Type::CONNECTOR->value;
    });
});

it('gets a single order', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->addMinute()));
    Http::fake([
        'https://example.test/api/v1/order/abc' => Http::response([
            'id' => 'abc',
            'type' => Type::VPN_CONNECTION->value,
            'status' => Status::PROCESSING->value,
        ], 200),
    ]);

    $order = makeClient()->getOrder('abc');

    expect($order->id)->toBe('abc')
        ->and($order->getType())->toBe(Type::VPN_CONNECTION)
        ->and($order->getStatus())->toBe(Status::PROCESSING);

    Http::assertSent(function ($request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://example.test/api/v1/order/abc';
    });
});

it('deletes an order remotely', function () {
    Cache::put(HttpRedProviderClient::ACCESS_TOKEN_CACHE_KEY, new AccessToken('::token::', now()->addMinute()));
    Http::fake([
        'https://example.test/api/v1/order/abc' => Http::response([], 204),
    ]);

    makeClient()->deleteOrder('abc');

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE'
            && $request->url() === 'https://example.test/api/v1/order/abc';
    });
});
