<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeWorkloadResource extends JsonResource
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
            'booking_address' => $this->booking_address,
            'completion_date' => Carbon::parse($this->completion_date)->format('F d, Y'),
            'booking_workload_status' => Booking::DELIVERABLE_STATUS[$this->deliverable_status],
            'link' => $this->link,
            'assigned_count' => $this->employees->count() ?? 0,
            'assigned_employees' => WorkloadEmployeeResource::collection($this->employees),
        ];
    }
}
