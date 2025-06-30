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

class CancelledBooking extends Mailable
{
    use Queueable, SerializesModels;

    private string $recipient;
    private string $clientName;
    private string $eventName;
    private string $packageName;
    private string $addOns;
    private string $bookingDate;
    private string $ceremonyTime;
    private string $cancelledBy;
    private string $cancelledAt;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->recipient = $booking->customer ? $booking->customer->first_name : null;
        $this->clientName = $booking->customer ? $booking->customer->full_name : null;
        $this->eventName = $booking->event_name;
        $this->packageName = $booking->package ? $booking->package->package_name : null;
        $this->addOns = $booking->addOns ? $booking->addOns->pluck('add_on_name')->implode(', ') : null;
        $this->bookingDate = $booking->booking_date;
        $this->ceremonyTime = $booking->ceremony_time;
        $this->cancelledBy = $booking->deleted_by;
        $this->cancelledAt = $booking->deleted_at;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SuperSeven Studio'),
            subject: 'Booking Cancelled',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client.booking.cancelled',
            with: $this->getEmailDetails()
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
            'recipient' => $this->recipient,
            'client_name' => $this->clientName,
            'event_name' => $this->eventName,
            'package_name' => $this->packageName,
            'add_ons' => $this->addOns,
            'booking_date' => Carbon::parse($this->bookingDate)->format('F d, Y'),
            'ceremony_time' => Carbon::parse($this->ceremonyTime)->format('h:i A'),
            'cancelled_by' => $this->cancelledBy,
            'cancelled_at' => Carbon::parse($this->cancelledAt)->format('F d, Y h:i A'),
        ];

        return $details;
    }
}
