<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingAddOnsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $booking = Booking::find($request->id);

        return [
            'id' => $this->id,
            'addon_name' => $this->addon_name,
            'selected' => $booking->addOns()->wherePivot('add_on_id', $this->id)->exists(),
        ];
    }
}
