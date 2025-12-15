<?php

namespace App\Services\RedProviderPortal\Contracts;

use App\Enums\Order\Type;
use App\Services\RedProviderPortal\DTO\OrderData;

interface RedProviderClient
{
    /** @return OrderData[] */
    public function listOrders(): array;

    public function createOrder(Type $type): OrderData;

    public function getOrder(string $id): OrderData;

    public function deleteOrder(string $id): void;
}
