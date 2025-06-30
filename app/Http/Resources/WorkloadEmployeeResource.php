<?php

namespace App\Http\Resources;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkloadEmployeeResource extends JsonResource
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
            'full_name' => $this->full_name,
            'workload_status' => Booking::WORKLOAD_STATUS[$this->pivot->workload_status],
            'date_assigned' => Carbon::parse($this->pivot->created_at)->format('F d, Y'),
            'date_uploaded' => $this->pivot->date_uploaded ? Carbon::parse($this->pivot->date_uploaded)->format('F d, Y') : null
        ];
    }
}
