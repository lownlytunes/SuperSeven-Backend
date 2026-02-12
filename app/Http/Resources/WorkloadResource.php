<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user()->id ?? 0;

        return [
            'id' => $this->id,
            'booking_date' => Carbon::parse($this->booking_date)->format('F d, Y'),
            'event_name' => $this->event_name,
            'customer_name' => $this->customer->full_name,
            'booking_address' => $this->booking_address,
            'package_name' => $this->package->package_name ?? null,
            'ceremony_time' => Carbon::parse($this->ceremony_time)->format('h:i A'),
            'completion_date' => $this->completion_date ? Carbon::parse($this->completion_date)->format('F d, Y') 
                    : Carbon::parse($this->booking_date)->addMonths(2)->format('F d, Y'),
            'booking_workload_status' => Booking::DELIVERABLE_STATUS[$this->deliverable_status],
            'booking_paid' => $this->has_full_payment,
            $this->mergeWhen($request->route()->named('workload.detail', 'workload.update'), [
                'employee_workload_status' => Booking::WORKLOAD_STATUS[$this->employees()->wherePivot('user_id', $user)->pluck('workload_status')->first()] ?? null
            ]),
            'link' => $this->link,
            'assigned_count' => $this->employees->count() ?? 0,
            'assigned_employees' => WorkloadEmployeeResource::collection($this->employees),
        ];
    }
}