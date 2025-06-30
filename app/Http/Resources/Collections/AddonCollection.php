<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\AddonResource;
use Illuminate\Http\Request;

class AddonCollection extends PaginationCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => AddonResource::collection($this->collection),

            $this->merge($this->pagination()),
        ];
    }
}
