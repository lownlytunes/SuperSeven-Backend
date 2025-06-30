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

class CompletedWorkload extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $eventName;
    private string $completedDate;
    private string $clientName;
    private string $driveLink;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->bookingId = $booking->id;
        $this->eventName = $booking->event_name;
        $this->completedDate = $booking->updated_at;
        $this->clientName = $booking->customer ? $booking->customer->first_name : null;
        $this->driveLink = $booking->link;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SuperSeven Studio'),
            subject: 'Completed Workload',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client.workload.completed',
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

    public function getEmailDetails()
    {
        return [
            'event_name' => $this->eventName,
            'completed_date' => Carbon::parse($this->completedDate)->format('F d, Y'),
            'client_name' => $this->clientName,
            'drive_link' => $this->driveLink,
            'link' => [
                'url' => config('app.frontend_url') . "customer/bookings/{$this->bookingId}",
            ],
        ];
    }
}
