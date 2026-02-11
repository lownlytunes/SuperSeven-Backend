<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\ReportBillingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportBillingCollection extends PaginationCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => ReportBillingResource::collection($this->collection),

            $this->merge($this->pagination()),
        ];
    }
}
