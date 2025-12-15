<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'name' => $resource->name,
            'type' => $resource->type,
            'status' => $resource->status,
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
        ];
    }
}
