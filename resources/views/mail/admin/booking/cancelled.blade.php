<x-mail::message>
# Hello {{ $recipient }},

The booking for **{{ $client_name }}** has been cancelled. Please note that
no refund will be issued for this cancellation, in accordance with our policy.

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

Please update your records and inform any involved staff if necessary.

</x-mail::message>
