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

class WorkloadRelease extends Mailable
{
    use Queueable, SerializesModels;

    private int $bookingId;
    private string $clientName;
    private string $eventName;
    private string $bookingDate;
    private string $dateReleased;
    private string $workloadStatus;
    private string $recipient;
    private string $employeeName;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $status, string $recipient, string $employeeName)
    {
        $this->bookingId = $booking->id;
        $this->clientName = $booking->customer ? $booking->customer->full_name : null;
        $this->eventName = $booking->event_name;
        $this->bookingDate = $booking->booking_date;
        $this->dateReleased = $booking->updated_at;
        $this->workloadStatus = $status;
        $this->recipient = $recipient;
        $this->employeeName = $employeeName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'SuperSeven Studio'),
            subject: 'Releasing',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.admin.workload.release',
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
            'client_name' => $this->clientName,
            'event_name' => $this->eventName,
            'booking_date' => Carbon::parse($this->bookingDate)->format('F d, Y'),
            'date' => Carbon::parse($this->dateReleased)->format('F d, Y'),
            'workload_status' => $this->workloadStatus,
            'link' => [
                'url' => config('app.frontend_url') . "/workload/{$this->bookingId}",
            ],
            'recipient' => $this->recipient,
            'employee_name' => $this->employeeName
        ];

        return $details;
    }
}
