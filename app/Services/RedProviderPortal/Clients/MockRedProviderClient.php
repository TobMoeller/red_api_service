<?php

namespace App\Services\RedProviderPortal\Clients;

use App\Enums\Order\Status;
use App\Enums\Order\Type;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use App\Services\RedProviderPortal\DTO\OrderData;
use Carbon\CarbonImmutable;

class MockRedProviderClient implements RedProviderClient
{
    public function listOrders(): array
    {
        $orders = [];
        for ($i = 0; $i < rand(1, 20); $i++) {
            $orders[] = [
                'id' => fake()->uuid(),
                'type' => (fake()->randomElement(Type::cases()))->value,
                'status' => Status::ORDERED->value,
                'created_at' => CarbonImmutable::now()->toIso8601String(),
            ];
        }

        return OrderData::fromList($orders);
    }

    public function createOrder(Type $type): OrderData
    {
        return OrderData::fromArray([
            'id' => fake()->uuid(),
            'type' => (fake()->randomElement(Type::cases()))->value,
            'status' => Status::ORDERED->value,
            'created_at' => CarbonImmutable::now()->toIso8601String(),
        ]);
    }

    public function getOrder(string $id): OrderData
    {
        return OrderData::fromArray([
            'id' => fake()->uuid(),
            'type' => (fake()->randomElement(Type::cases()))->value,
            'status' => (fake()->randomElement(Status::cases()))->value,
            'created_at' => CarbonImmutable::now()->toIso8601String(),
        ]);
    }

    public function deleteOrder(string $id): void
    {
        //
    }
}
