<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'transaction_date' => Carbon::parse($this->transaction_date)->format('F d, Y'),
            'amount_paid' => $this->amount_paid,
            'balance' => $this->balance,
            'payment_method' => Payment::PAYMENT_METHODS[$this->payment_method],
            'remarks' => $this->remarks,
        ];
    }
}
