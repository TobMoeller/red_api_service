<?php

namespace App\Http\Requests\Api\V1\Orders;

use App\Enums\Api\V1\Orders\SortOrders;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'int', 'min:1'],
            'perPage' => ['sometimes', 'int', 'min:1', 'max:100'],

            'name' => ['sometimes', 'string', 'min:1', 'max:255'],

            'sort' => [
                'sometimes',
                'string',
                Rule::enum(SortOrders::class),
            ],
        ];
    }
}
