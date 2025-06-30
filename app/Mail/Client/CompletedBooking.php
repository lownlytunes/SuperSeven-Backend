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

class CompletedBooking extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $clientName;
    private string $eventName;
    private string $packageName;
    private string $addOns;
    private string $bookingDate;
    private string $ceremonyTime;
    private string $bookingAddress;
    private int $status;
    private string $receipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->bookingId = $booking->id;
        $this->clientName = $booking->customer ? $booking->customer->full_name : null;
        $this->eventName = $booking->event_name;
        $this->packageName = $booking->package ? $booking->package->package_name : null;
        $this->addOns = $booking->addOns ? $booking->addOns->pluck('add_on_name')->implode(', ') : null;
        $this->bookingDate = $booking->booking_date;
        $this->ceremonyTime = $booking->ceremony_time;
        $this->bookingAddress = $booking->booking_address;
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
            subject: 'Booking Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client.booking.completed',
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
            'booking_id' => $this->bookingId,
            'client_name' => $this->clientName,
            'event_name' => $this->eventName,
            'package_name' => $this->packageName,
            'add_ons' => $this->addOns,
            'booking_date' => Carbon::parse($this->bookingDate)->format('F d, Y'),
            'ceremony_time' => Carbon::parse($this->ceremonyTime)->format('h:i A'),
            'booking_address' => $this->bookingAddress,
            'status' => Booking::STATUS[$this->status],
            'link' => [
                'url' => config('app.frontend_url') . "customer/bookings/{$this->bookingId}",
            ],
            'receipient' => $this->receipient
        ];

        return $details;
    }
}
