<?php

namespace App\Enums\Api\V1\Orders;

enum SortOrders: string
{
    case NAME_ASC = 'name';
    case NAME_DESC = '-name';
    case CREATED_AT_ASC = 'created_at';
    case CREATED_AT_DESC = '-created_at';

    public function field(): string
    {
        return match ($this) {
            self::NAME_ASC,
            self::NAME_DESC => 'name',
            self::CREATED_AT_ASC,
            self::CREATED_AT_DESC => 'created_at',
        };
    }

    public function order(): string
    {
        return match ($this) {
            self::NAME_ASC,
            self::CREATED_AT_ASC => 'asc',
            self::NAME_DESC,
            self::CREATED_AT_DESC => 'desc',
        };
    }
}
