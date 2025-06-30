<x-mail::message>
# Hi {{ $receipient }},

A new booking has been created on your platform. Here are the details:

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
View Booking Details
</x-mail::button>

Please review the booking before you confirm.

</x-mail::message>
