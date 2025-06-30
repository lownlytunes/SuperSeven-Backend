<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'add_on_name' => $this->add_on_name,
            'add_on_details' => $this->add_on_details,
            'add_on_price' => $this->add_on_price,
        ];
    }
}
