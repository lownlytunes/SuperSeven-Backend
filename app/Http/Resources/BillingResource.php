<?php

namespace App\Http\Resources;

use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->billing->id,
            'booking_id' => $this->id,
            'event_name' => $this->event_name,
            'customer_name' => $this->customer->full_name,
            'package' => $this->package->package_name,
            'add_ons' => AddonResource::collection($this->addOns),
            'package_amount' => $this->billing->package_amount,
            'add_on_amount' => $this->billing->add_on_amount,
            'discount' => $this->discount ?? 0,
            'total_amount' => $this->billing->total_amount,
            'balance' => $this->billing?->latestPayment->balance ?? $this->billing->total_amount,
            'status' => Billing::STATUS[$this->billing->billing_status],

            $this->mergeWhen($request->route()->named('billing.view'), [
                //Transactions
                'transactions' => TransactionResource::collection($this->billing->payments) ?? [],
            ])
        ];
    }
}
