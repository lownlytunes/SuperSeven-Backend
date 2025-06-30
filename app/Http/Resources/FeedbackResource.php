<?php

namespace App\Http\Resources;

use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
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
            'event_name' => $this->booking->event_name,
            'customer_name' => $this->user->full_name,
            'booking_date' => Carbon::parse($this->booking->booking_date)->format('F d, Y'),
            'feedback_date' => Carbon::parse($this->feedback_date)->format('F d, Y'),
            'feedback_status' => Feedback::STATUSES[$this->feedback_status],
            $this->mergeWhen($request->route()->named('feedback.detail' , 'feedback.view', 'feedback.add'), [
                'booking_date_detail' => Carbon::parse($this->booking->booking_date)->format('l, F j, Y'),
                'booking_address' => $this->booking->booking_address,
                'feedback_detail' => $this->feedback_details,
            ]),
        ];
    }
}
