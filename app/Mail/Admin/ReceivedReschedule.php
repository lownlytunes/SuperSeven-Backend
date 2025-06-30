<?php

namespace App\Mail\Admin;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceivedReschedule extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $clientName;
    private string $eventName;
    private string $packageName;
    private string $addOns;
    private string $newDate;
    private string $ceremonyTime;
    private string $receipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $receipient)
    {
        $this->bookingId = $booking->id;
        $this->clientName = $booking->customer ? $booking->customer->full_name : null;
        $this->eventName = $booking->event_name;
        $this->packageName = $booking->package ? $booking->package->package_name : null;
        $this->addOns = $booking->addOns ? $booking->addOns->pluck('add_on_name')->implode(', ') : null;
        $this->newDate = $booking->booking_date;
        $this->ceremonyTime = $booking->ceremony_time;
        $this->receipient = $receipient;
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
            markdown: 'mail.admin.reschedule.received',
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
        return [
            'client_name' => $this->clientName,
            'event_name' => $this->eventName,
            'package_name' => $this->packageName,
            'add_ons' => $this->addOns,
            'new_date' => Carbon::parse($this->newDate)->format('F d, Y'),
            'ceremony_time' => Carbon::parse($this->ceremonyTime)->format('h:i A'),
            'link' => [
                'url' => config('app.frontend_url') . "/bookings/{$this->bookingId}",
            ],
            'receipient' => $this->receipient
        ];
    }
}
