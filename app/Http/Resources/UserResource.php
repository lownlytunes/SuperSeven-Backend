<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'mid_name' => $this->mid_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'contact_no' => $this->contact_num,
            'address' => $this->address,
            'email_verified_at' => $this->email_verified_at,
            'user_type' => $this->customer ? 'Customer' : ($this->employee ? 'Employee' : null),
            'user_role' => $this->customer
                ? User::ROLE_TYPES[$this->customer->customer_type]
                : ($this->employee
                    ? User::ROLE_TYPES[$this->employee->employee_type]
                    : null),
            'status' => $this->status,
        ];
    }
}
