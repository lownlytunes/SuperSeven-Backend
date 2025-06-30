<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportBookingResource extends JsonResource
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
            'booking_date' => Carbon::parse($this->booking_date)->format('F d, Y'),
            'event_name' => $this->event_name,
            'customer_name ' => $this->customer->full_name,
            'total_amount' => $this->billing->total_amount,
        ];
    }   
}
