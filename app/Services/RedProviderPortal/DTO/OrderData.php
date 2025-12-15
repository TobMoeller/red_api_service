<?php

namespace App\Services\RedProviderPortal\DTO;

class OrderData
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $type,
        public readonly ?string $status,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            isset($payload['id']) ? (string) $payload['id'] : null,
            isset($payload['type']) ? (string) $payload['type'] : null,
            isset($payload['status']) ? (string) $payload['status'] : null,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     */
    public static function fromList(array $payload): array
    {
        return array_map(fn (array $item) => self::fromArray($item), $payload);
    }
}
