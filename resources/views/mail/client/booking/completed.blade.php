<x-mail::message>
# Hello {{  $receipient }},

Thank you for you booking! We've successfully received your request.

Booking Details:
* Booking ID: **{{ $booking_id }}**
* Client Name: **{{  $client_name }}**
* Event Name: **{{ $event_name }}**
* Package: **{{ $package_name }}**
* Add On/s: **{{ $add_ons }}**
* Booking Date: **{{ $booking_date }}**
* Ceremony Time: **{{ $ceremony_time }}**
* Booking Address: **{{ $booking_address }}**
* Status: **{{ $status }}**

<x-mail::button :url="$link['url']">
View your Booking
</x-mail::button>

We'll be in touch shortly to confirm all the details.
If you have any questions in the meantime, feel free to contact us.

Best Regards,<br>
{{ config('app.name') }}
</x-mail::message>
