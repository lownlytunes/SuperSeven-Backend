<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AddEmployeeWorkloadResource extends JsonResource
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
            'full_name' => $this->full_name,
            'user_role' => $this->customer
                ? User::ROLE_TYPES[$this->customer->customer_type]
                : ($this->employee
                    ? User::ROLE_TYPES[$this->employee->employee_type]
                    : null),
            'selected' => $booking->employees()->wherePivot('user_id', $this->id)->exists(),
        ];
    }
}
