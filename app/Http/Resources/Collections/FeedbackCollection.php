<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\FeedbackResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FeedbackCollection extends PaginationCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => FeedbackResource::collection($this->collection),

            $this->merge($this->pagination()),
        ];
    }
}
