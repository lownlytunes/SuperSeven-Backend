<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'booking_date' => [
                'iso' => Carbon::parse($this->booking_date)->toISOString(),
                'formatted' => Carbon::parse($this->booking_date)->format('F d, Y (l)'),
                'day' => Carbon::parse($this->booking_date)->day,
                'month' => Carbon::parse($this->booking_date)->month,
                'year' => Carbon::parse($this->booking_date)->year,
            ],
            'ceremony_time' => Carbon::parse($this->ceremony_time)->format('h:i A'),
            'event_name' => $this->event_name,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer->full_name,
            'discount' => $this->discount,
            'booking_address' => $this->booking_address,
            'booking_status' => Booking::STATUS[$this->booking_status],
            'package' => $this->package->package_name,
            'add_ons' => AddonResource::collection($this->addOns),
            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y'),
        ];
    }
}
