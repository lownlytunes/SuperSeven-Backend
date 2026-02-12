<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\BillingResource;
use App\Models\Billing;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingCollection extends PaginationCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => BillingResource::collection($this->collection),

            'extra' => [
                'total_bill' => $this->getTotalBilling($request) ?? 0,
                'total_balance' => $this->getTotalBalance($request) ?? 0,
            ],

            $this->merge($this->pagination()),
        ];
    }

    private function getTotalBilling(Request $request)
    {
        $clientId = $request->user()->customer ? $request->user()->id : 0;
        return Billing::whereHas('booking', function ($query) use ($clientId) {
            if ($clientId) {
                $query->where('customer_id', $clientId);
            }
            $query->where('booking_status', '!=', Booking::STATUS_REJECTED);
        })->sum('total_amount');
    }

    private function getTotalBalance(Request $request)
    {
        $clientId = $request->user()->customer ? $request->user()->id : 0;
        return Billing::whereHas('booking', function ($query) use ($clientId) {
            if ($clientId) {
                $query->where('customer_id', $clientId);
            }
            $query->where('booking_status', '!=', Booking::STATUS_REJECTED);
        })->sum('balance');
    }
}