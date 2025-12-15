<?php

namespace App\Services\RedProviderPortal\DTO;

use App\Enums\Order\Status;
use App\Enums\Order\Type;
use Exception;

class OrderData
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $type,
        public readonly ?string $status,
    ) {
    }

    /**
     * @param array<string, string> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (empty($id = $payload['id'])) {
            throw new Exception('Order payload is missing an ID.');
        }

        return new self(
            (string) $id,
            isset($payload['type']) ? strval($payload['type']) : null,
            isset($payload['status']) ? strval($payload['status']) : null,
        );
    }

    /**
     * @param array<int, array<string, string>> $payload
     * @return self[]
     */
    public static function fromList(array $payload): array
    {
        return array_map(fn (array $item) => self::fromArray($item), $payload);
    }

    public function getStatus(): ?Status
    {
        return $this->status ? Status::tryFrom($this->status) : null;
    }

    public function getType(): ?Type
    {
        return $this->type ? Type::tryFrom($this->type) : null;
    }
}
