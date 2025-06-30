<x-mail::message>
# Hello {{ $recipient }},

Your booking has been cancelled. Please note that no refund will be issued, as per
our cancellation policy.

**Booking Details:**
* Client Name: **{{ $client_name }}**
* Event Name: **{{ $event_name }}**
* Package: **{{ $package_name }}**
* Add On/s: **{{ $add_ons }}**
* Booking Date: **{{ $booking_date }}**
* Ceremony Time: **{{ $ceremony_time }}**
* Cancelled By: **{{ $cancelled_by }}**
* Cancelled At: **{{ $cancelled_at }}**
* Refund Status: **No Refund**

If you have any questions regarding our policy,
please feel free to reach out.

Best regards,<br>
{{ config('app.name') }}
</x-mail::message>
