<?php

namespace App\Mail\Client;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompletedReschedule extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $eventName;
    private string $packageName;
    private string $addons;
    private string $bookingDate;
    private string $ceremonyTime;
    private string $status;
    private string $receipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->bookingId = $booking->id;
        $this->eventName = $booking->event_name;
        $this->packageName = $booking->package ? $booking->package->package_name : null;
        $this->addons = $booking->addOns ? $booking->addOns->pluck('add_on_name')->implode(', ') : null;
        $this->bookingDate = $booking->booking_date;
        $this->ceremonyTime = $booking->ceremony_time;
        $this->status = $booking->booking_status;
        $this->receipient = $booking->customer ? $booking->customer->first_name : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SuperSeven Studio'),
            subject: 'Booking Rescheduled',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client.reschedule.completed',
            with: $this->getEmailDetails(),
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function getEmailDetails()
    {
        $details = [
            'event_name' => $this->eventName,
            'package_name' => $this->packageName,
            'add_ons' => $this->addons,
            'new_date' => Carbon::parse($this->bookingDate)->format('F d, Y'),
            'ceremony_time' => Carbon::parse($this->ceremonyTime)->format('h:i A'),
            'status' => Booking::STATUS[$this->status],
            'link' => [
                'url' => config('app.frontend_url') . "customer/bookings/{$this->bookingId}",
            ],
            'receipient' => $this->receipient
        ];

        return $details;
    }
}
