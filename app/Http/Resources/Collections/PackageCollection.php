<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\PackageResource;
use Illuminate\Http\Request;

class PackageCollection extends PaginationCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PackageResource::collection($this->collection),

            $this->merge($this->pagination()),
        ];
    }
}
